<?php
session_start();
require_once 'config.php'; // Ensure this sets up the PDO connection in $pdo

// Get the class from the URL query parameter
$classCode = isset($_GET['class']) ? $_GET['class'] : 'Unknown';

// Optionally, parse the class code into components if needed
if (strlen($classCode) == 5) {
    $dept     = substr($classCode, 0, 2); // e.g., "CO"
    $semester = substr($classCode, 2, 1); // e.g., "6"
    $scheme   = substr($classCode, 3, 1); // e.g., "I"
    $division = substr($classCode, 4, 1); // e.g., "C"
} else {
    $dept = $semester = $scheme = $division = 'N/A';
}

// Get search query if provided
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build the SQL query for fetching students in this class
if ($search !== '') {
    $stmt = $pdo->prepare("SELECT roll_no, name, email FROM students WHERE class = ? AND roll_no LIKE ?");
    $stmt->execute([$classCode, "%$search%"]);
} else {
    $stmt = $pdo->prepare("SELECT roll_no, name, email FROM students WHERE class = ?");
    $stmt->execute([$classCode]);
}
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($classCode); ?> - Class Details</title>
    <!-- Material Symbols for icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <!-- Your existing CSS files (adjust paths as needed) -->
    <link rel="stylesheet" href="css_files/classDetails.css">
    <link rel="stylesheet" href="css_files/menu.css">
    <script src="https://unpkg.com/lenis@1.2.3/dist/lenis.min.js"></script>
   
</head>
<body>
<div id="main">
    <video src="videos/video1.mp4" loop muted autoplay></video>
    <!-- Navbar -->
    <nav id="portfolink-navbar">
        <h1>PortfolinK</h1>
        <div id="portfolink-part2">
            <a href="contact.php">Contact Us</a>
            <a href="dashboard.php">Dashboard</a>
        </div>
    </nav>
    <!-- Navbar end -->

    <div id="page1">
        <h1><?php echo htmlspecialchars($classCode); ?> - Class Details</h1>
        <ul>
            <li><strong>Department:</strong> <?php echo htmlspecialchars($dept); ?></li>
            <li><strong>Semester:</strong> <?php echo htmlspecialchars($semester); ?></li>
            <li><strong>Exam Scheme:</strong> <?php echo htmlspecialchars($scheme); ?></li>
            <li><strong>Division:</strong> <?php echo htmlspecialchars($division); ?></li>
        </ul>
    </div>

    <div id="page2">
        <h2 class="portfolink-classDetails-header" style="color: #ffffff; font-size: 7vw; text-align: center; letter-spacing: 0.3vw;">Student List:</h2>
        <!-- Search form -->
        <form method="get" action="classDetails.php" style="text-align: center; margin-bottom: 20px;">
    <!-- Pass along the current class parameter -->
    <input type="hidden" name="class" value="<?php echo htmlspecialchars($classCode); ?>">
    <label for="search" style="color: white; font-size: 1.5vw;">Search Roll No:</label>
    <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" style="border: solid thin white; color: white; background: transparent; font-size: 1.5vw; padding: 5px; font-family: 'poppins','sans-serif' !important;">
    <button type="submit" style="font-size: 1.5vw; padding: 5px 10px;">Search</button>
</form>


        <table>
            <thead>
                <tr>
                    <th>Roll No</th>
                    <th>Name</th>
                    <th>Email ID</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <a href="portfolio.php?roll_no=<?php echo urlencode($student['roll_no']); ?>">
                                    <?php echo htmlspecialchars($student['roll_no']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>">
                                    <?php echo htmlspecialchars($student['email']); ?>
                                </a>
                            </td>
                            <td>
                                <a href="deleteStudent.php?roll_no=<?php echo urlencode($student['roll_no']); ?>&class=<?php echo urlencode($classCode); ?>" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No students found in this class.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script src="javascript_files/script.js"></script>
</body>
</html>
