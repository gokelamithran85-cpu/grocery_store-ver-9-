<?php
$host = 'localhost';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS grocery_store";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select database
$conn->select_db("grocery_store");

// Read SQL file
$sqlFile = file_get_contents('setup_database.sql');
$queries = explode(';', $sqlFile);

foreach ($queries as $query) {
    if (trim($query) != '') {
        if ($conn->query($query) === TRUE) {
            echo "Query executed successfully<br>";
        } else {
            echo "Error: " . $conn->error . "<br>";
        }
    }
}

echo "<h3>Database setup completed!</h3>";
echo '<a href="index.php">Go to Homepage</a>';

$conn->close();
?>