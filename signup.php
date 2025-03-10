<?php 
include 'db.php'; 
include('base.php');  

$error = ''; // Variable to store error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $username = trim($_POST['username']); 
    $email = trim($_POST['email']); 
    $password = trim($_POST['password']); 
    
    // Server-side validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); 
        try { 
            $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)"; 
            $stmt = $pdo->prepare($sql); 
            $stmt->execute(['username' => $username, 'email' => $email, 'password' => $hashedPassword]); 
            header('Location: login.php'); 
            exit; 
        } catch (PDOException $e) { 
            $error = "Error: " . $e->getMessage();
        }
    }
}
?> 
<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <title>Signup</title> 
    <style> 
        body { 
            background-image: url('ba.jpg'); 
            background-size: cover; 
            background-position: center; 
            color: white; 
        } 
    </style> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"> 
</head> 
<body> 
<div class="container mt-5"> 
    <h2>Register to Get Started</h2> 
    <?php if (!empty($error)) : ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="signup.php" novalidate> 
        <div class="mb-3"> 
            <label for="username" class="form-label">Username</label> 
            <input type="text" class="form-control" id="username" name="username"> 
            <div class="invalid-feedback">
                Please enter your username.
            </div>
        </div> 
        <div class="mb-3"> 
            <label for="email" class="form-label">Email</label> 
            <input type="email" class="form-control" id="email" name="email"> 
            <div class="invalid-feedback">
                Please enter a valid email address.
            </div>
        </div> 
        <div class="mb-3"> 
            <label for="password" class="form-label">Password</label> 
            <input type="password" class="form-control" id="password" name="password"> 
            <div class="invalid-feedback">
                Password must be at least 6 characters long.
            </div>
        </div> 
        <button type="submit" class="btn btn-primary">Signup</button> 
    </form> 
</div> 
</body> 
</html>
