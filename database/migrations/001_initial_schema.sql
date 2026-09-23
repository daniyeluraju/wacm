-- ==========================================================
-- WACM (WhatsApp Assistant & Contact Manager)
-- Initial Relational Database Schema
-- Charset: utf8mb4, Collation: utf8mb4_unicode_ci
-- ==========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(191) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('super_admin', 'admin', 'viewer') NOT NULL DEFAULT 'admin',
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `last_login_at` DATETIME NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_email` (`email`),
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Contact Lists Table
CREATE TABLE IF NOT EXISTS `contact_lists` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `color` VARCHAR(20) NOT NULL DEFAULT '#3b82f6',
    `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `is_archived` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_contact_lists_created_by` (`created_by`),
    INDEX `idx_contact_lists_is_archived` (`is_archived`),
    CONSTRAINT `fk_contact_lists_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Contacts Table
CREATE TABLE IF NOT EXISTS `contacts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name` VARCHAR(150) NOT NULL,
    `phone_raw` VARCHAR(50) NOT NULL,
    `country_code` VARCHAR(10) NOT NULL DEFAULT '+1',
    `phone_normalized` VARCHAR(30) NOT NULL,
    `email` VARCHAR(191) NULL DEFAULT NULL,
    `group_name` VARCHAR(100) NULL DEFAULT NULL,
    `consent_status` ENUM('granted', 'pending', 'denied', 'revoked', 'unspecified') NOT NULL DEFAULT 'unspecified',
    `consent_source` VARCHAR(150) NULL DEFAULT NULL,
    `consent_date` DATETIME NULL DEFAULT NULL,
    `opt_out_status` TINYINT(1) NOT NULL DEFAULT 0,
    `opt_out_date` DATETIME NULL DEFAULT NULL,
    `custom_field_1` VARCHAR(255) NULL DEFAULT NULL,
    `custom_field_2` VARCHAR(255) NULL DEFAULT NULL,
    `notes` TEXT NULL DEFAULT NULL,
    `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
    `deleted_at` DATETIME NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_contacts_phone_normalized` (`phone_normalized`),
    INDEX `idx_contacts_email` (`email`),
    INDEX `idx_contacts_consent_status` (`consent_status`),
    INDEX `idx_contacts_opt_out` (`opt_out_status`),
    INDEX `idx_contacts_group` (`group_name`),
    INDEX `idx_contacts_is_deleted` (`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Contact List Members (Pivot Table)
CREATE TABLE IF NOT EXISTS `contact_list_members` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `list_id` BIGINT UNSIGNED NOT NULL,
    `contact_id` BIGINT UNSIGNED NOT NULL,
    `added_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_list_contact` (`list_id`, `contact_id`),
    INDEX `idx_list_members_list` (`list_id`),
    INDEX `idx_list_members_contact` (`contact_id`),
    CONSTRAINT `fk_list_members_list` FOREIGN KEY (`list_id`) REFERENCES `contact_lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_list_members_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Consent Records (Audit trail of consent)
CREATE TABLE IF NOT EXISTS `consent_records` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `contact_id` BIGINT UNSIGNED NOT NULL,
    `status` ENUM('granted', 'pending', 'denied', 'revoked') NOT NULL,
    `source` VARCHAR(150) NOT NULL,
    `notes` TEXT NULL DEFAULT NULL,
    `recorded_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `recorded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_consent_records_contact` (`contact_id`),
    INDEX `idx_consent_records_status` (`status`),
    CONSTRAINT `fk_consent_records_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_consent_records_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Message Drafts Table
CREATE TABLE IF NOT EXISTS `message_drafts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(200) NOT NULL,
    `content` TEXT NOT NULL,
    `has_attachment` TINYINT(1) NOT NULL DEFAULT 0,
    `caption` VARCHAR(500) NULL DEFAULT NULL,
    `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `is_archived` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_drafts_created_by` (`created_by`),
    INDEX `idx_drafts_archived` (`is_archived`),
    CONSTRAINT `fk_drafts_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Attachments Table
CREATE TABLE IF NOT EXISTS `attachments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `draft_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `stored_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_size` BIGINT UNSIGNED NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `dimensions` VARCHAR(50) NULL DEFAULT NULL,
    `is_temporary` TINYINT(1) NOT NULL DEFAULT 0,
    `uploaded_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_attachments_draft` (`draft_id`),
    INDEX `idx_attachments_uploaded_by` (`uploaded_by`),
    INDEX `idx_attachments_is_temp` (`is_temporary`),
    CONSTRAINT `fk_attachments_draft` FOREIGN KEY (`draft_id`) REFERENCES `message_drafts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_attachments_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Campaigns Table
CREATE TABLE IF NOT EXISTS `campaigns` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(200) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `contact_list_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `message_draft_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `status` ENUM(
        'draft',
        'ready_for_review',
        'in_progress',
        'paused',
        'completed',
        'cancelled',
        'cleanup_pending',
        'cleaned_up'
    ) NOT NULL DEFAULT 'draft',
    `pacing_min_seconds` INT UNSIGNED NOT NULL DEFAULT 32,
    `pacing_max_seconds` INT UNSIGNED NOT NULL DEFAULT 40,
    `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `started_at` DATETIME NULL DEFAULT NULL,
    `completed_at` DATETIME NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_campaigns_status` (`status`),
    INDEX `idx_campaigns_list` (`contact_list_id`),
    INDEX `idx_campaigns_draft` (`message_draft_id`),
    INDEX `idx_campaigns_created_by` (`created_by`),
    CONSTRAINT `fk_campaigns_list` FOREIGN KEY (`contact_list_id`) REFERENCES `contact_lists` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_campaigns_draft` FOREIGN KEY (`message_draft_id`) REFERENCES `message_drafts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_campaigns_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Campaign Recipients Table
CREATE TABLE IF NOT EXISTS `campaign_recipients` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` BIGINT UNSIGNED NOT NULL,
    `contact_id` BIGINT UNSIGNED NOT NULL,
    `status` ENUM(
        'pending',
        'eligible',
        'skipped',
        'chat_opened',
        'message_prepared',
        'user_marked_completed',
        'cancelled',
        'failed_preparation',
        'opted_out',
        'invalid'
    ) NOT NULL DEFAULT 'pending',
    `prepared_message` MEDIUMTEXT NULL DEFAULT NULL,
    `action_notes` TEXT NULL DEFAULT NULL,
    `completed_at` DATETIME NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_campaign_contact` (`campaign_id`, `contact_id`),
    INDEX `idx_recipients_campaign` (`campaign_id`),
    INDEX `idx_recipients_contact` (`contact_id`),
    INDEX `idx_recipients_status` (`status`),
    CONSTRAINT `fk_recipients_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_recipients_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Activity Logs Table
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `campaign_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `contact_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `action_type` VARCHAR(100) NOT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'Success',
    `notes` TEXT NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_activity_user` (`user_id`),
    INDEX `idx_activity_campaign` (`campaign_id`),
    INDEX `idx_activity_contact` (`contact_id`),
    INDEX `idx_activity_action` (`action_type`),
    INDEX `idx_activity_created_at` (`created_at`),
    CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_activity_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_activity_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Audit Logs Table
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(100) NOT NULL,
    `entity_id` VARCHAR(50) NULL DEFAULT NULL,
    `old_values` JSON NULL DEFAULT NULL,
    `new_values` JSON NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_entity` (`entity_type`, `entity_id`),
    INDEX `idx_audit_action` (`action`),
    INDEX `idx_audit_created_at` (`created_at`),
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. User Sessions Table
CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id` VARCHAR(128) NOT NULL,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL DEFAULT NULL,
    `last_activity` INT UNSIGNED NOT NULL,
    `payload` LONGTEXT NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_sessions_user_id` (`user_id`),
    INDEX `idx_sessions_last_activity` (`last_activity`),
    CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Cleanup History Table
CREATE TABLE IF NOT EXISTS `cleanup_history` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `campaign_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `initiated_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `cleanup_type` ENUM('automatic', 'manual', 'scheduled') NOT NULL DEFAULT 'automatic',
    `records_deleted` INT UNSIGNED NOT NULL DEFAULT 0,
    `files_deleted` INT UNSIGNED NOT NULL DEFAULT 0,
    `storage_released_bytes` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `status` ENUM('in_progress', 'completed', 'failed', 'cancelled') NOT NULL DEFAULT 'in_progress',
    `error_message` TEXT NULL DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_cleanup_campaign` (`campaign_id`),
    INDEX `idx_cleanup_initiated_by` (`initiated_by`),
    INDEX `idx_cleanup_status` (`status`),
    CONSTRAINT `fk_cleanup_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `campaigns` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_cleanup_user` FOREIGN KEY (`initiated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Storage Settings Table
CREATE TABLE IF NOT EXISTS `storage_settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT NOT NULL,
    `description` VARCHAR(255) NULL DEFAULT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_storage_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Import History Table
CREATE TABLE IF NOT EXISTS `import_history` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_size` BIGINT UNSIGNED NOT NULL,
    `total_rows` INT UNSIGNED NOT NULL DEFAULT 0,
    `imported_rows` INT UNSIGNED NOT NULL DEFAULT 0,
    `rejected_rows` INT UNSIGNED NOT NULL DEFAULT 0,
    `error_log_path` VARCHAR(500) NULL DEFAULT NULL,
    `status` ENUM('processing', 'completed', 'failed', 'rolled_back') NOT NULL DEFAULT 'processing',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_import_user` (`user_id`),
    INDEX `idx_import_status` (`status`),
    CONSTRAINT `fk_import_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
