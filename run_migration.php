<?php
include 'function/connection.php';

// Run the satuan migration
$satuan_sql = file_get_contents('database/add_satuan_to_tables.sql');
$satuan_queries = array_filter(array_map('trim', explode(';', $satuan_sql)));

echo 'Running satuan migration...' . PHP_EOL;
foreach($satuan_queries as $query) {
    if(!empty($query)) {
        // Try to execute the query, but don't fail if column already exists
        try {
            $result = mysqli_query($connection, $query);
            if ($result) {
                echo 'Query executed successfully: ' . substr($query, 0, 50) . '...' . PHP_EOL;
            } else {
                $error = mysqli_error($connection);
                // If it's a duplicate column error, skip it
                if (strpos($error, 'Duplicate column name') !== false || strpos($error, 'already exists') !== false) {
                    echo 'Column or constraint already exists, skipping...' . PHP_EOL;
                } else {
                    echo 'Error executing query: ' . $error . PHP_EOL;
                }
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (strpos($error, 'Duplicate column name') !== false || strpos($error, 'already exists') !== false) {
                echo 'Column or constraint already exists, skipping...' . PHP_EOL;
            } else {
                echo 'Exception: ' . $error . PHP_EOL;
            }
        }
    }
}

$sql = file_get_contents('database/create_pembelian_tables.sql');
$queries = array_filter(array_map('trim', explode(';', $sql)));

foreach($queries as $query) {
    if(!empty($query)) {
        // Try to execute the query, but don't fail if it's a duplicate index
        try {
            $result = mysqli_query($connection, $query);
            if ($result) {
                echo 'Query executed successfully: ' . substr($query, 0, 50) . '...' . PHP_EOL;
            } else {
                $error = mysqli_error($connection);
                // If it's a duplicate key error for index, skip it
                if (strpos($error, 'Duplicate key name') !== false || strpos($error, 'Duplicate entry') !== false) {
                    echo 'Index or constraint already exists, skipping...' . PHP_EOL;
                } else {
                    echo 'Error executing query: ' . $error . PHP_EOL;
                }
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (strpos($error, 'Duplicate key name') !== false || strpos($error, 'Duplicate entry') !== false) {
                echo 'Index or constraint already exists, skipping...' . PHP_EOL;
            } else {
                echo 'Exception: ' . $error . PHP_EOL;
            }
        }
    }
}
echo 'Migration completed.' . PHP_EOL;
?>
