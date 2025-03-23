<?php
session_start();
require_once 'config.php'; // Contains the PDO connection in $pdo
require 'vendor/autoload.php'; // Composer autoloader for PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

// Prevent caching so the browser always requests a fresh page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
        $excelFilePath = $_FILES['excel_file']['tmp_name'];
        try {
            // Load the Excel file
            $spreadsheet = IOFactory::load($excelFilePath);

            // For debugging: Print available sheet names
            $sheetNames = $spreadsheet->getSheetNames();
            $message .= "Found sheets: " . implode(", ", $sheetNames) . "<br>";

            /* --------------------------------------
             * Process the Students sheet
             * -------------------------------------- */
            $studentsSheet = $spreadsheet->getSheetByName('Students');
            $studentsInserted = 0;
            if ($studentsSheet) {
                $highestRow = $studentsSheet->getHighestRow();
                $message .= "Students sheet highest row: $highestRow<br>";
                for ($row = 2; $row <= $highestRow; $row++) {
                    $roll_no       = trim($studentsSheet->getCell("A$row")->getValue());
                    $name          = trim($studentsSheet->getCell("B$row")->getValue());
                    $email         = trim($studentsSheet->getCell("C$row")->getValue());
                    $phone         = trim($studentsSheet->getCell("D$row")->getValue());
                    $college       = trim($studentsSheet->getCell("E$row")->getValue());
                    $course        = trim($studentsSheet->getCell("F$row")->getValue());
                    $class         = trim($studentsSheet->getCell("G$row")->getValue());
                    $year_of_study = trim($studentsSheet->getCell("H$row")->getValue());
                    $cgpa          = trim($studentsSheet->getCell("I$row")->getValue());
                    $bio           = trim($studentsSheet->getCell("J$row")->getValue());
                    $linkedin      = trim($studentsSheet->getCell("K$row")->getValue());
                    $github        = trim($studentsSheet->getCell("L$row")->getValue());
                    $portfolio_link= trim($studentsSheet->getCell("M$row")->getValue());
                    $resume        = trim($studentsSheet->getCell("N$row")->getValue());

                    // Skip row if roll_no is empty
                    if (empty($roll_no)) {
                        continue;
                    }

                    // Default profile picture
                    $profile_pic = "default.png";
                    if (isset($_FILES['profile_images'])) {
                        foreach ($_FILES['profile_images']['name'] as $key => $filename) {
                            $fileExt  = pathinfo($filename, PATHINFO_EXTENSION);
                            $baseName = pathinfo($filename, PATHINFO_FILENAME);
                            if ($baseName === $roll_no) {
                                $targetDir = __DIR__ . '/uploads/profile_images/';
                                if (!is_dir($targetDir)) {
                                    if (!mkdir($targetDir, 0777, true)) {
                                        die('Failed to create directories: ' . $targetDir);
                                    }
                                }
                                // Rename file to [roll_no]_image.extension
                                $newFileName    = $roll_no . "_image." . $fileExt;
                                $targetFilePath = $targetDir . $newFileName;
                                if (move_uploaded_file($_FILES['profile_images']['tmp_name'][$key], $targetFilePath)) {
                                    $profile_pic = $newFileName;
                                }
                                break;
                            }
                        }
                    }

                    $sql = "INSERT INTO students 
                            (roll_no, name, email, phone, college, course, class, year_of_study, cgpa, profile_pic, bio, linkedin, github, portfolio_link, resume)
                            VALUES (:roll_no, :name, :email, :phone, :college, :course, :class, :year_of_study, :cgpa, :profile_pic, :bio, :linkedin, :github, :portfolio_link, :resume)
                            ON DUPLICATE KEY UPDATE 
                                name = VALUES(name),
                                email = VALUES(email),
                                phone = VALUES(phone),
                                college = VALUES(college),
                                course = VALUES(course),
                                class = VALUES(class),
                                year_of_study = VALUES(year_of_study),
                                cgpa = VALUES(cgpa),
                                profile_pic = VALUES(profile_pic),
                                bio = VALUES(bio),
                                linkedin = VALUES(linkedin),
                                github = VALUES(github),
                                portfolio_link = VALUES(portfolio_link),
                                resume = VALUES(resume)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':roll_no'       => $roll_no,
                        ':name'          => $name,
                        ':email'         => $email,
                        ':phone'         => $phone,
                        ':college'       => $college,
                        ':course'        => $course,
                        ':class'         => $class,
                        ':year_of_study' => $year_of_study,
                        ':cgpa'          => $cgpa,
                        ':profile_pic'   => $profile_pic,
                        ':bio'           => $bio,
                        ':linkedin'      => $linkedin,
                        ':github'        => $github,
                        ':portfolio_link'=> $portfolio_link,
                        ':resume'        => $resume
                    ]);
                    $studentsInserted++;
                }
            } else {
                $message .= "Students sheet not found.<br>";
            }

            /* --------------------------------------
             * Process the Projects sheet (lookup for student_id)
             * -------------------------------------- */
            $projectsSheet = $spreadsheet->getSheetByName('Projects');
            $projectsInserted = 0;
            if ($projectsSheet) {
                $highestRow = $projectsSheet->getHighestRow();
                $message .= "Projects sheet highest row: $highestRow<br>";
                for ($row = 2; $row <= $highestRow; $row++) {
                    $studentRoll = trim($projectsSheet->getCell("A$row")->getValue());
                    // Lookup student id using roll number
                    $studentQuery = $pdo->prepare("SELECT id FROM students WHERE roll_no = ?");
                    $studentQuery->execute([$studentRoll]);
                    $studentRow = $studentQuery->fetch(PDO::FETCH_ASSOC);
                    if ($studentRow) {
                        $student_id = $studentRow['id'];
                    } else {
                        $message .= "No student found for roll number $studentRoll on Projects sheet row $row.<br>";
                        continue;
                    }
                    
                    $project_name  = trim($projectsSheet->getCell("B$row")->getValue());
                    $description   = trim($projectsSheet->getCell("C$row")->getValue());
                    $technologies  = trim($projectsSheet->getCell("D$row")->getValue());
                    $github_link   = trim($projectsSheet->getCell("E$row")->getValue());
                    $live_demo     = trim($projectsSheet->getCell("F$row")->getValue());
                    if (empty($live_demo)) {
                        $live_demo = 'No Live Demo Available';
                    }
                    $project_image = trim($projectsSheet->getCell("G$row")->getValue());
                    
                    if (empty($project_name)) {
                        continue;
                    }
                    
                    $sql = "INSERT INTO projects (student_id, project_name, description, technologies, github_link, live_demo, project_image)
                            VALUES (:student_id, :project_name, :description, :technologies, :github_link, :live_demo, :project_image)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':student_id'    => $student_id,
                        ':project_name'  => $project_name,
                        ':description'   => $description,
                        ':technologies'  => $technologies,
                        ':github_link'   => $github_link,
                        ':live_demo'     => $live_demo,
                        ':project_image' => $project_image
                    ]);
                    $projectsInserted++;
                }
            } else {
                $message .= "Projects sheet not found.<br>";
            }

            /* --------------------------------------
             * Process the Skills sheet (lookup for student_id)
             * -------------------------------------- */
            $skillsSheet = $spreadsheet->getSheetByName('Skills');
            $skillsInserted = 0;
            if ($skillsSheet) {
                $highestRow = $skillsSheet->getHighestRow();
                $message .= "Skills sheet highest row: $highestRow<br>";
                for ($row = 2; $row <= $highestRow; $row++) {
                    $studentRoll = trim($skillsSheet->getCell("A$row")->getValue());
                    // Lookup student id using roll number
                    $studentQuery = $pdo->prepare("SELECT id FROM students WHERE roll_no = ?");
                    $studentQuery->execute([$studentRoll]);
                    $studentRow = $studentQuery->fetch(PDO::FETCH_ASSOC);
                    if ($studentRow) {
                        $student_id = $studentRow['id'];
                    } else {
                        $message .= "No student found for roll number $studentRoll on Skills sheet row $row.<br>";
                        continue;
                    }

                    $skill_name  = trim($skillsSheet->getCell("B$row")->getValue());
                    $skill_level = trim($skillsSheet->getCell("C$row")->getValue());

                    if (empty($skill_name)) {
                        continue;
                    }

                    // Use ON DUPLICATE KEY UPDATE to prevent duplicates.
                    $sql = "INSERT INTO skills (student_id, skill_name, skill_level)
                            VALUES (:student_id, :skill_name, :skill_level)
                            ON DUPLICATE KEY UPDATE skill_level = VALUES(skill_level)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':student_id'  => $student_id,
                        ':skill_name'  => $skill_name,
                        ':skill_level' => $skill_level
                    ]);
                    $skillsInserted++;
                }
            } else {
                $message .= "Skills sheet not found.<br>";
            }

            /* --------------------------------------
             * Process the Certifications sheet (lookup for student_id)
             * -------------------------------------- */
            $certSheet = $spreadsheet->getSheetByName('Certifications');
            $certInserted = 0;
            if ($certSheet) {
                $highestRow = $certSheet->getHighestRow();
                $message .= "Certifications sheet highest row: $highestRow<br>";
                for ($row = 2; $row <= $highestRow; $row++) {
                    $studentRoll = trim($certSheet->getCell("A$row")->getValue());
                    // Lookup student id using roll number
                    $studentQuery = $pdo->prepare("SELECT id FROM students WHERE roll_no = ?");
                    $studentQuery->execute([$studentRoll]);
                    $studentRow = $studentQuery->fetch(PDO::FETCH_ASSOC);
                    if ($studentRow) {
                        $student_id = $studentRow['id'];
                    } else {
                        $message .= "No student found for roll number $studentRoll on Certifications sheet row $row.<br>";
                        continue;
                    }

                    $certificate_name     = trim($certSheet->getCell("B$row")->getValue());
                    $issuing_organization = trim($certSheet->getCell("C$row")->getValue());
                    $issue_date           = trim($certSheet->getCell("D$row")->getValue());
                    $certificate_link     = trim($certSheet->getCell("E$row")->getValue());

                    if (empty($certificate_name)) {
                        continue;
                    }

                    // Use ON DUPLICATE KEY UPDATE to avoid duplicates (if unique key is set on student_id, certificate_name)
                    $sql = "INSERT INTO certifications (student_id, certificate_name, issuing_organization, issue_date, certificate_link)
                            VALUES (:student_id, :certificate_name, :issuing_organization, :issue_date, :certificate_link)
                            ON DUPLICATE KEY UPDATE issuing_organization = VALUES(issuing_organization), issue_date = VALUES(issue_date), certificate_link = VALUES(certificate_link)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':student_id'           => $student_id,
                        ':certificate_name'     => $certificate_name,
                        ':issuing_organization' => $issuing_organization,
                        ':issue_date'           => $issue_date,
                        ':certificate_link'     => $certificate_link
                    ]);
                    $certInserted++;
                }
            } else {
                $message .= "Certifications sheet not found.<br>";
            }

            /* --------------------------------------
             * Process the Extracurricular sheet (lookup for student_id)
             * -------------------------------------- */
            $extraSheet = $spreadsheet->getSheetByName('Extracurricular');
            $extraInserted = 0;
            if ($extraSheet) {
                $highestRow = $extraSheet->getHighestRow();
                $message .= "Extracurricular sheet highest row: $highestRow<br>";
                for ($row = 2; $row <= $highestRow; $row++) {
                    $studentRoll = trim($extraSheet->getCell("A$row")->getValue());
                    // Lookup student id using roll number
                    $studentQuery = $pdo->prepare("SELECT id FROM students WHERE roll_no = ?");
                    $studentQuery->execute([$studentRoll]);
                    $studentRow = $studentQuery->fetch(PDO::FETCH_ASSOC);
                    if ($studentRow) {
                        $student_id = $studentRow['id'];
                    } else {
                        $message .= "No student found for roll number $studentRoll on Extracurricular sheet row $row.<br>";
                        continue;
                    }

                    $activity_name = trim($extraSheet->getCell("B$row")->getValue());
                    $description   = trim($extraSheet->getCell("C$row")->getValue());

                    if (empty($activity_name)) {
                        continue;
                    }

                    $sql = "INSERT INTO extracurricular (student_id, activity_name, description)
                            VALUES (:student_id, :activity_name, :description)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':student_id'    => $student_id,
                        ':activity_name' => $activity_name,
                        ':description'   => $description
                    ]);
                    $extraInserted++;
                }
            } else {
                $message .= "Extracurricular sheet not found.<br>";
            }

            /* --------------------------------------
             * Process the Achievements sheet (lookup for student_id)
             * -------------------------------------- */
            $achieveSheet = $spreadsheet->getSheetByName('Achievements');
            $achieveInserted = 0;
            if ($achieveSheet) {
                $highestRow = $achieveSheet->getHighestRow();
                $message .= "Achievements sheet highest row: $highestRow<br>";
                for ($row = 2; $row <= $highestRow; $row++) {
                    $studentRoll = trim($achieveSheet->getCell("A$row")->getValue());
                    // Lookup student id using roll number
                    $studentQuery = $pdo->prepare("SELECT id FROM students WHERE roll_no = ?");
                    $studentQuery->execute([$studentRoll]);
                    $studentRow = $studentQuery->fetch(PDO::FETCH_ASSOC);
                    if ($studentRow) {
                        $student_id = $studentRow['id'];
                    } else {
                        $message .= "No student found for roll number $studentRoll on Achievements sheet row $row.<br>";
                        continue;
                    }

                    $achievement_name = trim($achieveSheet->getCell("B$row")->getValue());
                    $description      = trim($achieveSheet->getCell("C$row")->getValue());
                    $award_date       = trim($achieveSheet->getCell("D$row")->getValue());

                    if (empty($achievement_name)) {
                        continue;
                    }

                    // Use ON DUPLICATE KEY UPDATE to avoid duplicates (if unique key is set on student_id, achievement_name)
                    $sql = "INSERT INTO achievements (student_id, achievement_name, description, award_date)
                            VALUES (:student_id, :achievement_name, :description, :award_date)
                            ON DUPLICATE KEY UPDATE description = VALUES(description), award_date = VALUES(award_date)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':student_id'       => $student_id,
                        ':achievement_name' => $achievement_name,
                        ':description'      => $description,
                        ':award_date'       => $award_date
                    ]);
                    $achieveInserted++;
                }
            } else {
                $message .= "Achievements sheet not found.<br>";
            }
            
            // Build final debug message
            $message .= "<br>Final Insert Counts:<br>";
            $message .= "Students inserted: " . $studentsInserted . "<br>";
            $message .= "Projects inserted: " . $projectsInserted . "<br>";
            $message .= "Skills inserted: " . $skillsInserted . "<br>";
            $message .= "Certifications inserted: " . $certInserted . "<br>";
            $message .= "Extracurricular inserted: " . $extraInserted . "<br>";
            $message .= "Achievements inserted: " . $achieveInserted . "<br>";
            
        } catch (Exception $e) {
            $message = "Error loading file: " . $e->getMessage();
        }
    } else {
        $message = "Please upload a valid Excel file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feed Data | PortfolinK</title>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <script>
        // Force page reload if loaded from cache (e.g., using the back button)
        window.addEventListener("pageshow", function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="css_files/feedData.css">
    <link rel="stylesheet" href="css_files/menu.css">
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
        
       <div id="page1">
         <!-- Feed Data Form Container -->
         <div id="feed-data">
            <header>
                <h2>Feed Data</h2>
                <a href="logout.php" id="logout">Logout <span class="material-symbols-outlined">logout</span></a>
            </header>
            
            <?php if ($message): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form action="feedData.php" method="post" enctype="multipart/form-data">
                <div>
                    <label for="excel_file">Upload Excel File:</label>
                    <input type="file" name="excel_file" id="excel_file" accept=".xls,.xlsx" required>
                </div>
                <div>
                    <label for="profile_images">Upload Profile Images<br>
                        <small>(Ensure each file's name matches the student's roll number)</small>
                    </label>
                    <input type="file" name="profile_images[]" id="profile_images" accept="image/*" multiple>
                </div>
                <button type="submit">Submit Data &nbsp;<span class="material-symbols-outlined" style="font-size: 1.5vw;">publish</span></button>
            </form>
        </div>
       </div>
    </div>
    <script src="javascript_files/script.js"></script>
</body>
</html>
