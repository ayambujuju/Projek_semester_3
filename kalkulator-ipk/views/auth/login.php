<?php require_once '../layouts/header.php'; ?>
<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="text-center mb-4">Login</h3>
                <form action="<?= BASE_URL ?>actions/auth/login_action.php" method="POST">
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Masuk</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="register.php">Belum punya akun? Daftar</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once '../layouts/footer.php'; ?>