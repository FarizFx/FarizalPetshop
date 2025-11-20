-- Update roles table to add stock_management permission

-- Update super_admin role
UPDATE roles SET permissions = JSON_SET(permissions, '$.stock_management', true) WHERE role_name = 'super_admin';

-- Update admin role
UPDATE roles SET permissions = JSON_SET(permissions, '$.stock_management', true) WHERE role_name = 'admin';

-- Update manager role
UPDATE roles SET permissions = JSON_SET(permissions, '$.stock_management', true) WHERE role_name = 'manager';

-- Update staff role
UPDATE roles SET permissions = JSON_SET(permissions, '$.stock_management', true) WHERE role_name = 'staff';

-- Update user role (no stock management permission)
UPDATE roles SET permissions = JSON_SET(permissions, '$.stock_management', false) WHERE role_name = 'user';

-- Update guest role (no stock management permission)
UPDATE roles SET permissions = JSON_SET(permissions, '$.stock_management', false) WHERE role_name = 'guest';
