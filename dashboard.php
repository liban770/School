<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include('db.php');


$student_count = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $student_count = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Handle error (e.g. log it)
}

// Query to count total teachers
$teacher_count = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM teachers");
    $teacher_count = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Handle error
}

// Query to count upcoming exams
$upcoming_exam_count = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM exams WHERE exam_date > NOW()"); // Assuming you have an exam_date field
    $upcoming_exam_count = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Handle error
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('ba.jpg'); /* Replace with your image URL */
            background-size: cover;
            background-position: center;
            color: white; /* Text color for better contrast */
        }
        .overlay {
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black overlay */
            padding: 20px;
            border-radius: 10px;
        }
        h1, p {
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
        }
        footer {
            background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent background */
            color: white; /* Footer text color */
            padding: 10px 0;
            position: relative;
            bottom: 0;
            width: 100%;
        }
        .card {
            background-color: rgba(255, 255, 255, 0.1); /* Semi-transparent card */
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="https://abaarsotechuniversity.org/">ATU</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="students.php">Students</a></li>
                    <li class="nav-item"><a class="nav-link" href="teachers.php">Teachers</a></li>
                    <li class="nav-item"><a class="nav-link" href="class.php">Class</a></li>
                    <li class="nav-item"><a class="nav-link" href="grade.php">Grade</a></li>
                    <li class="nav-item"><a class="nav-link" href="sub.php">Subject</a></li>
                    <li class="nav-item"><a class="nav-link" href="attendace.php">Attendance</a></li>
                    <li class="nav-item"><a class="nav-link" href="exams.php">Exams</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5 overlay">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>Welcome to the Dashboard! We're excited to have you here. Explore the various sections to manage your activities and stay updated.</p>
        <div class="mt-3">
            <a href="read.html" class="btn btn-primary">Read More</a>
            <a href="learn.html" class="btn btn-warning">Learn More</a>
        </div>

        <!-- New Dynamic Cards Section -->
        <div class="row mt-4">
            <div class="col-md-4 mb-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Students</h5>
                        <p class="card-text"><?php echo $student_count; ?></p>
                        <a href="students.php" class="btn btn-info">View Students</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total Teachers</h5>
                        <p class="card-text"><?php echo $teacher_count; ?></p>
                        <a href="teachers.php" class="btn btn-info">View Teachers</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Upcoming Exams</h5>
                        <p class="card-text"><?php echo $upcoming_exam_count; ?></p>
                        <a href="exams.php" class="btn btn-info">View Exams</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center mt-5">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> ATU. All rights reserved.</p>
            <div class="mb-2">
                <a href="privacy.html" class="text-white">Privacy Policy</a> | 
                <a href="terms.html" class="text-white">Terms of Service</a>
            </div>
            <div class="mb-2">
                <a href="contact.html" class="text-white">Contact Us</a> | 
                <a href="about.php" class="text-white">About Us</a> | 
                <a href="location.html" class="text-white">Location</a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>