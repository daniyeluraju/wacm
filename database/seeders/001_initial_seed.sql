-- ==========================================================
-- WACM (WhatsApp Assistant & Contact Manager)
-- Initial Seed Data
-- ==========================================================

-- Default Users
-- Passwords:
-- admin@wacm.local   => Admin@123456 (Super Admin)
-- manager@wacm.local => Admin@123456 (Admin)
-- viewer@wacm.local  => Viewer@123456 (Viewer)

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `status`, `created_at`, `updated_at`)
VALUES
(1, 'Super Administrator', 'admin@wacm.local', '$2y$10$uQc7Ykdf7fNn75aKojNohej1/64JD1yklUVHOM2llOuDQrTEjSgBy', 'super_admin', 'active', NOW(), NOW()),
(2, 'Operations Manager', 'manager@wacm.local', '$2y$10$uQc7Ykdf7fNn75aKojNohej1/64JD1yklUVHOM2llOuDQrTEjSgBy', 'admin', 'active', NOW(), NOW()),
(3, 'Audit Viewer', 'viewer@wacm.local', '$2y$10$VsGVXOETDKMb38.xvi1pEuoQj6YMATqDw5jgwgWN3FGFUe1fTaWl.', 'viewer', 'active', NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Default Storage & Cleanup Settings
INSERT INTO `storage_settings` (`setting_key`, `setting_value`, `description`, `updated_at`)
VALUES
('cleanup_enabled', '1', 'Master switch for automatic cleanup system', NOW()),
('cleanup_retention_hours', '24', 'Hours to retain temporary files after campaign completion', NOW()),
('cleanup_auto_delete_temp', '1', 'Automatically delete temporary CSV/XLSX imports after processing', NOW()),
('storage_warning_threshold_mb', '500', 'Storage limit in megabytes before warning banner appears', NOW()),
('max_upload_size_bytes', '10485760', 'Maximum allowed attachment upload size in bytes (10MB)', NOW()),
('pacing_reminder_min_seconds', '32', 'Default minimum user pacing reminder interval', NOW()),
('pacing_reminder_max_seconds', '40', 'Default maximum user pacing reminder interval', NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Sample Contact List
INSERT INTO `contact_lists` (`id`, `name`, `description`, `color`, `created_by`, `is_archived`, `created_at`, `updated_at`)
VALUES
(1, 'VIP Client Onboarding', 'Priority contacts for welcome orientation and verified service notifications', '#3b82f6', 1, 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Sample Message Draft
INSERT INTO `message_drafts` (`id`, `title`, `content`, `has_attachment`, `caption`, `created_by`, `is_archived`, `created_at`, `updated_at`)
VALUES
(1, 'Welcome & Orientation Notice', 'Hello {{name}},\n\nThank you for connecting with us! We have set up your account for {{group_name}}.\n\nPlease let us know if you have any questions.\n\nBest regards,\nCustomer Care Team', 0, NULL, 1, 0, NOW(), NOW())
ON DUPLICATE KEY UPDATE `updated_at` = NOW();

-- Initial Audit Log
INSERT INTO `audit_logs` (`user_id`, `action`, `entity_type`, `entity_id`, `new_values`, `ip_address`, `created_at`)
VALUES
(1, 'System Initialized', 'Database', '1', '{"status":"seeded","version":"1.0.0"}', '127.0.0.1', NOW());
