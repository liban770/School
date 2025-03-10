<?php
session_start();
include 'db.php'; 

$classes = [];
$searchTerm = '';
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_class'])) {
        $class_name = $_POST['class_name'];
        $teacher_id = isset($_POST['teacher_id']) ? $_POST['teacher_id'] : null;

        if (!empty($class_name) && $teacher_id !== null) {
            try {
                $sql = "INSERT INTO Classes (class_name, teacher_id) VALUES (:class_name, :teacher_id)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'class_name' => $class_name,
                    'teacher_id' => $teacher_id
                ]);
                $message = "Class added successfully!";
                $messageType = "success";
            } catch (PDOException $e) {
                $message = "Error adding class: " . htmlspecialchars($e->getMessage());
                $messageType = "danger";
            }
        } else {
            $message = "Class name and Teacher ID are required.";
            $messageType = "danger";
        }
    }

    if (isset($_POST['update_class'])) {
        $class_id = $_POST['class_id'];
        $class_name = $_POST['class_name'];
        $teacher_id = isset($_POST['teacher_id']) ? $_POST['teacher_id'] : null;

        if (!empty($class_name) && $teacher_id !== null) {
            try {
                $sql = "UPDATE Classes SET class_name = :class_name, teacher_id = :teacher_id WHERE class_id = :class_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'class_name' => $class_name,
                    'teacher_id' => $teacher_id,
                    'class_id' => $class_id
                ]);
                $message = "Class updated successfully!";
                $messageType = "success";
            } catch (PDOException $e) {
                $message = "Error updating class: " . htmlspecialchars($e->getMessage());
                $messageType = "danger";
            }
        } else {
            $message = "Class name and Teacher ID are required.";
            $messageType = "danger";
        }
    }

    if (isset($_POST['delete_class'])) {
        $class_id = $_POST['class_id'];

        try {
            $sql = "DELETE FROM attendance WHERE class_id = :class_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['class_id' => $class_id]);

            $sql = "DELETE FROM Classes WHERE class_id = :class_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['class_id' => $class_id]);

            $message = "Class deleted successfully!";
            $messageType = "danger";
        } catch (PDOException $e) {
            $message = "Error deleting class: " . htmlspecialchars($e->getMessage());
            $messageType = "danger";
        }
    }
}

if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    try {
        $sql = "SELECT * FROM Classes WHERE class_name LIKE :search";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search' => '%' . $searchTerm . '%']);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Error fetching classes: " . htmlspecialchars($e->getMessage());
        $messageType = "danger";
    }
} else {
    try {
        $classes = $pdo->query("SELECT * FROM Classes")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Error fetching classes: " . htmlspecialchars($e->getMessage());
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-5">
    <h2 class="text-center">Add Class</h2>
    <?php if ($message): ?>
        <div class='alert alert-<?php echo $messageType; ?> alert-dismissible fade show' role='alert'>
            <?php echo $message; ?>
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>
    <?php endif; ?>
    <form method="POST" action="" class="mt-4">
        <input type="hidden" name="add_class" value="1">
        <div class="mb-3">
            <label for="class_name" class="form-label">Class Name:</label>
            <input type="text" class="form-control" id="class_name" name="class_name">
        </div>
        <div class="mb-3">
            <label for="teacher_id" class="form-label">Teacher ID:</label>
            <input type="text" class="form-control" id="teacher_id" name="teacher_id">
        </div>
        <button type="submit" class="btn btn-primary">Add Class</button>
    </form>

    <h2 class="text-center mt-5">Manage Classes</h2>

    <div class="mb-3">
        <form method="GET" action="">
            <label for="search" class="form-label">Search Classes:</label>
            <div class="input-group">
                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Enter class name">
                <button type="submit" class="btn btn-outline-primary">Search</button>
            </div>
        </form>
    </div>

    <table class="table mt-4">
        <thead>
            <tr>
                <th>Class Name</th>
                <th>Teacher ID</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($classes as $class): ?>
            <tr>
                <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                <td><?php echo htmlspecialchars($class['teacher_id']); ?></td>
                <td>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateModal<?php echo $class['class_id']; ?>">Update</button>
                    <form method="POST" action="" class="d-inline">
                        <input type="hidden" name="class_id" value="<?php echo $class['class_id']; ?>">
                        <input type="hidden" name="delete_class" value="1">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>

            <div class="modal fade" id="updateModal<?php echo $class['class_id']; ?>" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateModalLabel">Update Class</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <input type="hidden" name="class_id" value="<?php echo $class['class_id']; ?>">
                                <input type="hidden" name="update_class" value="1">
                                <div class="mb-3">
                                    <label for="class_name" class="form-label">Class Name:</label>
                                    <input type="text" class="form-control" id="class_name" name="class_name" value="<?php echo htmlspecialchars($class['class_name']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="teacher_id" class="form-label">Teacher ID:</label>
                                    <input type="text" class="form-control" id="teacher_id" name="teacher_id" value="<?php echo htmlspecialchars($class['teacher_id']); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Class</button>
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