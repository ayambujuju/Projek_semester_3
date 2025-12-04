<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require '../partials/db.php';

// --- Data Fetching ---
$total_shipments = 0;
$in_transit = 0;
$delivered = 0;
$pending = 0;
$statuses = [];

$result = $conn->query("SELECT * FROM shipments ORDER BY id DESC");
$shipments_data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

if (!empty($shipments_data)) {
    $total_shipments = count($shipments_data);
    foreach ($shipments_data as $item) {
        $status = $item['status'];
        if (!isset($statuses[$status])) {
            $statuses[$status] = 0;
        }
        $statuses[$status]++;
    }
    $in_transit = $statuses['In Transit'] ?? 0;
    $delivered = $statuses['Delivered'] ?? 0;
    $pending = $statuses['Pending'] ?? 0;
}

$recent_shipments = array_slice($shipments_data, 0, 5);
?>
<?php include 'partials/header.php'; ?>

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="bg-dark border-right" id="sidebar-wrapper">
        <div class="sidebar-heading">
             <i class="bi bi-box-seam-fill"></i> Check Cargo
        </div>
        <div class="list-group list-group-flush">
            <a href="dashboard.php" class="list-group-item list-group-item-action active">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="tracking.php" class="list-group-item list-group-item-action">
                <i class="bi bi-truck"></i> Manage Shipments
            </a>
            <a href="edit_history.php" class="list-group-item list-group-item-action">
                <i class="bi bi-clock-history"></i> Edit History
            </a>
             <a href="#" class="list-group-item list-group-item-action">
                <i class="bi bi-gear-fill"></i> Settings
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
            <h1 class="mt-4">Dashboard</h1>
            <p>Welcome to the Check Cargo admin panel.</p>

            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Total Shipments</h5>
                                    <p class="card-text fs-2 fw-bold stat-number"><?php echo $total_shipments; ?></p>
                                </div>
                                <i class="bi bi-boxes fs-1 stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">In Transit</h5>
                                    <p class="card-text fs-2 fw-bold stat-number"><?php echo $in_transit; ?></p>
                                </div>
                                <i class="bi bi-truck fs-1 stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                             <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Delivered</h5>
                                    <p class="card-text fs-2 fw-bold stat-number"><?php echo $delivered; ?></p>
                                </div>
                                <i class="bi bi-check-circle-fill fs-1 stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
                 <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                             <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Pending</h5>
                                    <p class="card-text fs-2 fw-bold stat-number"><?php echo $pending; ?></p>
                                </div>
                                <i class="bi bi-clock-history fs-1 stat-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">
                            <i class="bi bi-bar-chart-fill"></i> Shipment Status Overview
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header">
                           <i class="bi bi-list-ul"></i> Recent Shipments
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tracking #</th>
                                            <th>Status</th>
                                            <th>Receiver</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recent_shipments)): ?>
                                            <?php foreach ($recent_shipments as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['tracking_number']); ?></td>
                                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($item['status']); ?></span></td>
                                                    <td><?php echo htmlspecialchars($item['receiver_name'] ?? ''); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center">No recent shipments.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /#page-content-wrapper -->
</div>
<!-- /#wrapper -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    const statusChartCtx = document.getElementById('statusChart').getContext('2d');
    if (statusChartCtx) {
        const statusChart = new Chart(statusChartCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($statuses)); ?>,
                datasets: [{
                    label: 'Shipment Status',
                    data: <?php echo json_encode(array_values($statuses)); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Shipments'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Status'
                        }
                    }
                }
            }
        });
    }

    const menuToggle = document.getElementById('menu-toggle');
    const wrapper = document.getElementById('wrapper');
    if(menuToggle) {
        menuToggle.addEventListener('click', function () {
            wrapper.classList.toggle('toggled');
        });
    }
});
</script>

<?php include 'partials/footer.php'; ?>
