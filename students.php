<?php
session_start();
include 'db.php';

function validateInput($data) {
    return isset($data) && !empty(trim($data));
}

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $grade_level = $_POST['grade_level'];

    if (!validateInput($username) || 
        !validateInput($password) || 
        !validateInput($first_name) || 
        !validateInput($last_name) || 
        !validateInput($date_of_birth) || 
        !validateInput($grade_level) || 
        !is_numeric($grade_level)) {
        
        $errorMessage = "All fields are required, and Grade Level must be a number.";
    } else {
        $password = password_hash($password, PASSWORD_BCRYPT);

        try {
            $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['username' => $username, 'password' => $password]);

            $user_id = $pdo->lastInsertId();

            $sql2 = "INSERT INTO students (id, first_name, last_name, date_of_birth, grade_level) VALUES (:id, :first_name, :last_name, :date_of_birth, :grade_level)";
            $stmt2 = $pdo->prepare($sql2);
            $stmt2->execute([
                'id' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'date_of_birth' => $date_of_birth,
                'grade_level' => $grade_level
            ]);

            $successMessage = "Student added successfully!";
        } catch (PDOException $e) {
            $errorMessage = "Error: " . htmlspecialchars($e->getMessage());
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    $user_id = $_POST['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $date_of_birth = $_POST['date_of_birth'];
    $grade_level = $_POST['grade_level'];

    if (!validateInput($first_name) || 
        !validateInput($last_name) || 
        !validateInput($date_of_birth) || 
        !validateInput($grade_level) || 
        !is_numeric($grade_level)) {
        
        $errorMessage = "All fields are required, and Grade Level must be a number.";
    } else {
        try {
            $sql = "UPDATE students SET first_name = :first_name, last_name = :last_name, date_of_birth = :date_of_birth, grade_level = :grade_level WHERE id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'date_of_birth' => $date_of_birth,
                'grade_level' => $grade_level,
                'user_id' => $user_id
            ]);

            $successMessage = "Student updated successfully!";
        } catch (PDOException $e) {
            $errorMessage = "Error: " . htmlspecialchars($e->getMessage());
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
    $user_id = $_POST['user_id'];

    try {
        $sql = "DELETE FROM students WHERE id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);

        $sql2 = "DELETE FROM users WHERE id = :user_id";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute(['user_id' => $user_id]);

        $successMessage = "Student deleted successfully!";
    } catch (PDOException $e) {
        $errorMessage = "Error: " . htmlspecialchars($e->getMessage());
    }
}

$searchTerm = '';
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    try {
        $sql = "SELECT * FROM students WHERE first_name LIKE :search OR last_name LIKE :search OR grade_level LIKE :search";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search' => '%' . $searchTerm . '%']);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errorMessage = "Error fetching students: " . htmlspecialchars($e->getMessage());
    }
} else {
    $students = $pdo->query("SELECT * FROM students")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management</title>
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
    <h2 class="text-center">Add Student</h2>
    <?php if ($errorMessage): ?>
        <div class='alert alert-danger text-center'>
            <?php echo $errorMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif ($successMessage): ?>
        <div class='alert alert-success text-center'>
            <?php echo $successMessage; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="students.php" class="mt-4">
        <input type="hidden" name="add_student" value="1">
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
            <label for="date_of_birth" class="form-label">Date of Birth:</label>
            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
        </div>
        <div class="mb-3">
            <label for="grade_level" class="form-label">Grade Level:</label>
            <input type="text" class="form-control" id="grade_level" name="grade_level">
        </div>
        <button type="submit" class="btn btn-primary">Add Student</button>
    </form>

    <h2 class="text-center mt-5">Manage Students</h2>
    
    <div class="mb-3">
        <form method="GET" action="">
            <label for="search" class="form-label">Search Students:</label>
            <div class="input-group">
                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Enter name or grade level">
                <button type="submit" class="btn btn-outline-primary">Search</button>
            </div>
        </form>
    </div>

    <table class="table mt-4">
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Date of Birth</th>
                <th>Grade Level</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
            <tr>
                <td><?php echo htmlspecialchars($student['first_name']); ?></td>
                <td><?php echo htmlspecialchars($student['last_name']); ?></td>
                <td><?php echo htmlspecialchars($student['date_of_birth']); ?></td>
                <td><?php echo htmlspecialchars($student['grade_level']); ?></td>
                <td>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateModal<?php echo $student['id']; ?>">Update</button>
                    <form method="POST" action="" class="d-inline">
                        <input type="hidden" name="user_id" value="<?php echo $student['id']; ?>">
                        <input type="hidden" name="delete_student" value="1">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>

            <div class="modal fade" id="updateModal<?php echo $student['id']; ?>" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateModalLabel">Update Student</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <input type="hidden" name="user_id" value="<?php echo $student['id']; ?>">
                                <input type="hidden" name="update_student" value="1">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name:</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name:</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth:</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($student['date_of_birth']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="grade_level" class="form-label">Grade Level:</label>
                                    <input type="text" class="form-control" id="grade_level" name="grade_level" value="<?php echo htmlspecialchars($student['grade_level']); ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Student</button>
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