<?php
session_start();
require_once 'config.php'; // Contains the PDO connection in $pdo

// Get the roll number and class from the URL query parameters
$roll_no = isset($_GET['roll_no']) ? $_GET['roll_no'] : null;
$classCode = isset($_GET['class']) ? $_GET['class'] : 'Unknown';

if (!$roll_no) {
    echo "No roll number provided.";
    exit;
}

// Delete the student record
$deleteStmt = $pdo->prepare("DELETE FROM students WHERE roll_no = ?");
$deleteStmt->execute([$roll_no]);

// Optionally, you could also delete related data from other tables if foreign keys are not set to cascade

// Redirect back to the class details page with the same class
header("Location: classDetails.php?class=" . urlencode($classCode));
exit;
?>
