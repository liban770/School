<?php
session_start();
include 'db.php';

$grades = [];
$searchTerm = '';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_grade'])) {
        $student_id = $_POST['id'];
        $class_id = $_POST['class_id'];
        $grade = $_POST['grade'];

        // Validate input
        if ($student_id === null || $class_id === null || empty($grade)) {
            $message = "All fields are required.";
            $messageType = "danger";
        } elseif (!is_numeric($student_id) || !is_numeric($class_id) || !is_numeric($grade)) {
            $message = "Student ID, Class ID, and Grade must be numeric.";
            $messageType = "danger";
        } else {
            try {
                $sql = "INSERT INTO grade (id, class_id, grade) VALUES (:id, :class_id, :grade)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'id' => $student_id,
                    'class_id' => $class_id,
                    'grade' => $grade
                ]);
                $message = "Grade added successfully!";
                $messageType = "success";
            } catch (PDOException $e) {
                $message = "Error adding grade: " . htmlspecialchars($e->getMessage());
                $messageType = "danger";
            }
        }
    }

    if (isset($_POST['update_grade'])) {
        $grade_id = $_POST['grade_id'];
        $student_id = $_POST['id'];
        $class_id = $_POST['class_id'];
        $grade = $_POST['grade'];

        // Validate input
        if ($student_id === null || $class_id === null || empty($grade)) {
            $message = "All fields are required.";
            $messageType = "danger";
        } elseif (!is_numeric($student_id) || !is_numeric($class_id) || !is_numeric($grade)) {
            $message = "Student ID, Class ID, and Grade must be numeric.";
            $messageType = "danger";
        } else {
            try {
                $sql = "UPDATE grade SET id = :id, class_id = :class_id, grade = :grade WHERE grade_id = :grade_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'id' => $student_id,
                    'class_id' => $class_id,
                    'grade' => $grade,
                    'grade_id' => $grade_id
                ]);
                $message = "Grade updated successfully!";
                $messageType = "success";
            } catch (PDOException $e) {
                $message = "Error updating grade: " . htmlspecialchars($e->getMessage());
                $messageType = "danger";
            }
        }
    }

    if (isset($_POST['delete_grade'])) {
        $grade_id = $_POST['grade_id'];

        try {
            $sql = "DELETE FROM grade WHERE grade_id = :grade_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['grade_id' => $grade_id]);
            $message = "Grade deleted successfully!";
            $messageType = "danger";
        } catch (PDOException $e) {
            $message = "Error deleting grade: " . htmlspecialchars($e->getMessage());
            $messageType = "danger";
        }
    }
}

if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    try {
        $sql = "SELECT * FROM grade WHERE id LIKE :search OR class_id LIKE :search";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search' => '%' . $searchTerm . '%']);
        $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Error fetching grades: " . htmlspecialchars($e->getMessage());
        $messageType = "danger";
    }
} else {
    try {
        $grades = $pdo->query("SELECT * FROM grade")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Error fetching grades: " . htmlspecialchars($e->getMessage());
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Add Grade</h2>
    <?php if ($message): ?>
        <div class='alert alert-<?php echo $messageType; ?> alert-dismissible fade show' role='alert'>
            <?php echo $message; ?>
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>
    <?php endif; ?>
    <form method="POST" action="" class="mt-4">
        <input type="hidden" name="add_grade" value="1">
        <div class="mb-3">
            <label for="student_id" class="form-label">Student ID:</label>
            <input type="text" class="form-control" id="id" name="id">
        </div>
        <div class="mb-3">
            <label for="class_id" class="form-label">Class ID:</label>
            <input type="text" class="form-control" id="class_id" name="class_id">
        </div>
        <div class="mb-3">
            <label for="grade" class="form-label">Grade:</label>
            <input type="number" class="form-control" id="grade" name="grade">
        </div>
        <button type="submit" class="btn btn-primary">Add Grade</button>
    </form>

    <h2 class="text-center mt-5">Manage Grades</h2>

    <div class="mb-3">
        <form method="GET" action="">
            <label for="search" class="form-label">Search Grades:</label>
            <div class="input-group">
                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Enter Student ID or Class ID">
                <button type="submit" class="btn btn-outline-primary">Search</button>
            </div>
        </form>
    </div>

    <table class="table mt-4">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Class ID</th>
                <th>Grade</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($grades as $grade): ?>
            <tr>
                <td><?php echo htmlspecialchars($grade['id']); ?></td>
                <td><?php echo htmlspecialchars($grade['class_id']); ?></td>
                <td><?php echo htmlspecialchars($grade['grade']); ?></td>
                <td>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateModal<?php echo $grade['grade_id']; ?>">Update</button>
                    <form method="POST" action="" class="d-inline">
                        <input type="hidden" name="grade_id" value="<?php echo $grade['grade_id']; ?>">
                        <input type="hidden" name="delete_grade" value="1">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>

            <div class="modal fade" id="updateModal<?php echo $grade['grade_id']; ?>" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateModalLabel">Update Grade</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <input type="hidden" name="grade_id" value="<?php echo $grade['grade_id']; ?>">
                                <input type="hidden" name="update_grade" value="1">
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">Student ID:</label>
                                    <input type="text" class="form-control" id="id" name="id" value="<?php echo htmlspecialchars($grade['id']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">Class ID:</label>
                                    <input type="text" class="form-control" id="class_id" name="class_id" value="<?php echo htmlspecialchars($grade['class_id']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="grade" class="form-label">Grade:</label>
                                    <input type="number" class="form-control" id="grade" name="grade" value="<?php echo htmlspecialchars($grade['grade']); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Grade</button>
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