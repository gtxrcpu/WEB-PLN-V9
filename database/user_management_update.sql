-- ==========================================================
-- SCRIPT SQL: UPDATE STRUKTUR UNIT & USER MANAGEMENT
-- ==========================================================

-- 1. TAMBAH DAN UPDATE UNIT
-- Update Unit Existing yang berubah kode
UPDATE `units` SET `code` = 'UP2W3', `name` = 'UP2WIII' WHERE `code` = 'UPW2';
UPDATE `units` SET `code` = 'UP2W4', `name` = 'UP2WIV' WHERE `code` = 'UPW3';

-- Insert Unit Baru
INSERT IGNORE INTO `units` (`code`, `name`, `description`, `created_at`, `updated_at`) VALUES 
('UP2W1', 'UP2WI', 'Unit Pelayanan dan Pengelolaan Wilayah I', NOW(), NOW()),
('UP2W2', 'UP2WII', 'Unit Pelayanan dan Pengelolaan Wilayah II', NOW(), NOW()),
('UP2W5', 'UP2WV', 'Unit Pelayanan dan Pengelolaan Wilayah V', NOW(), NOW()),
('UP2W6', 'UP2WVI', 'Unit Pelayanan dan Pengelolaan Wilayah VI', NOW(), NOW());

-- 2. UPDATE EMAIL EXISTING USER MANAGER
-- Update UP2WIII
UPDATE `users` SET 
    `email` = 'user.up2w3@pln.com', 
    `username` = 'user_up2w3', 
    `name` = 'User UP2WIII' 
WHERE `email` = 'leader.upw2@pln.co.id';

-- Update UP2WIV
UPDATE `users` SET 
    `email` = 'user.up2w4@pln.com', 
    `username` = 'user_up2w4',
    `name` = 'User UP2WIV' 
WHERE `email` = 'leader.upw3@pln.co.id';

-- 3. INSERT USER BARU (UP2WI, UP2WII, UP2WV, UP2WVI)
-- (Subqueries dipakai untuk memastikan ForeignKey unit tepat pasca insert di atas)
INSERT INTO `users` (`name`, `username`, `email`, `password`, `unit_id`, `position`, `created_at`, `updated_at`)
VALUES 
('User UP2WI', 'user_up2w1', 'user.up2w1@pln.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', (SELECT id FROM units WHERE code = 'UP2W1'), 'leader', NOW(), NOW()),
('User UP2WII', 'user_up2w2', 'user.up2w2@pln.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', (SELECT id FROM units WHERE code = 'UP2W2'), 'leader', NOW(), NOW()),
('User UP2WV', 'user_up2w5', 'user.up2w5@pln.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', (SELECT id FROM units WHERE code = 'UP2W5'), 'leader', NOW(), NOW()),
('User UP2WVI', 'user_up2w6', 'user.up2w6@pln.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', (SELECT id FROM units WHERE code = 'UP2W6'), 'leader', NOW(), NOW());

-- 4. ASSIGN ROLE LEADER SPATIE (Optional jika manual DB insert via IDE, direkomendasikan pakai Artisan Tinker)
-- INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) 
--   SELECT id, 'App\\Models\\User', (SELECT id FROM users WHERE email='user.up2w1@pln.com') FROM roles WHERE name='leader';
