<?php
require_once 'config.php'; // Contains the PDO connection in $pdo

// Get the roll number from the URL query parameter (or via URL rewriting)
$roll_no = isset($_GET['roll_no']) ? $_GET['roll_no'] : null;
if (!$roll_no) {
    echo "No roll number provided.";
    exit;
}

// Fetch student details using the roll number
$studentStmt = $pdo->prepare("SELECT * FROM students WHERE roll_no = ?");
$studentStmt->execute([$roll_no]);
$student = $studentStmt->fetch(PDO::FETCH_ASSOC);
if (!$student) {
    echo "Student not found.";
    exit;
}

// Fetch related skills
$skillsStmt = $pdo->prepare("SELECT skill_name, skill_level FROM skills WHERE student_id = ?");
$skillsStmt->execute([$student['id']]);
$skills = $skillsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch related certifications
$certStmt = $pdo->prepare("SELECT certificate_name, issuing_organization FROM certifications WHERE student_id = ?");
$certStmt->execute([$student['id']]);
$certifications = $certStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch related projects
$projStmt = $pdo->prepare("SELECT project_name, description FROM projects WHERE student_id = ?");
$projStmt->execute([$student['id']]);
$projects = $projStmt->fetchAll(PDO::FETCH_ASSOC);

// Determine which CSS file to load (custom vs. default)
$customCSSPath = "uploads/custom_css/" . $student['roll_no'] . "_cssfile.css";
$cssToLoad = file_exists($customCSSPath) ? $customCSSPath : "css_files/portfolio.css";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($student['name']); ?>'s Portfolio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Use absolute paths for CSS to ensure proper loading -->
    <link rel="stylesheet" href="/PortfolinK/<?php echo htmlspecialchars($cssToLoad); ?>">
    <link rel="stylesheet" href="/PortfolinK/css_files/menu.css">
    <script src="https://unpkg.com/lenis@1.2.3/dist/lenis.min.js"></script>
    <script src="https://kit.fontawesome.com/a9a67c95a9.js" crossorigin="anonymous"></script>
</head>
<body>
<div id="main">
    <nav id="portfolink-navbar">
        <h1>PortfolinK</h1>
    </nav>
    <div id="page1">
        <?php
            // Build the server file path using __DIR__
            $profileImageFile = __DIR__ . "/uploads/profile_images/" . $student['profile_pic'];
            // Check if the file exists on the server or if the student's profile picture is empty.
            if (!file_exists($profileImageFile) || empty($student['profile_pic'])) {
                $profileImageURL = "/PortfolinK/images_file/default.jpg";
            } else {
                $profileImageURL = "/PortfolinK/uploads/profile_images/" . $student['profile_pic'];
            }
        ?>
        <img src="<?php echo htmlspecialchars($profileImageURL); ?>" alt="<?php echo htmlspecialchars($student['name']); ?> Profile" onerror="this.onerror=null; this.src='/PortfolinK/images_file/default.jpg';">
        <div id="portfolink-main-content">
            <h1><?php echo htmlspecialchars($student['name']); ?></h1>
            <h6>
                <span><?php echo htmlspecialchars($student['class']); ?></span>
                <span><?php echo htmlspecialchars($student['roll_no']); ?></span>
                <span><?php echo htmlspecialchars($student['email']); ?></span>
            </h6>
            <div id="portfolink-about">
                <h4>About Me:</h4>
                <p><?php echo htmlspecialchars($student['bio']); ?></p>
                <h4>CGPA: <span><?php echo htmlspecialchars($student['cgpa']); ?></span></h4>
            </div>
            <div id="portfolink-skills-certifications">
                <div id="portfolink-skills">
                    <h4>Skills:</h4>
                    <ul>
                        <?php
                        if (!empty($skills)) {
                            foreach ($skills as $skill) {
                                echo "<li>" . htmlspecialchars($skill['skill_name']) . " | " . htmlspecialchars($skill['skill_level']) . "</li>";
                            }
                        } else {
                            echo "<li>No skills available.</li>";
                        }
                        ?>
                    </ul>
                </div>
                <div id="portfolink-certifications">
                    <h4>Certifications:</h4>
                    <ul>
                        <?php
                        if (!empty($certifications)) {
                            foreach ($certifications as $cert) {
                                echo "<li>" . htmlspecialchars($cert['certificate_name']) . " | " . htmlspecialchars($cert['issuing_organization']) . "</li>";
                            }
                        } else {
                            echo "<li>No certifications available.</li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div id="portfolink-projects">
                <h4>Projects:</h4>
                <ul>
                    <?php
                    if (!empty($projects)) {
                        foreach ($projects as $proj) {
                            echo "<li><strong>Title:</strong> " . htmlspecialchars($proj['project_name']) . "<br>
                                  <strong>Description:</strong> " . htmlspecialchars($proj['description']) . "</li>";
                        }
                    } else {
                        echo "<li>No projects available.</li>";
                    }
                    ?>
                </ul>
            </div>
            <div id="portfolink-socials">
                <h4>Connect with Me:</h4>
                <ul>
                    <li>
                        <a href="<?php echo htmlspecialchars($student['linkedin']); ?>" target="_blank">
                            <i class="fa-brands fa-linkedin"></i> LinkedIn
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo htmlspecialchars($student['github']); ?>" target="_blank">
                            <i class="fa-brands fa-github"></i> GitHub
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<script src="/PortfolinK/javascript_files/script.js"></script>
</body>
</html>
