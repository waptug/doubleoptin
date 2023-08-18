<?php
include 'dbconfig.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(16));

    try {
        // Connect to the database using PDO
        $connection = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insert the email and token into the database
        $sql = "INSERT INTO email_requests (email, token) VALUES (?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(1, $email, PDO::PARAM_STR);
        $stmt->bindParam(2, $token, PDO::PARAM_STR);
        $stmt->execute();

        // Create the confirmation link
        $confirmation_link = "https://doubleoptin.ddev.site/confirm.php?email=$email&token=$token";
        $subject = "Confirm Your Request for Zip File";
        $body = "Please confirm your request by clicking this link: $confirmation_link";

        // Send the email
        mail($email, $subject, $body);

        echo "Verification email sent. Please check your email.";
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
}
?>

