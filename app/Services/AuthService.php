<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Security;
use App\Core\Session;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\User;
use PDO;

class AuthService
{
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    /**
     * Authenticate user credentials
     */
    public function attempt(string $email, string $password, string $ip = '127.0.0.1', string $userAgent = ''): array
    {
        $email = strtolower(trim($email));

        // 1. Check rate limiting / brute force lock
        if ($this->isRateLimited($email, $ip)) {
            ActivityLog::create([
                'action_type' => 'Login',
                'status' => 'Blocked',
                'notes' => "Too many failed login attempts for {$email} from {$ip}. Temporarily locked for " . self::LOCKOUT_MINUTES . " minutes.",
                'ip_address' => $ip,
                'user_agent' => substr($userAgent, 0, 255),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return [
                'success' => false,
                'error' => 'Too many failed login attempts. Please wait 15 minutes before trying again.',
            ];
        }

        // 2. Fetch user by email
        $user = User::findByEmail($email);

        if (!$user) {
            $this->recordFailedAttempt($email, $ip, $userAgent, 'User not found');
            return [
                'success' => false,
                'error' => 'Invalid email address or password.',
            ];
        }

        // 3. Check account status
        if ($user->status !== 'active') {
            $this->recordFailedAttempt($email, $ip, $userAgent, "Account is {$user->status}");
            return [
                'success' => false,
                'error' => "Your account is {$user->status}. Please contact an administrator.",
            ];
        }

        // 4. Verify password hash
        if (!password_verify($password, $user->password_hash)) {
            $this->recordFailedAttempt($email, $ip, $userAgent, 'Incorrect password', $user->id);
            return [
                'success' => false,
                'error' => 'Invalid email address or password.',
            ];
        }

        // 5. Rehash password if cost factor or algorithm changed
        if (password_needs_rehash($user->password_hash, PASSWORD_BCRYPT)) {
            User::update($user->id, [
                'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            ]);
        }

        // 6. Regenerate session to prevent session fixation attacks
        Session::regenerate(true);
        Security::regenerateCsrfToken();

        // 7. Update last login timestamp
        User::update($user->id, [
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);

        // 8. Store user info in session (excluding password_hash)
        $userData = [
            'id' => (int) $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'last_login_at' => date('Y-m-d H:i:s'),
        ];
        Session::set('user', $userData);

        // 9. Record success in Activity & Audit logs
        ActivityLog::create([
            'user_id' => $user->id,
            'action_type' => 'Login',
            'status' => 'Success',
            'notes' => 'User logged in successfully.',
            'ip_address' => $ip,
            'user_agent' => substr($userAgent, 0, 255),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'success' => true,
            'user' => $userData,
        ];
    }

    /**
     * Terminate user session
     */
    public function logout(): void
    {
        $user = auth_user();
        if ($user) {
            ActivityLog::create([
                'user_id' => $user['id'],
                'action_type' => 'Logout',
                'status' => 'Success',
                'notes' => 'User logged out.',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        Session::destroy();
    }

    /**
     * Change user password securely
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found.'];
        }

        if (!password_verify($currentPassword, $user->password_hash)) {
            return ['success' => false, 'error' => 'Current password is incorrect.'];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'error' => 'New password must be at least 8 characters long.'];
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        User::update($userId, [
            'password_hash' => $newHash,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLog::log('Password Updated', 'User', $userId);
        ActivityLog::create([
            'user_id' => $userId,
            'action_type' => 'Password Changed',
            'status' => 'Success',
            'notes' => 'User changed their password.',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return ['success' => true];
    }

    /**
     * Update user profile information
     */
    public function updateProfile(int $userId, string $name, string $email): array
    {
        $email = strtolower(trim($email));
        $name = trim($name);

        if (empty($name)) {
            return ['success' => false, 'error' => 'Name cannot be empty.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'A valid email address is required.'];
        }

        // Check for email collision
        $existing = User::findByEmail($email);
        if ($existing && (int)$existing->id !== $userId) {
            return ['success' => false, 'error' => 'This email address is already in use by another account.'];
        }

        $user = User::find($userId);
        $oldValues = ['name' => $user->name, 'email' => $user->email];
        $newValues = ['name' => $name, 'email' => $email];

        User::update($userId, [
            'name' => $name,
            'email' => $email,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Refresh Session
        $sessionUser = Session::get('user', []);
        $sessionUser['name'] = $name;
        $sessionUser['email'] = $email;
        Session::set('user', $sessionUser);

        AuditLog::log('Profile Updated', 'User', $userId, $oldValues, $newValues);

        return ['success' => true];
    }

    /**
     * Check if IP or email is currently rate-limited due to repeated failures
     */
    private function isRateLimited(string $email, string $ip): bool
    {
        $since = date('Y-m-d H:i:s', strtotime('-' . self::LOCKOUT_MINUTES . ' minutes'));
        $pdo = Database::connection();

        $stmt = $pdo->prepare("
            SELECT COUNT(*) as failed_count 
            FROM `activity_logs` 
            WHERE `action_type` = 'Login' 
              AND `status` = 'Failed' 
              AND (`notes` LIKE :email_note OR `ip_address` = :ip)
              AND `created_at` >= :since
        ");

        $stmt->execute([
            'email_note' => "%{$email}%",
            'ip' => $ip,
            'since' => $since,
        ]);

        $failedCount = (int) $stmt->fetchColumn();
        return $failedCount >= self::MAX_FAILED_ATTEMPTS;
    }

    /**
     * Record a failed login attempt in activity logs
     */
    private function recordFailedAttempt(string $email, string $ip, string $userAgent, string $reason, ?int $userId = null): void
    {
        ActivityLog::create([
            'user_id' => $userId,
            'action_type' => 'Login',
            'status' => 'Failed',
            'notes' => "Failed login for email [{$email}]: {$reason}",
            'ip_address' => $ip,
            'user_agent' => substr($userAgent, 0, 255),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
