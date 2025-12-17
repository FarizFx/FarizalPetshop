<?php
// FIXED Change Password Page with Enhanced Debugging
session_start();

// Debug session
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo "<h2>Session Debug:</h2>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    echo "<hr>";
}

// Include files
require_once './function/connection.php'; 
include './function/language.php';

// Enhanced error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';
$debug_info = [];

// Check if user is logged in
if (!isset($_SESSION['nama'])) {
    $message = __('Please log in first.');
    echo "<div style='color: red; padding: 20px;'>";
    echo "<h3>Login Required</h3>";
    echo "<p>$message</p>";
    echo "<p><a href='login.php'>Go to Login</a></p>";
    echo "</div>";
    exit();
}

echo "<div style='background: #e3f2fd; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
echo "<strong>Logged in as:</strong> " . htmlspecialchars($_SESSION['nama']) . " ";
echo "<strong>User ID:</strong> " . ($_SESSION['user_id'] ?? 'Not set');
echo "</div>";

// Get user_id from session with fallback
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

if (!$user_id) {
    $message = __('User ID not found in session. Please log out and log in again.');
    echo "<div style='color: red; padding: 20px;'>";
    echo "<h3>Session Error</h3>";
    echo "<p>$message</p>";
    echo "<p>Available session keys: " . implode(', ', array_keys($_SESSION)) . "</p>";
    echo "</div>";
}

$min_password_length = 8;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3>Form Submitted</h3>";
    echo "<p>POST data received</p>";
    echo "</div>";
    
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Debug form data
    $debug_info[] = "Form fields: current_password=" . strlen($current_password) . " chars, new_password=" . strlen($new_password) . " chars, confirm_password=" . strlen($confirm_password) . " chars";

    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = __('All fields must be filled.');
        $debug_info[] = "Validation failed: Empty fields";
    } elseif (!$user_id) {
        $message = __('User ID is missing in session.');
        $debug_info[] = "Validation failed: No user_id";
    } elseif ($new_password !== $confirm_password) {
        $message = __('New password and confirmation password do not match.');
        $debug_info[] = "Validation failed: Passwords don't match";
    } elseif (strlen($new_password) < $min_password_length) {
        $message = sprintf(__('Password must be at least %d characters long.'), $min_password_length);
        $debug_info[] = "Validation failed: Password too short";
    } else {
        $debug_info[] = "Validation passed";
        
        // Check database connection
        if (!$connection) {
            $message = __('Database connection failed.');
            $debug_info[] = "Database connection failed";
        } else {
            $debug_info[] = "Database connection OK";
            
            // Test database query first
            $test_stmt = $connection->prepare("SELECT COUNT(*) as count FROM user WHERE id = ?");
            if (!$test_stmt) {
                $message = __('Database Error: Failed to prepare test statement. ') . $connection->error;
                $debug_info[] = "Test prepare failed: " . $connection->error;
            } else {
                $test_stmt->bind_param("i", $user_id);
                if ($test_stmt->execute()) {
                    $test_result = $test_stmt->get_result();
                    $test_row = $test_result->fetch_assoc();
                    if ($test_row['count'] == 0) {
                        $message = __('User not found in database.');
                        $debug_info[] = "User not found in database";
                    } else {
                        $debug_info[] = "User found in database";
                        
                        // Proceed with password change
                        $stmt = $connection->prepare("SELECT password FROM user WHERE id = ?");
                        
                        if (!$stmt) {
                            $message = __('Database Error: Failed to prepare fetch statement. ') . $connection->error;
                            $debug_info[] = "Prepare failed: " . $connection->error;
                        } else {
                            $stmt->bind_param("i", $user_id);
                            
                            if (!$stmt->execute()) {
                                $message = __('Database Error: Failed to execute fetch query. ') . $stmt->error;
                                $debug_info[] = "Execute failed: " . $stmt->error;
                            } else {
                                $stmt->bind_result($hashed_password);
                                
                                if ($stmt->fetch()) {
                                    $debug_info[] = "Current password hash retrieved: " . substr($hashed_password, 0, 20) . "...";
                                    
                                    // Verify current password
                                    if (password_verify($current_password, $hashed_password)) {
                                        $debug_info[] = "Current password verification: SUCCESS";
                                        
                                        // Hash new password
                                        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                                        $debug_info[] = "New password hash generated: " . substr($new_hashed_password, 0, 20) . "...";
                                        
                                        // Update password
                                        $update_stmt = $connection->prepare("UPDATE user SET password = ? WHERE id = ?");
                                        
                                        if (!$update_stmt) {
                                            $message = __('Database Error: Failed to prepare update statement. ') . $connection->error;
                                            $debug_info[] = "Update prepare failed: " . $connection->error;
                                        } else {
                                            $update_stmt->bind_param("si", $new_hashed_password, $user_id);
                                            
                                            if ($update_stmt->execute()) {
                                                $debug_info[] = "Password update executed successfully";
                                                
                                                // Verify the update
                                                $verify_stmt = $connection->prepare("SELECT password FROM user WHERE id = ?");
                                                $verify_stmt->bind_param("i", $user_id);
                                                $verify_stmt->execute();
                                                $verify_result = $verify_stmt->get_result();
                                                
                                                if ($verify_row = $verify_result->fetch_assoc()) {
                                                    if (password_verify($new_password, $verify_row['password'])) {
                                                        $message = __('Password successfully changed.');
                                                        $debug_info[] = "Password change verification: SUCCESS";
                                                    } else {
                                                        $message = __('Password update failed - verification mismatch.');
                                                        $debug_info[] = "Password change verification: FAILED";
                                                    }
                                                } else {
                                                    $message = __('Password update failed - cannot verify.');
                                                    $debug_info[] = "Password verification query failed";
                                                }
                                                $verify_stmt->close();
                                            } else {
                                                $message = __('Failed to change password. Database Error: ') . $update_stmt->error;
                                                $debug_info[] = "Update execute failed: " . $update_stmt->error;
                                            }
                                            $update_stmt->close();
                                        }
                                    } else {
                                        $message = __('Current password is wrong.');
                                        $debug_info[] = "Current password verification: FAILED";
                                    }
                                } else {
                                    $message = __('User ID not found in database.');
                                    $debug_info[] = "Fetch failed - no user data";
                                }
                            }
                            $stmt->close();
                        }
                    }
                } else {
                    $message = __('Database Error: Test query failed.');
                    $debug_info[] = "Test execute failed: " . $test_stmt->error;
                }
                $test_stmt->close();
            }
        }
    }
}

// Close connection
if (isset($connection) && is_object($connection)) {
    $connection->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= __('Change Password') ?> - Farizal Petshop</title>
    <link rel="stylesheet" href="../assets/compiled/css/app.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .debug-info { background: #f8f9fa; padding: 10px; margin: 10px 0; border-left: 4px solid #007bff; }
        .debug-info h4 { margin-top: 0; color: #007bff; }
        .debug-info ul { margin-bottom: 0; }
    </style>
</head>
<body>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white p-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-shield-lock me-2"></i><?= __('Change Password') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <?php 
                            $alert_class = 'alert-info';
                            if (strpos($message, 'successfully') !== false) {
                                $alert_class = 'alert-success';
                            } elseif (strpos($message, 'wrong') !== false || strpos($message, 'Failed') !== false || strpos($message, 'not match') !== false || strpos($message, 'Database Error') !== false || strpos($message, 'missing')) {
                                $alert_class = 'alert-danger';
                            }
                            ?>
                            <div class="alert <?= $alert_class ?>"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($debug_info)): ?>
                        <div class="debug-info">
                            <h4><i class="bi bi-info-circle"></i> Debug Information</h4>
                            <ul>
                                <?php foreach ($debug_info as $info): ?>
                                    <li><?= htmlspecialchars($info) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <form method="POST" action="change-password.php">
                                    <div class="mb-3 mt-4">
                                        <label for="current_password" class="form-label"><?= __('Current Password') ?> *</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="<?= __('Current password') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label"><?= __('New Password') ?> *</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required placeholder="<?= __('New password') ?>">
                                    </div>
                                    <div class="mb-3 pb-3">
                                        <label for="confirm_password" class="form-label"><?= __('Confirm New Password') ?> *</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="<?= __('Confirm password') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="showPassword">
                                            <label class="form-check-label" for="showPassword">
                                                <?= __('Show Password') ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 pt-5 mt-5">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle me-1"></i><?= __('Change Password') ?>
                                        </button>
                                        <a href="index.php?halaman=profile" class="btn btn-secondary">
                                            <i class="bi bi-arrow-left me-1"></i><?= __('Back') ?>
                                        </a>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-column justify-content-center h-100 mt-3">
                                    <div class="text-center">
                                        <i class="bi bi-shield-check text-primary" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3"><?= __('Password Security Tips') ?></h5>
                                        <ul class="list-unstyled text-start mt-3">
                                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><?= __('Minimum 8 characters') ?></li>
                                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><?= __('Combination of uppercase & lowercase letters') ?></li>
                                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><?= __('Include numbers & symbols') ?></li>
                                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><?= __('Do not use personal information') ?></li>
                                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><?= __('Change password regularly') ?></li>
                                        </ul>
                                        <div class="alert alert-warning mt-4">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong><?= __('Important!') ?></strong> <?= __('Do not share your password with anyone.') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('showPassword').addEventListener('change', function() {
            const passwordFields = ['current_password', 'new_password', 'confirm_password'];
            const type = this.checked ? 'text' : 'password';

            passwordFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.type = type;
                }
            });
        });
    </script>
</body>
</html>
