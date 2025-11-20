<?php
include "./function/connection.php";

$query = mysqli_query($connection, "SELECT * FROM roles");
while($row = mysqli_fetch_assoc($query)) {
    echo $row['role_name'] . ': ' . $row['permissions'] . PHP_EOL;
}
?>
