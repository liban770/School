<?php
session_start();
include 'db.php';

$subjects = [];
$searchTerm = '';
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_subject'])) {
        $subject_name = trim($_POST['subject_name']);
        $teacher_id = $_POST['teacher_id'];

        // Validate input
        if (empty($subject_name) || empty($teacher_id)) {
            $errorMessage = "Both Subject Name and Teacher ID are required.";
        } elseif (!is_numeric($teacher_id)) {
            $errorMessage = "Teacher ID must be a number.";
        } else {
            try {
                $sql = "INSERT INTO Subjects (subject_name, teacher_id) VALUES (:subject_name, :teacher_id)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['subject_name' => $subject_name, 'teacher_id' => $teacher_id]);
                $successMessage = "Subject added successfully!";
            } catch (PDOException $e) {
                $errorMessage = "Error adding subject: " . htmlspecialchars($e->getMessage());
            }
        }
    }

    if (isset($_POST['update_subject'])) {
        $subject_id = $_POST['subject_id'];
        $subject_name = trim($_POST['subject_name']);
        $teacher_id = $_POST['teacher_id'];

        // Validate input
        if (empty($subject_name) || empty($teacher_id)) {
            $errorMessage = "Both Subject Name and Teacher ID are required.";
        } elseif (!is_numeric($teacher_id)) {
            $errorMessage = "Teacher ID must be a number.";
        } else {
            try {
                $sql = "UPDATE Subjects SET subject_name = :subject_name, teacher_id = :teacher_id WHERE subject_id = :subject_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'subject_name' => $subject_name,
                    'teacher_id' => $teacher_id,
                    'subject_id' => $subject_id
                ]);
                $successMessage = "Subject updated successfully!";
            } catch (PDOException $e) {
                $errorMessage = "Error updating subject: " . htmlspecialchars($e->getMessage());
            }
        }
    }

    if (isset($_POST['delete_subject'])) {
        $subject_id = $_POST['subject_id'];

        try {
            $sql = "DELETE FROM exams WHERE subject_id = :subject_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['subject_id' => $subject_id]);

            $sql = "DELETE FROM Subjects WHERE subject_id = :subject_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['subject_id' => $subject_id]);
            $successMessage = "Subject and its dependent records deleted successfully!";
        } catch (PDOException $e) {
            $errorMessage = "Error deleting subject: " . htmlspecialchars($e->getMessage());
        }
    }
}

if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    try {
        $sql = "SELECT * FROM Subjects WHERE subject_name LIKE :search";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search' => '%' . $searchTerm . '%']);
        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errorMessage = "Error fetching subjects: " . htmlspecialchars($e->getMessage());
    }
} else {
    try {
        $subjects = $pdo->query("SELECT * FROM Subjects")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errorMessage = "Error fetching subjects: " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Management</title>
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
    <h2 class="text-center">Add Subject</h2>
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

    <form method="POST" action="" class="mt-4">
        <input type="hidden" name="add_subject" value="1">
        <div class="mb-3">
            <label for="subject_name" class="form-label">Subject Name:</label>
            <input type="text" class="form-control" id="subject_name" name="subject_name">
        </div>
        <div class="mb-3">
            <label for="teacherid" class="form-label">Teacher ID:</label>
            <input type="number" class="form-control" id="teacherid" name="teacher_id">
        </div>
        <button type="submit" class="btn btn-primary">Add Subject</button>
    </form>

    <h2 class="text-center mt-5">Manage Subjects</h2>
    
    <div class="mb-3">
        <form method="GET" action="">
            <label for="search" class="form-label">Search Subjects:</label>
            <div class="input-group">
                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Enter subject name">
                <button type="submit" class="btn btn-outline-primary">Search</button>
            </div>
        </form>
    </div>

    <table class="table mt-4">
        <thead>
            <tr>
                <th>Subject Name</th>
                <th>Teacher ID</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subjects as $subject): ?>
            <tr>
                <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                <td><?php echo htmlspecialchars($subject['teacher_id']); ?></td>
                <td>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateModal<?php echo $subject['subject_id']; ?>">Update</button>
                    <form method="POST" action="" class="d-inline">
                        <input type="hidden" name="subject_id" value="<?php echo $subject['subject_id']; ?>">
                        <input type="hidden" name="delete_subject" value="1">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>

            <div class="modal fade" id="updateModal<?php echo $subject['subject_id']; ?>" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateModalLabel">Update Subject</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <input type="hidden" name="subject_id" value="<?php echo $subject['subject_id']; ?>">
                                <input type="hidden" name="update_subject" value="1">
                                <div class="mb-3">
                                    <label for="subject_name" class="form-label">Subject Name:</label>
                                    <input type="text" class="form-control" id="subject_name" name="subject_name" value="<?php echo htmlspecialchars($subject['subject_name']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="teacherid" class="form-label">Teacher ID:</label>
                                    <input type="number" class="form-control" id="teacher_id" name="teacher_id" value="<?php echo htmlspecialchars($subject['teacher_id']); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Subject</button>
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