<?php

session_start();

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PortfolinK</title>
    <link rel="stylesheet" href="css_files/style.css">
    <link rel="stylesheet" href="css_files/menu.css">
    <link rel="stylesheet" href="css_files/footer.css">
    <script src="https://unpkg.com/lenis@1.2.3/dist/lenis.min.js"></script>
</head>

<body>

    <div id="main">
        <video src="videos/video1.mp4" loop muted autoplay></video>
        <nav id="portfolink-navbar">
            <h1>PortfolinK</h1>
            <div id="portfolink-part2">
                <a href="contact.php">Contact Us</a>
                <a href="dashboard.php">Dashboard</a>
            </div>
        </nav>
        <div id="page1">
            <h1>Welcome To PortfolinK</h1>
        </div>
        <div id="page2">
            <div id="portfolink-short-intro-left">
                <h2>PortfolinK</h2>
                <video src="videos/video3.mp4" loop muted autoplay></video>
            </div>
            <div id="portfolink-short-intro-right">
                <p>
                    <b>PortfoliK</b> is an innovative platform designed to help <b>students</b> create and showcase
                    their <b>portfolios effortlessly</b>. With a simple and unique <b>URL</b> structure, each student
                    gets a personalized portfolio page to highlight their <b>achievements, projects,</b> and
                    <b>skills</b>.

                </p>
                
                <p>
                    In today’s <b>digital</b> world, having an online portfolio is essential for students to stand out.
                    PortfoliK provides an <b>easy-to-use</b> solution that <b>dynamically</b> generates a professional
                    portfolio using a student’s roll number or name. <b>No complicated</b> setup—just visit your unique
                    URL and view your profile <b>instantly!</b>
                </p>
            </div>

        </div>
        <div id="footer">
            <h3>PortfolinK &copy; 2025</h3>
            <h4>Copyright © 2025 Vinay Prakash More ®</h4>
        </div>
    </div>

    <script src="javascript_files/script.js"></script>
</body>

</html>