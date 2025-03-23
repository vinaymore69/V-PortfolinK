
<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $name    = trim($_POST["name"]);
    $email   = trim($_POST["email"]);
    $tittle  = trim($_POST["tittle"]); // 'tittle' as provided in your form
    $enquiry = trim($_POST["enquiry"]);

    // Validate required fields
    if (empty($name) || empty($email) || empty($enquiry)) {
        echo "Name, email, and enquiry are required.";
        exit;
    }

    // Prepare the SQL query to insert the form data
    $sql = "INSERT INTO queries (name, email, tittle, enquiry) VALUES (:name, :email, :tittle, :enquiry)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            ':name'    => $name,
            ':email'   => $email,
            ':tittle'  => $tittle,
            ':enquiry' => $enquiry,
        ]);
        // Retrieve the unique id generated for this query
        $last_id = $pdo->lastInsertId();
        echo "Your query has been submitted successfully. Your Query ID is: " . $last_id;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    // Not a POST request; redirect back to contact page
    header("Location: contact.php");
    exit;
}
?>
