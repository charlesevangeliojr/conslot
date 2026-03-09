<?php
// Database Configuration
$host = 'localhost';
$username = 'root';
$password = '2004';
$database = 'conslot';

// Create connection first without database
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS $database");
$conn->select_db($database);

// Set charset
$conn->set_charset("utf8mb4");
?>