<?php
$host = "db";
$username = "db";
$password = "db";
$dbname = "db";

try {
    // Connect to the MySQL server
    $connection = new PDO("mysql:host=$host", $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create the database if it doesn't exist
    $connection->exec("CREATE DATABASE IF NOT EXISTS $dbname");

    // Use the database
    $connection->exec("USE $dbname");

    // Create the table
    $sql = "CREATE TABLE IF NOT EXISTS email_requests (
        id INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(255) NOT NULL,
        token VARCHAR(64) NOT NULL,
        confirmed BOOLEAN DEFAULT FALSE
    )";

    $connection->exec($sql);

    echo "Database and table created successfully.";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

