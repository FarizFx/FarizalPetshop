-- Update user table to include missing columns
ALTER TABLE user ADD COLUMN email VARCHAR(150) NOT NULL DEFAULT 'admin@example.com';
ALTER TABLE user ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'admin';
ALTER TABLE user ADD COLUMN foto_profil VARCHAR(255) DEFAULT NULL;
ALTER TABLE user ADD COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE user ADD COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Update the existing user
UPDATE user SET email = 'admin@example.com', role = 'admin', created_at = NOW(), updated_at = NOW() WHERE id = 1;
