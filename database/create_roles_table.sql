-- Migration to create roles table for dynamic role management

CREATE TABLE IF NOT EXISTS roles (
    role_name VARCHAR(50) PRIMARY KEY,
    role_level INT NOT NULL,
    permissions JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default roles with permissions as JSON
INSERT INTO roles (role_name, role_level, permissions) VALUES
('super_admin', 100, JSON_OBJECT(
    'user_management', true,
    'role_management', true,
    'product_management', true,
    'category_management', true,
    'sales_management', true,
    'report_management', true,
    'system_settings', true,
    'backup_restore', true,
    'view_dashboard', true,
    'view_profile', true,
    'edit_profile', true,
    'change_password', true
)),
('admin', 80, JSON_OBJECT(
    'user_management', true,
    'role_management', false,
    'product_management', true,
    'category_management', true,
    'sales_management', true,
    'report_management', true,
    'system_settings', true,
    'backup_restore', true,
    'view_dashboard', true,
    'view_profile', true,
    'edit_profile', true,
    'change_password', true
)),
('manager', 60, JSON_OBJECT(
    'user_management', false,
    'role_management', false,
    'product_management', true,
    'category_management', true,
    'sales_management', true,
    'report_management', true,
    'system_settings', false,
    'backup_restore', false,
    'view_dashboard', true,
    'view_profile', true,
    'edit_profile', true,
    'change_password', true
)),
('staff', 40, JSON_OBJECT(
    'user_management', false,
    'role_management', false,
    'product_management', true,
    'category_management', false,
    'sales_management', true,
    'report_management', false,
    'system_settings', false,
    'backup_restore', false,
    'view_dashboard', true,
    'view_profile', true,
    'edit_profile', true,
    'change_password', true
)),
('user', 20, JSON_OBJECT(
    'user_management', false,
    'role_management', false,
    'product_management', false,
    'category_management', false,
    'sales_management', false,
    'report_management', false,
    'system_settings', false,
    'backup_restore', false,
    'view_dashboard', true,
    'view_profile', true,
    'edit_profile', true,
    'change_password', true
)),
('guest', 0, JSON_OBJECT(
    'user_management', false,
    'role_management', false,
    'product_management', false,
    'category_management', false,
    'sales_management', false,
    'report_management', false,
    'system_settings', false,
    'backup_restore', false,
    'view_dashboard', false,
    'view_profile', false,
    'edit_profile', false,
    'change_password', false
));
