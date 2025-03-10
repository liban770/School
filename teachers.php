<?php
session_start();
include 'db.php';

$teachers = [];
$searchTerm = '';
$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_teacher'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $subject = trim($_POST['subject']);

        // Validate input
        if (empty($username) || empty($password) || empty($first_name) || empty($last_name) || empty($subject)) {
            $errorMessage = "All fields are required.";
        } elseif (strlen($password) < 6) {
            $errorMessage = "Password must be at least 6 characters.";
        } else {
            try {
                $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['username' => $username, 'password' => password_hash($password, PASSWORD_BCRYPT)]);

                $user_id = $pdo->lastInsertId();

                $sql2 = "INSERT INTO teachers (id, first_name, last_name, subject) VALUES (:id, :first_name, :last_name, :subject)";
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->execute([
                    'id' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'subject' => $subject
                ]);

                $successMessage = "Teacher added successfully!";
            } catch (PDOException $e) {
                $errorMessage = "Error adding teacher: " . htmlspecialchars($e->getMessage());
            }
        }
    }

    if (isset($_POST['update_teacher'])) {
        $user_id = $_POST['user_id'];
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $subject = trim($_POST['subject']);

        // Validate input
        if (empty($first_name) || empty($last_name) || empty($subject)) {
            $errorMessage = "All fields are required.";
        } else {
            try {
                $sql = "UPDATE teachers SET first_name = :first_name, last_name = :last_name, subject = :subject WHERE id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'subject' => $subject,
                    'user_id' => $user_id
                ]);

                $successMessage = "Teacher updated successfully!";
            } catch (PDOException $e) {
                $errorMessage = "Error updating teacher: " . htmlspecialchars($e->getMessage());
            }
        }
    }

    if (isset($_POST['delete_teacher'])) {
        $user_id = $_POST['user_id'];

        try {
            $sql = "DELETE FROM Classes WHERE teacher_id = :teacher_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['teacher_id' => $user_id]);

            $sql = "DELETE FROM teachers WHERE id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['user_id' => $user_id]);

            $sql2 = "DELETE FROM users WHERE id = :user_id";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute(['user_id' => $user_id]);

            $successMessage = "Teacher deleted successfully!";
        } catch (PDOException $e) {
            $errorMessage = "Error deleting teacher: " . htmlspecialchars($e->getMessage());
        }
    }
}

if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    try {
        $sql = "SELECT * FROM teachers WHERE first_name LIKE :search OR last_name LIKE :search OR subject LIKE :search";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search' => '%' . $searchTerm . '%']);
        $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errorMessage = "Error fetching teachers: " . htmlspecialchars($e->getMessage());
    }
} else {
    try {
        $teachers = $pdo->query("SELECT * FROM teachers")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errorMessage = "Error fetching teachers: " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .alert {
            position: relative;
        }
        .alert .btn-close {
            position: absolute;
            top: 5px;
            right: 10px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Add Teacher</h2>
    <?php if (!empty($errorMessage)): ?>
        <div class='alert alert-danger text-center'>
            <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif (!empty($successMessage)): ?>
        <div class='alert alert-success text-center'>
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="teachers.php" class="mt-4">
        <input type="hidden" name="add_teacher" value="1">
        <div class="mb-3">
            <label for="username" class="form-label">Username:</label>
            <input type="text" class="form-control" id="username" name="username">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        <div class="mb-3">
            <label for="first_name" class="form-label">First Name:</label>
            <input type="text" class="form-control" id="first_name" name="first_name">
        </div>
        <div class="mb-3">
            <label for="last_name" class="form-label">Last Name:</label>
            <input type="text" class="form-control" id="last_name" name="last_name">
        </div>
        <div class="mb-3">
            <label for="subject" class="form-label">Subject:</label>
            <input type="text" class="form-control" id="subject" name="subject">
        </div>
        <button type="submit" class="btn btn-primary">Add Teacher</button>
    </form>

    <h2 class="text-center mt-5">Manage Teachers</h2>
    
    <div class="mb-3">
        <form method="GET" action="">
            <label for="search" class="form-label">Search Teachers:</label>
            <div class="input-group">
                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Enter name or subject">
                <button type="submit" class="btn btn-outline-primary">Search</button>
            </div>
        </form>
    </div>

    <table class="table mt-4">
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Subject</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($teachers as $teacher): ?>
            <tr>
                <td><?php echo htmlspecialchars($teacher['first_name']); ?></td>
                <td><?php echo htmlspecialchars($teacher['last_name']); ?></td>
                <td><?php echo htmlspecialchars($teacher['subject']); ?></td>
                <td>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateModal<?php echo $teacher['id']; ?>">Update</button>
                    <form method="POST" action="" class="d-inline">
                        <input type="hidden" name="user_id" value="<?php echo $teacher['id']; ?>">
                        <input type="hidden" name="delete_teacher" value="1">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>

            <div class="modal fade" id="updateModal<?php echo $teacher['id']; ?>" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateModalLabel">Update Teacher</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <input type="hidden" name="user_id" value="<?php echo $teacher['id']; ?>">
                                <input type="hidden" name="update_teacher" value="1">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name:</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($teacher['first_name']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name:</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($teacher['last_name']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject:</label>
                                    <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($teacher['subject']); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Teacher</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>