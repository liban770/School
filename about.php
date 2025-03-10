<?php
// about.php
$pageTitle = "About Us";
$schoolName = "Your School Name";
$schoolDescription = "We are dedicated to providing high-quality education and nurturing the future leaders of tomorrow.";
$teamMembers = [
    ["name" => "John Doe", "position" => "Principal"],
    ["name" => "Jane Smith", "position" => "Vice Principal"],
    ["name" => "Emily Johnson", "position" => "Head of Mathematics"],
    ["name" => "Michael Brown", "position" => "Head of Science"],
];
$testimonials = [
    [
        "name" => "Alice Green",
        "text" => "KidKinder has transformed my child's education! The teachers are fantastic.",
        "image" => "img/testimonial-3.jpg" // Path to Alice's image
    ],
    [
        "name" => "David White",
        "text" => "A wonderful environment for learning. Highly recommend!",
        "image" => "img/testimonial-1.jpg" // Path to David's image
    ],
    [
        "name" => "Laura Black",
        "text" => "My child loves going to school, and I can see the improvement every day.",
        "image" => "img/testimonial-2.jpg" // Path to Laura's image
    ],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('ba.jpg'); /* Background image related to education */
            background-size: cover; /* Cover the entire viewport */
            color: white; /* Text color for better contrast */
        }
        .content {
            background-color: rgba(0, 0, 0, 0.7); /* Semi-transparent background for text */
            padding: 20px;
            border-radius: 10px;
        }
        .team-member {
            text-align: center; /* Center align team member information */
        }
        .testimonial {
            display: flex; /* Use flexbox for alignment */
            align-items: center; /* Center vertically */
            margin-bottom: 15px; /* Space between testimonials */
        }
        .testimonial img {
            width: 80px; /* Set width for images */
            height: 80px; /* Set height for images */
            border-radius: 50%; /* Make images circular */
            margin-right: 15px; /* Space between image and text */
        }
    </style>
</head>
<body>
    <div class="container mt-5 content">
        <h1 class="text-center"><?php echo htmlspecialchars($schoolName); ?></h1>
        <p class="text-center"><?php echo htmlspecialchars($schoolDescription); ?></p>

        <h2 class="mt-5 text-center">Meet Our Team</h2>
        <ul class="list-group">
            <?php foreach ($teamMembers as $member): ?>
                <li class="list-group-item team-member">
                    <strong><?php echo htmlspecialchars($member['name']); ?></strong>, <?php echo htmlspecialchars($member['position']); ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <h2 class="mt-5 text-center">Testimonials</h2>
        <div class="list-group">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="list-group-item testimonial">
                    <img src="<?php echo htmlspecialchars($testimonial['image']); ?>" alt="<?php echo htmlspecialchars($testimonial['name']); ?>">
                    <div>
                        <p>"<?php echo htmlspecialchars($testimonial['text']); ?>"</p>
                        <small>- <?php echo htmlspecialchars($testimonial['name']); ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <h2 class="mt-5 text-center">Contact Us</h2>
        <p class="text-center">If you have any questions, feel free to <a href="contact.php" class="text-light">contact us</a>.</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>