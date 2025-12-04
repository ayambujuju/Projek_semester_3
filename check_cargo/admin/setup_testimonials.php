<?php
require '../partials/db.php';

$sql = "
CREATE TABLE IF NOT EXISTS testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 0
);
";

if ($conn->query($sql) === TRUE) {
    echo "Table 'testimonials' created successfully or already exists.";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
