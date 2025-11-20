<?php
include 'function/connection.php';
include_once 'function/role_manager.php';

// Inisialisasi role manager
$role_manager = new RoleManager($connection);

// Test delete role yang tidak digunakan
$roles_to_test = ['moderator', 'test'];

foreach ($roles_to_test as $role) {
    echo "Testing deletion of role: $role\n";

    // Cek apakah ada user yang menggunakan role ini
    $stmt = $connection->prepare("SELECT COUNT(*) as count FROM user WHERE role = ?");
    $stmt->bind_param("s", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();

    echo "Users with this role: $count\n";

    if ($count == 0) {
        $result = $role_manager->deleteRole($role);
        echo "Deletion result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
        echo "Message: " . $result['message'] . "\n";
    } else {
        echo "Cannot delete role - still in use\n";
    }

    echo "---\n";
}
?>
