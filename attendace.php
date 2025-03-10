<?php
session_start();
include 'db.php'; 
$message = '';
$messageType = ''; 


$students = [];
try {
    $sql = "SELECT studentid, first_name, last_name FROM Students";
    $stmt = $pdo->query($sql);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $studentsAssoc = [];
    foreach ($students as $student) {
        $studentsAssoc[$student['studentid']] = $student['first_name'] . ' ' . $student['last_name'];
    }
} catch (PDOException $e) {
    $message = "Error fetching students: " . htmlspecialchars($e->getMessage());
    $messageType = "danger";
}

// Fetch all classes for the dropdown
$classes = [];
try {
    $sql = "SELECT class_id, class_name FROM classes";
    $stmt = $pdo->query($sql);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error fetching classes: " . htmlspecialchars($e->getMessage());
    $messageType = "danger";
}

// Handle adding attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_attendance'])) {
    $studentid = $_POST['studentid'];
    $class_id = $_POST['class_id'];
    $attendance_date = $_POST['attendance_date'];
    $status = $_POST['status'];
    if (!array_key_exists($studentid, $studentsAssoc)) {
        $message = "All feilsds are required.";
        $messageType = "danger";
    } else {
        try {
            $sql = "INSERT INTO attendance (studentid, class_id, attendance_date, status) VALUES (:studentid, :class_id, :attendance_date, :status)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'studentid' => $studentid,
                'class_id' => $class_id,
                'attendance_date' => $attendance_date,
                'status' => $status
            ]);

            $message = "Attendance added successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $message = "Error: " . htmlspecialchars($e->getMessage());
            $messageType = "danger";
        }
    }
}

// Handle updating attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_attendance'])) {
    $attendance_id = $_POST['attendance_id'];
    $class_id = $_POST['class_id'];
    $status = $_POST['status'];

    try {
        $sql = "UPDATE attendance SET class_id = :class_id, status = :status WHERE attendance_id = :attendance_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'class_id' => $class_id,
            'status' => $status,
            'attendance_id' => $attendance_id
        ]);

        $message = "Attendance updated successfully!";
        $messageType = "success";
    } catch (Exception $e) {
        $message = "Error updating attendance: " . htmlspecialchars($e->getMessage());
        $messageType = "danger";
    }
}

// Handle deleting attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_attendance'])) {
    $attendance_id = $_POST['attendance_id'];

    try {
        $sql = "DELETE FROM attendance WHERE attendance_id = :attendance_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['attendance_id' => $attendance_id]);

        $message = "Attendance record deleted successfully!";
        $messageType = "danger";
    } catch (Exception $e) {
        $message = "Error deleting attendance: " . htmlspecialchars($e->getMessage());
        $messageType = "danger";
    }
}

// Handle search
$searchTerm = '';
$attendances = [];
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    // Filtering students by name
    $filteredStudentIds = [];
    foreach ($students as $student) {
        if (stripos($student['first_name'], $searchTerm) !== false || stripos($student['last_name'], $searchTerm) !== false) {
            $filteredStudentIds[] = $student['studentid'];
        }
    }
    if (!empty($filteredStudentIds)) {
        $placeholders = implode(',', array_fill(0, count($filteredStudentIds), '?'));
        $sql = "SELECT attendance_id, studentid, attendance_date, status, class_id FROM attendance WHERE studentid IN ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($filteredStudentIds);
        $attendances = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} else {
    // Fetch all attendance records if no search term is provided
    $attendances = $pdo->query("SELECT attendance_id, studentid, attendance_date, status, class_id FROM attendance")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Add Attendance</h2>
    <?php if (!empty($message)): ?>
        <div class='alert alert-<?php echo $messageType; ?> alert-dismissible fade show' role='alert'>
            <?php echo $message; ?>
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>
    <?php endif; ?>
    <form method="POST" action="" class="mt-4">
        <input type="hidden" name="add_attendance" value="1">
        <div class="mb-3">
            <label for="studentid" class="form-label">Student ID:</label>
            <input type="text" class="form-control" id="studentid" name="studentid">
        </div>
        <div class="mb-3">
            <label for="class_id" class="form-label">Class Name:</label>
            <select class="form-select" id="class_id" name="class_id">
                <option value="">Select a class</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?php echo htmlspecialchars($class['class_id']); ?>"><?php echo htmlspecialchars($class['class_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="attendance_date" class="form-label">Attendance Date:</label>
            <input type="date" class="form-control" id="attendance_date" name="attendance_date">
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status:</label>
            <select class="form-select" id="status" name="status">
                <option value="Present">Present</option>
                <option value="Absent">Absent</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Attendance</button>
    </form>

    <h2 class="text-center mt-5">Manage Attendance</h2>
    
    <!-- Search Functionality -->
    <div class="mb-3">
        <form method="GET" action="">
            <label for="search" class="form-label">Search Attendance:</label>
            <div class="input-group">
                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Enter student name">
                <button type="submit" class="btn btn-outline-primary">Search</button>
            </div>
        </form>
    </div>

    <table class="table mt-4">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Class Name</th>
                <th>Attendance Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($attendances as $attendance): ?>
            <tr>
                <td><?php echo htmlspecialchars($studentsAssoc[$attendance['studentid']] ?? 'Unknown'); ?></td>
                <td><?php
                    // Fetch class name for the attendance record
                    $classId = $attendance['class_id'];
                    $className = '';
                    foreach ($classes as $class) {
                        if ($class['class_id'] == $classId) {
                            $className = $class['class_name'];
                            break;
                        }
                    }
                    echo htmlspecialchars($className);
                ?></td>
                <td><?php echo htmlspecialchars($attendance['attendance_date']); ?></td>
                <td><?php echo htmlspecialchars($attendance['status']); ?></td>
                <td>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateModal<?php echo $attendance['attendance_id']; ?>">Update</button>
                    <form method="POST" action="" class="d-inline">
                        <input type="hidden" name="attendance_id" value="<?php echo $attendance['attendance_id']; ?>">
                        <input type="hidden" name="delete_attendance" value="1">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>

            <!-- Update Modal -->
            <div class="modal fade" id="updateModal<?php echo $attendance['attendance_id']; ?>" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="updateModalLabel">Update Attendance</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <input type="hidden" name="attendance_id" value="<?php echo $attendance['attendance_id']; ?>">
                                <input type="hidden" name="update_attendance" value="1">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status:</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="Present" <?php echo ($attendance['status'] == 'Present') ? 'selected' : ''; ?>>Present</option>
                                        <option value="Absent" <?php echo ($attendance['status'] == 'Absent') ? 'selected' : ''; ?>>Absent</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">Class Name:</label>
                                    <select class="form-select" id="class_id" name="class_id">
                                        <?php foreach ($classes as $class): ?>
                                            <option value="<?php echo htmlspecialchars($class['class_id']); ?>" <?php echo ($class['class_id'] == $attendance['class_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($class['class_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Update Attendance</button>
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