<?php
session_start();
require '../partials/db.php';

// Security check REMOVED

$messages = [];

// --- Table: shipments ---
$sql_shipments = "
CREATE TABLE IF NOT EXISTS `shipments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tracking_number` varchar(255) NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'Pending',
  `sender_name` varchar(255) NOT NULL,
  `sender_phone` varchar(50) DEFAULT NULL,
  `sender_address` text,
  `receiver_name` varchar(255) NOT NULL,
  `receiver_phone` varchar(50) DEFAULT NULL,
  `receiver_address` text,
  `cargo_type` varchar(100) DEFAULT NULL,
  `cargo_weight` decimal(10,2) DEFAULT NULL,
  `cargo_dimensions` varchar(100) DEFAULT NULL,
  `package_count` int(11) DEFAULT '1',
  `special_instructions` text,
  `service_type` varchar(100) DEFAULT NULL,
  `estimated_arrival` date DEFAULT NULL,
  `proof_of_delivery_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_number` (`tracking_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($sql_shipments) === TRUE) {
    $messages[] = "<div class='alert alert-success'>Table 'shipments' created successfully or already exists.</div>";
} else {
    $messages[] = "<div class='alert alert-danger'>Error creating 'shipments' table: " . $conn->error . "</div>";
}

// --- Table: shipment_history ---
$sql_history = "
CREATE TABLE IF NOT EXISTS `shipment_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shipment_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shipment_id` (`shipment_id`),
  CONSTRAINT `shipment_history_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($conn->query($sql_history) === TRUE) {
    $messages[] = "<div class='alert alert-success'>Table 'shipment_history' created successfully or already exists.</div>";
} else {
    $messages[] = "<div class='alert alert-danger'>Error creating 'shipment_history' table: " . $conn->error . "</div>";
}

// --- Table: testimonials ---
$sql_testimonials = "
CREATE TABLE IF NOT EXISTS `testimonials` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `company` VARCHAR(255),
    `content` TEXT NOT NULL,
    `avatar_url` VARCHAR(255),
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";
if ($conn->query($sql_testimonials) === TRUE) {
    $messages[] = "<div class='alert alert-success'>Table 'testimonials' created successfully or already exists.</div>";
} else {
    $messages[] = "<div class='alert alert-danger'>Error creating 'testimonials' table: " . $conn->error . "</div>";
}


// --- Table: users (for admin login) ---
$sql_users = "
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `full_name` VARCHAR(255),
    `role` VARCHAR(50) DEFAULT 'admin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";
if ($conn->query($sql_users) === TRUE) {
    $messages[] = "<div class='alert alert-success'>Table 'users' created successfully or already exists.</div>";
    
    // Check if default admin exists, if not, create one
    $res = $conn->query("SELECT * FROM users WHERE username = 'admin'");
    if($res->num_rows == 0) {
        $admin_pass_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = "INSERT INTO users (username, password, email, full_name) VALUES ('admin', '$admin_pass_hash', 'admin@example.com', 'Default Admin')";
        if($conn->query($insert_admin) === TRUE) {
            $messages[] = "<div class='alert alert-info'>Default admin user created. <strong>Username:</strong> admin, <strong>Password:</strong> admin123. Please change this password immediately.</div>";
        }
    }

} else {
    $messages[] = "<div class='alert alert-danger'>Error creating 'users' table: " . $conn->error . "</div>";
}


$conn->close();

foreach ($messages as $message) {
    echo $message;
}
?>