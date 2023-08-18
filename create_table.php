<?php
$host = "localhost";
$username = "root";
$password = "root";
$dbname = "db";

// Create a connection
$connection = new mysqli($host, $username, $password);

// Check the connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Create the database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($connection->query($sql) === TRUE) {
    echo "Database created successfully. ";
} else {
    echo "Error creating database: " . $connection->error;
}

// Select the database
$connection->select_db($dbname);

// Create the table
$sql = "CREATE TABLE IF NOT EXISTS email_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    confirmed BOOLEAN DEFAULT FALSE
)";

if ($connection->query($sql) === TRUE) {
    echo "Table created successfully.";
} else {
    echo "Error creating table: " . $connection->error;
}

$connection->close();
?>
