<?php
$servername = "korra.design";
$username = "provadis";
$password = "alexandros2406";
$dbname = "provadis_project";
// $servername = "mysql34.1blu.de";
// $username = "s319321_3591318";
// $password = "HdJ%uxh0JogiZQk";
// $dbname = "db319321x3591318";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>