<?php
session_start();
require 'partials/db.php';

echo "<h3>Starting database schema check for 'shipments' table...</h3>";

$columns = [
    'sender_name' => 'VARCHAR(255) NOT NULL',
    'sender_phone' => 'VARCHAR(50) DEFAULT NULL',
    'sender_address' => 'TEXT',
    'receiver_name' => 'VARCHAR(255) NOT NULL',
    'receiver_phone' => 'VARCHAR(50) DEFAULT NULL',
    'receiver_address' => 'TEXT',
    'cargo_type' => 'VARCHAR(100) DEFAULT NULL',
    'cargo_weight' => 'DECIMAL(10,2) DEFAULT NULL',
    'cargo_dimensions' => 'VARCHAR(100) DEFAULT NULL',
    'package_count' => "INT(11) DEFAULT '1'",
    'special_instructions' => 'TEXT',
    'service_type' => 'VARCHAR(100) DEFAULT NULL',
    'estimated_arrival' => 'DATE DEFAULT NULL',
    'proof_of_delivery_url' => 'VARCHAR(255) DEFAULT NULL',
    'last_updated' => 'TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
];

$table_name = 'shipments';

foreach ($columns as $column_name => $column_definition) {
    $result = $conn->query("SHOW COLUMNS FROM `$table_name` LIKE '$column_name'");
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE `$table_name` ADD COLUMN `$column_name` $column_definition";
        if ($conn->query($sql) === TRUE) {
            echo "<p style='color:green;'>Column `$column_name` added successfully.</p>";
        } else {
            echo "<p style='color:red;'>Error adding column `$column_name`: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:blue;'>Column `$column_name` already exists.</p>";
    }
}

echo "<h3>Database schema check complete.</h3>";

$conn->close();
?>