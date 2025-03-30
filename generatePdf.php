<?php
session_start();
require_once 'config.php'; // Contains the PDO connection in $pdo
require_once "TCPDF-main/tcpdf.php";

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// If a roll number is provided, generate the PDF for that student.
if (isset($_GET['roll_no']) && !empty($_GET['roll_no'])) {
    $roll_no = $_GET['roll_no'];

    // Fetch student details from the students table.
    $stmt = $pdo->prepare("SELECT * FROM students WHERE roll_no = ?");
    $stmt->execute([$roll_no]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo "Student not found.";
        exit;
    }

    // Fetch related skills
    $skillsStmt = $pdo->prepare("SELECT skill_name, skill_level FROM skills WHERE student_id = ?");
    $skillsStmt->execute([$student['id']]);
    $skillsArr = $skillsStmt->fetchAll(PDO::FETCH_ASSOC);
    $skills = !empty($skillsArr)
        ? implode("<br>", array_map(fn($s) => "{$s['skill_name']} ({$s['skill_level']})", $skillsArr))
        : "No skills available.";

    // Fetch related projects
    $projStmt = $pdo->prepare("SELECT project_name, description, technologies, github_link, live_demo, project_image FROM projects WHERE student_id = ?");
    $projStmt->execute([$student['id']]);
    $projectsArr = $projStmt->fetchAll(PDO::FETCH_ASSOC);
    $projects = "";
    if (!empty($projectsArr)) {
        foreach ($projectsArr as $p) {
            $projects .= "<b>Title:</b> " . htmlspecialchars($p['project_name']) . "<br>";
            $projects .= "<b>Description:</b> " . htmlspecialchars($p['description']) . "<br>";
            $projects .= "<b>Technologies:</b> " . htmlspecialchars($p['technologies']) . "<br>";
            $projects .= "<b>GitHub:</b> " . htmlspecialchars($p['github_link']) . "<br>";
            $projects .= "<b>Live Demo:</b> " . htmlspecialchars($p['live_demo']) . "<br><br>";
        }
    } else {
        $projects = "No projects available.";
    }

    // Fetch related certifications
    $certStmt = $pdo->prepare("SELECT certificate_name, issuing_organization, issue_date, certificate_link FROM certifications WHERE student_id = ?");
    $certStmt->execute([$student['id']]);
    $certArr = $certStmt->fetchAll(PDO::FETCH_ASSOC);
    $certifications = "";
    if (!empty($certArr)) {
        foreach ($certArr as $c) {
            $certifications .= "<b>Certificate:</b> " . htmlspecialchars($c['certificate_name']) . "<br>";
            $certifications .= "<b>Organization:</b> " . htmlspecialchars($c['issuing_organization']) . "<br>";
            $certifications .= "<b>Issue Date:</b> " . htmlspecialchars($c['issue_date']) . "<br>";
            $certifications .= "<b>Link:</b> " . htmlspecialchars($c['certificate_link']) . "<br><br>";
        }
    } else {
        $certifications = "No certifications available.";
    }

    // Fetch related extracurricular activities
    $extraStmt = $pdo->prepare("SELECT activity_name, description FROM extracurricular WHERE student_id = ?");
    $extraStmt->execute([$student['id']]);
    $extraArr = $extraStmt->fetchAll(PDO::FETCH_ASSOC);
    $extracurricular = "";
    if (!empty($extraArr)) {
        foreach ($extraArr as $e) {
            $extracurricular .= "<b>Activity:</b> " . htmlspecialchars($e['activity_name']) . "<br>";
            $extracurricular .= "<b>Description:</b> " . htmlspecialchars($e['description']) . "<br><br>";
        }
    } else {
        $extracurricular = "No extracurricular activities available.";
    }

    // Fetch related achievements
    $achStmt = $pdo->prepare("SELECT achievement_name, description, award_date FROM achievements WHERE student_id = ?");
    $achStmt->execute([$student['id']]);
    $achArr = $achStmt->fetchAll(PDO::FETCH_ASSOC);
    $achievements = "";
    if (!empty($achArr)) {
        foreach ($achArr as $a) {
            $achievements .= "<b>Achievement:</b> " . htmlspecialchars($a['achievement_name']) . "<br>";
            $achievements .= "<b>Description:</b> " . htmlspecialchars($a['description']) . "<br>";
            $achievements .= "<b>Award Date:</b> " . htmlspecialchars($a['award_date']) . "<br><br>";
        }
    } else {
        $achievements = "No achievements available.";
    }

    // Determine the profile image path
    $profile_image_path = "uploads/profile_images/" . $student['profile_pic'];
    if (!file_exists($profile_image_path) || empty($student['profile_pic'])) {
        $profile_image_path = "images_file/default.jpg";
    }

    // Create a new TCPDF instance
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);
    $pdf->SetCreator("PortfolinK");
    $pdf->SetAuthor("Vinay");
    $pdf->SetTitle("Student Portfolio PDF");
    $pdf->SetSubject("Student Portfolio");
    $pdf->SetKeywords("Student, Portfolio, PDF, TCPDF");
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();

    // Add profile image (position may be adjusted)
    $pdf->Image($profile_image_path, 80, 10, 50, 50, '', '', '', false, 300, '', false, false, 1, false, false, false);
    $pdf->Ln(55);

    // Build the HTML content for the PDF
    $html = <<<EOD
    <h2 style="text-align:center;">Student Portfolio</h2>
    <hr>
    <h3 style="text-align:center;">{$student['name']}</h3>
    <h4 style="text-align:center;">Roll No: {$student['roll_no']} | Email: {$student['email']} | Phone: {$student['phone']}</h4>
    <h4 style="text-align:center;">College: {$student['college']} | Course: {$student['course']} | Class: {$student['class']}</h4>
    <h4 style="text-align:center;">Year of Study: {$student['year_of_study']} | CGPA: {$student['cgpa']}</h4>
    <hr>
    <h3>Bio</h3>
    <p>{$student['bio']}</p>
    <hr>
    <h3>Skills</h3>
    <p>{$skills}</p>
    <hr>
    <h3>Projects</h3>
    <p>{$projects}</p>
    <hr>
    <h3>Certifications</h3>
    <p>{$certifications}</p>
    <hr>
    <h3>Extracurricular Activities</h3>
    <p>{$extracurricular}</p>
    <hr>
    <h3>Achievements</h3>
    <p>{$achievements}</p>
    <hr>
    <p style="text-align:center;"><i>Generated by PortfolinK</i></p>
EOD;

    $pdf->writeHTML($html, true, false, true, false, "");
    $pdf->Output("Student_Portfolio.pdf", "I");
    exit;
}

// If no roll number is provided, display a list of students.
$stmt = $pdo->query("SELECT roll_no, name FROM students ORDER BY roll_no ASC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Generate Portfolio PDF | PortfolinK</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css_files/generatePdf.css">
    <link rel="stylesheet" href="css_files/menu.css">
    <link rel="stylesheet" href="css_files/footer.css">
    <script src="https://unpkg.com/lenis@1.2.3/dist/lenis.min.js"></script>

    <style>

    </style>
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
            <h1>Generate Portfolio PDF</h1>
            <p>Select a student from the list below:</p>
            <table>
                <thead>
                    <tr>
                        <th>Roll No</th>
                        <th>Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $stud): ?>
                            <tr>
                                <td>
                                    <a href="generatePdf.php?roll_no=<?php echo urlencode($stud['roll_no']); ?>">
                                        <?php echo htmlspecialchars($stud['roll_no']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($stud['name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">No students found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>

        <!-- Footer -->
        <div id="footer">
            <h3>PortfolinK &copy; 2025</h3>
            <h4>Copyright © 2025 Vinay Prakash More ®</h4>
        </div>
    </div>
</body>

</html>