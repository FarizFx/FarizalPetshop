<?php
include "./function/connection.php";

$query = mysqli_query($connection, "SELECT * FROM user LIMIT 5");
while($row = mysqli_fetch_assoc($query)) {
    echo $row['nama'] . ' - ' . $row['role'] . PHP_EOL;
}
?>
