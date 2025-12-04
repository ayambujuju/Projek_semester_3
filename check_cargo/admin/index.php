<?php
session_start();
// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}
include 'partials/header.php'; 
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="fw-bold my-4">Admin Panel Login</h3>
                </div>
                <div class="card-body p-4">
                    
                    <?php if(isset($_GET['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            Username atau password salah!
                        </div>
                    <?php endif; ?>

                    <form action="auth.php" method="POST">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                            <label for="username">Username</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password">Password</label>
                        </div>
                        <div class="d-grid mt-4">
                            <button class="btn btn-primary btn-lg fw-bold" type="submit">Login</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center py-3">
                    <div class="small">Check Cargo &copy; 2025</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
