
<!DOCTYPE html>
<html lang="en">

<?php
include('base.php');
include('db.php');
session_start();

$empty_err = $username_err = $password_err = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $pattern = '/^[a-zA-Z0-9]{3,20}$/';

    if (empty($username) || empty($password)) {
        $empty_err = 'All fields are required.';
    } else {
        if (!preg_match($pattern, $username)) {
            $username_err = 'Your username is not valid.';
        }
        if (!preg_match($pattern, $password)) {
            $password_err = 'Your password is not valid.';
        }
        if (empty($username_err) && empty($password_err)) {
            $sql = 'SELECT * FROM users WHERE username = :username';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: dashboard.php');
                exit();
            } else {
                $empty_err = 'Incorrect username or password.';
            }
        }
    }
}
?>
<style>
     body {
            background-image: url('ba.jpg'); /* Replace with your image URL */
            background-size: cover;
            background-position: center;
            color: white; /* Text color for better contrast */
        }
</style>
<body>
<header class="bg-primary text-white py-3 mb-4">
    <div class="container text-center">
        <h1>Login to School Management System</h1>
        <p class="lead">Access your dashboard by logging in below..</p>
    </div>
</header>
<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4 shadow">
                <h2 class="text-center mb-4">Login</h2>
                <form action="login.php" method="post" class="form-group">
                    <div class="mb-3">
                        <small class="text-danger"><?= $empty_err ?></small>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" id="username" class="form-control">
                        <small class="text-danger"><?= $username_err ?></small>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control">
                        <small class="text-danger"><?= $password_err ?></small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <p class="mt-3 text-center">Don’t have an account? <a href="signup.php">Sign up here</a>.</p>
            </div>
        </div>
    </div>
</div>
<footer class="bg-dark text-white text-center py-3">
    <p>&copy; 2025 School Management System. All rights reserved.</p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>