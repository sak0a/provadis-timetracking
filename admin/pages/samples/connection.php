<?php
$servername = "korra.design";
$username = "provadis";
$password = "alexandros2406";
$dbname = "provadis_project";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>