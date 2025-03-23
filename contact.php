

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | PortfolinK</title>
    <link rel="stylesheet" href="css_files/contact.css">
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
        <!-- Navbar End -->

        <div id="page1">
            <!-- Contact Us Form -->
            <div class="portfolink-contact-form">
                <h2>Contact Us</h2>
                <form action="process_contact.php" method="post">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>

                    <label for="address">Tittle:</label>
                    <input type="text" id="tittle" name="tittle">

                    <label for="enquiry">Enquiry Content:</label>
                    <textarea id="enquiry" name="enquiry" rows="5"  required ed ></textarea>

                    <button type="submit">Submit</button>
                </form>
            </div>
        </div>
 
        <script src="javascript_files/script.js"></script>
    </div>
</body>
</html>
