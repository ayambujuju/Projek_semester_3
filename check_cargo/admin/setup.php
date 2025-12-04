<?php
session_start();
require '../partials/db.php';

// Security check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die("Unauthorized access.");
}

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
?>
<?php include 'partials/header.php'; ?>
<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="bg-dark border-right" id="sidebar-wrapper">
        <div class="sidebar-heading">
             <i class="bi bi-box-seam-fill"></i> Check Cargo
        </div>
        <div class="list-group list-group-flush">
            <a href="dashboard.php" class="list-group-item list-group-item-action">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="tracking.php" class="list-group-item list-group-item-action">
                <i class="bi bi-truck"></i> Manage Shipments
            </a>
             <a href="setup.php" class="list-group-item list-group-item-action active">
                <i class="bi bi-gear-fill"></i> Setup
            </a>
        </div>
    </div>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light border-bottom">
            <div class="container-fluid">
                <button class="btn btn-primary" id="menu-toggle"><i class="bi bi-list"></i></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#">Profile</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h4 class="mb-0"><i class="bi bi-gear-fill"></i> Database Setup</h4>
                </div>
                <div class="card-body">
                    <p>This page runs the setup script to ensure all necessary database tables are created. If you see any errors, please resolve them in your database environment.</p>
                    <hr>
                    <?php
                    if (!empty($messages)) {
                        foreach ($messages as $message) {
                            echo $message;
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'partials/footer.php'; ?>