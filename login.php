<?php
session_start();
require_once 'config.php'; // Include the database configuration

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Prepare a statement to select the user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify the password using password_verify()
    if ($user && password_verify($password, $user['password'])) {
        // If credentials are correct, set session variables and redirect to dashboard
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        header("Location: dashboard.php");
        exit;
    } else {
        echo "<script>Invalid username or password.</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login | PortfolinK</title>
    <link rel="stylesheet" href="css_files/login.css">
    <link rel="stylesheet" href="css_files/menu.css">
    <script src="https://unpkg.com/lenis@1.2.3/dist/lenis.min.js"></script>

</head>

<body>




    <div id="main">
        <video src="videos/video1.mp4" loop muted autoplay></video>

        <!-- navbar  -->
        <nav id="portfolink-navbar">
            <h1>PortfolinK</h1>
            <div id="portfolink-part2">
                <a href="contact.php">Contact Us</a>
                <a href="dashboard.php">Dashboard</a>
            </div>
        </nav>
        <!-- navbar  end -->



        <div id="page1">
            <?php if (isset($error)): ?>
                <p style="color:red;"><?php echo $error; ?></p>
            <?php endif; ?>
           
            <form method="post" action="login.php">
            <h1 class="portfolink-login-heading" style=" text-align: right;
    font-size: 4vw;
    letter-spacing: 0.1vw;
    color: rgb(255, 255, 255);
    line-height: 5vw;">
                Login to PortfolinK
            </h1>
                <label>
                    Username:
                    <input type="text" name="username" minlength="5" required>
                </label>
                <label>
                    Password:
                    <input type="password" name="password" minlength="5" required>
                </label><br>
                <button type="submit">Login</button>
            </form>
        </div>


    </div>
    <script src="javascript_files/script.js"></script>
</body>

</html>