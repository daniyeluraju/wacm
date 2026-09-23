<?php

namespace App\Core;

use PDO;
use PDOStatement;

abstract class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public static function query(): static
    {
        return new static();
    }

    protected static function db(): PDO
    {
        return Database::connection();
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]) && $this->attributes[$name] !== null && $this->attributes[$name] !== '';
    }

    public function __unset(string $name): void
    {
        unset($this->attributes[$name]);
    }

    public function toArray(): array
    {
        $data = $this->attributes;
        foreach ($this->hidden as $key) {
            unset($data[$key]);
        }
        return $data;
    }

    /**
     * Find a single record by primary key
     */
    public static function find(int|string $id): ?static
    {
        $instance = new static();
        $stmt = self::db()->prepare("SELECT * FROM `{$instance->table}` WHERE `{$instance->primaryKey}` = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new static($row);
    }

    /**
     * Retrieve all records
     */
    public static function all(string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $instance = new static();
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $stmt = self::db()->query("SELECT * FROM `{$instance->table}` ORDER BY `{$orderBy}` {$direction}");
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new static($row);
        }
        return $results;
    }

    /**
     * Select records matching key-value pairs
     */
    public static function where(string $column, mixed $value, string $operator = '='): array
    {
        $instance = new static();
        $stmt = self::db()->prepare("SELECT * FROM `{$instance->table}` WHERE `{$column}` {$operator} :val");
        $stmt->execute(['val' => $value]);
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new static($row);
        }
        return $results;
    }

    /**
     * Find first record matching key-value
     */
    public static function findBy(string $column, mixed $value): ?static
    {
        $instance = new static();
        $stmt = self::db()->prepare("SELECT * FROM `{$instance->table}` WHERE `{$column}` = :val LIMIT 1");
        $stmt->execute(['val' => $value]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new static($row);
    }

    /**
     * Insert a new record
     */
    public static function create(array $data): ?static
    {
        $instance = new static();
        $filtered = [];

        foreach ($data as $key => $val) {
            if (empty($instance->fillable) || in_array($key, $instance->fillable, true)) {
                $filtered[$key] = $val;
            }
        }

        if (empty($filtered)) {
            return null;
        }

        $columns = array_keys($filtered);
        $fields = '`' . implode('`, `', $columns) . '`';
        $placeholders = ':' . implode(', :', $columns);

        $sql = "INSERT INTO `{$instance->table}` ({$fields}) VALUES ({$placeholders})";
        $stmt = self::db()->prepare($sql);
        $stmt->execute($filtered);

        $insertId = self::db()->lastInsertId();
        return self::find($insertId);
    }

    /**
     * Update record by primary key
     */
    public static function update(int|string $id, array $data): bool
    {
        $instance = new static();
        $filtered = [];
        $setClauses = [];

        foreach ($data as $key => $val) {
            if (empty($instance->fillable) || in_array($key, $instance->fillable, true)) {
                $filtered[$key] = $val;
                $setClauses[] = "`{$key}` = :{$key}";
            }
        }

        if (empty($filtered)) {
            return false;
        }

        $filtered['__pk'] = $id;
        $sql = "UPDATE `{$instance->table}` SET " . implode(', ', $setClauses) . " WHERE `{$instance->primaryKey}` = :__pk";
        $stmt = self::db()->prepare($sql);
        return $stmt->execute($filtered);
    }

    /**
     * Delete record by primary key
     */
    public static function delete(int|string $id): bool
    {
        $instance = new static();
        $stmt = self::db()->prepare("DELETE FROM `{$instance->table}` WHERE `{$instance->primaryKey}` = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Count total records with flexible string or associative array condition
     */
    public static function count(string|array $where = '1=1', array $params = []): int
    {
        $instance = new static();
        if (is_array($where)) {
            if (empty($where)) {
                $whereClause = '1=1';
                $params = [];
            } else {
                $conditions = [];
                $params = [];
                foreach ($where as $col => $val) {
                    $paramKey = "cnt_" . preg_replace('/[^a-zA-Z0-9_]/', '', $col);
                    $conditions[] = "`{$col}` = :{$paramKey}";
                    $params[$paramKey] = $val;
                }
                $whereClause = implode(' AND ', $conditions);
            }
        } else {
            $whereClause = empty($where) ? '1=1' : $where;
        }

        $stmt = self::db()->prepare("SELECT COUNT(*) as total FROM `{$instance->table}` WHERE {$whereClause}");
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    /**
     * Execute raw prepared statement
     */
    public static function raw(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
