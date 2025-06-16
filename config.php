<?php

// config.php - Database configuration settings



// Database credentials

define('DB_SERVER', 'localhost');

define('DB_USERNAME', 'root');           // Change this to your MySQL username

define('DB_PASSWORD', '');               // Change this to your MySQL password

define('DB_NAME', 'vdart_feedback');     // Change this if you use a different database name



// Attempt to connect to MySQL database

$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

 

// Check connection

if($conn === false) {

    die("ERROR: Could not connect to database. " . mysqli_connect_error());

}



// Set charset to ensure proper encoding

mysqli_set_charset($conn, "utf8mb4");

?>