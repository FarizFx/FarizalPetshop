<?php
// Database configuration using environment variables with fallbacks
$hostname = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: 'fariz';
$database = getenv('DB_NAME') ?: 'login_petshop';
?>
