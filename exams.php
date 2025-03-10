<?php
session_start();
include 'db.php';

$exams = [];
$searchTerm = '';
$message = '';
$messageType = '';

function subjectExists($pdo, $subject_id) {
    $sql = "SELECT COUNT(*) FROM subjects WHERE subject_id = :subject_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['subject_id' => $subject_id]);
    return $stmt->fetchColumn() > 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_exam'])) {
        $exam_name = $_POST['exam_name'];
        $subject_id = $_POST['subject_id'];
        $exam_date = $_POST['exam_date'];

        // Validate input
        if (empty($exam_name) || empty($subject_id) || empty($exam_date)) {
            $message = "All fields are required.";
            $messageType = "danger";
        } elseif (!is_numeric($subject_id)) {
            $message = "Subject ID must be numeric.";
            $messageType = "danger";
        } elseif (!subjectExists($pdo, $subject_id)) {
            $message = "Error adding exam: Subject ID does not exist.";
            $messageType = "danger";
        } else {
            try {
                $sql = "INSERT INTO Exams (exam_name, subject_id, exam_date) VALUES (:exam_name, :subject_id, :exam_date)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['exam_name' => $exam_name, 'subject_id' => $subject_id, 'exam_date' => $exam_date]);
                $message = "Exam added successfully!";
                $messageType = "success";
            } catch (PDOException $e) {
                $message = "Error adding exam: " . htmlspecialchars($e->getMessage());
                $messageType = "danger";
            }
        }
    }

    if (isset($_POST['update_exam'])) {
        $exam_id = $_POST['exam_id'];
        $exam_name = $_POST['exam_name'];
        $subject_id = $_POST['subject_id'];
        $exam_date = $_POST['exam_date'];

        // Validate input
        if (empty($exam_name) || empty($subject_id) || empty($exam_date)) {
            $message = "All fields are required.";
            $messageType = "danger";
        } elseif (!is_numeric($subject_id)) {
            $message = "Subject ID must be numeric.";
            $messageType = "danger";
        } elseif (!subjectExists($pdo, $subject_id)) {
            $message = "Error updating exam: Subject ID does not exist.";
            $messageType = "danger";
        } else {
            try {
                $sql = "UPDATE Exams SET exam_name = :exam_name, subject_id = :subject_id, exam_date = :exam_date WHERE exam_id = :exam_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'exam_name' => $exam_name,
                    'subject_id' => $subject_id,
                    'exam_date' => $exam_date,
                    'exam_id' => $exam_id
                ]);
                $message = "Exam updated successfully!";
                $messageType = "success";
            } catch (PDOException $e) {
                $message = "Error updating exam: " . htmlspecialchars($e->getMessage());
                $messageType = "danger";
            }
        }
    }

    if (isset($_POST['delete_exam'])) {
        $exam_id = $_POST['exam_id'];
        try {
            $sql = "DELETE FROM Exams WHERE exam_id = :exam_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['exam_id' => $exam_id]);
            $message = "Exam deleted successfully!";
            $messageType = "danger";
        } catch (PDOException $e) {
            $message = "Error deleting exam: " . htmlspecialchars($e->getMessage());
            $messageType = "danger";
        }
    }
}

if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    try {
        $sql = "SELECT * FROM Exams WHERE exam_name LIKE :search";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search' => '%' . $searchTerm . '%']);
        $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Error fetching exams: " . htmlspecialchars($e->getMessage());
        $messageType = "danger";
    }
} else {
    try {
        $exams = $pdo->query("SELECT * FROM Exams")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Error fetching exams: " . htmlspecialchars($e->getMessage());
        $messageType = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Add Exam</h2>
    <?php if ($message): ?>
        <div class='alert alert-<?php echo $messageType; ?> alert-dismissible fade show' role='alert'>
            <?php echo $message; ?>
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>
    <?php endif; ?>
    <form method="POST" action="" class="mt-4">
        <input type="hidden" name="add_exam" value="1">
        <div class="mb-3">
            <label for="exam_name" class="form-label">Exam Name:</label>
            <input type="text" class="form-control" id="exam_name" name="exam_name">
        </div>
        <div class="mb-3">
            <label for="subject_id" class="form-label">Subject ID:</label>
            <input type="number" class="form-control" id="subject_id" name="subject_id">
        </div>
        <div class="mb-3">
            <label for="exam_date" class="form-label">Exam Date:</label>
            <input type="date" class="form-control" id="exam_date" name="exam_date">
        </div>
        <button type="submit" class="btn btn-primary">Add Exam</button>
    </form>

    <h2 class="text-center mt-5">Manage Exams</h2>
    <div class="mb-3">
        <form method="GET" action="">
            <label for="search" class="form-label">Search Exams:</label>
            <div class="input-group">
                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Enter exam name">
                <button type="submit" class="btn btn-outline-primary">Search</button>
            </div>
        </form>
    </div>
    <table class="table mt-4">
        <thead>
            <tr>
                <th>Exam Name</th>
                <th>Subject ID</th>
                <th>Exam Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($exams as $exam): ?>
            <tr>
                <td><?php echo htmlspecialchars($exam['exam_name']); ?></td>
                <td><?php echo htmlspecialchars($exam['subject_id']); ?></td>
                <td><?php echo htmlspecialchars($exam['exam_date']); ?></td>
                <td>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateModal<?php echo $exam['exam_id']; ?>">Update</button>
                    <form method="POST" action="" class="d-inline">
                        <input type="hidden" name="exam_id" value="<?php echo $exam['exam_id']; ?>">
                        <input type="hidden" name="delete_exam" value="1">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <div class="modal fade" id="updateModal<?php echo $exam['exam_id']; ?>" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateModalLabel">Update Exam</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <input type="hidden" name="exam_id" value="<?php echo $exam['exam_id']; ?>">
                                <input type="hidden" name="update_exam" value="1">
                                <div class="mb-3">
                                    <label for="exam_name" class="form-label">Exam Name:</label>
                                    <input type="text" class="form-control" id="exam_name" name="exam_name" value="<?php echo htmlspecialchars($exam['exam_name']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="subject_id" class="form-label">Subject ID:</label>
                                    <input type="number" class="form-control" id="subject_id" name="subject_id" value="<?php echo htmlspecialchars($exam['subject_id']); ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="exam_date" class="form-label">Exam Date:</label>
                                    <input type="date" class="form-control" id="exam_date" name="exam_date" value="<?php echo htmlspecialchars($exam['exam_date']); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">Update Exam</button>
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