<?php
session_start();
require_once 'config.php'; // Contains the PDO connection in $pdo

// Prevent caching so the browser always requests a fresh page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Query the database for class counts
try {
    $stmt = $pdo->query("SELECT class, COUNT(*) as student_count FROM students GROUP BY class ORDER BY class ASC");
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $classes = [];
    $error = "Error fetching class data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Dashboard | PortfolinK</title>
    <script>
        // Force page reload if loaded from cache (e.g., using the back button)
        window.addEventListener("pageshow", function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
    <!-- Material Symbols for icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <!-- Your existing CSS files -->
    <link rel="stylesheet" href="css_files/dashboard.css">
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
            <h1 id="portfolink-dashboard-heading">
                Welcome to the Dashboard, <?php echo htmlspecialchars($_SESSION['username']); ?>!
            </h1>

            <!-- Button to go to feedData.php (Above Logout) -->
            <a href="feedData.php" id="logout" style="margin-bottom: 1vw;">Go to Feed Data &nbsp; <span class="material-symbols-outlined">
post_add
</span></a>

            <a href="logout.php" id="logout">Logout&nbsp;<span class="material-symbols-outlined">logout</span></a>
        </div>

        <div id="page2">
            <h4>Class Overview</h4>
            <?php if (isset($error)): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>
            <div class="card-container">
                <?php if (!empty($classes)): ?>
                    <?php foreach ($classes as $class): ?>
                        <a href="classDetails.php?class=<?php echo urlencode($class['class']); ?>" style="text-decoration: none; color: inherit;">
                            <div class="class-card">
                                <h3><?php echo htmlspecialchars($class['class']); ?></h3>
                                <p><?php echo htmlspecialchars($class['student_count']); ?> students</p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No class data available.</p>
                <?php endif; ?>
            </div>

            <!-- Button to go to feedData.php (Below Class Overview) -->
            <div class="feed-data-button-container">
                <a href="feedData.php" class="feed-data-button">Go to Feed Data</a>
            </div>

        </div>

    </div>

    <script src="javascript_files/script.js"></script>
</body>
</html>
