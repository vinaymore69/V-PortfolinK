<?php
session_start();
session_destroy();

// Set headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logging Out...</title>
</head>
<body>
    <script>
        // Redirect to login.php using location.replace() so that this page isn't saved in the browser history
        window.location.replace("login.php");
    </script>
    <!-- Fallback for browsers with JavaScript disabled -->
    <noscript>
        <meta http-equiv="refresh" content="0; url=login.php">
    </noscript>
</body>
</html>
