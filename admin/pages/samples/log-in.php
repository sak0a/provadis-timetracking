<?php
ob_start();
session_start();
require_once 'connection.php'; // Datenbankverbindung
require_once 'crypt.php';



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT user_id, first_name, last_name, email, role, password_hash FROM Users WHERE email = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['angemeldet'] = true;            
            $_SESSION['username'] = $username;
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name']; 
            $_SESSION['role'] = $user['role']; 
            session_regenerate_id(true);

            echo "angemeldet, alles ok";

            setcookie('angemeldet', true, time() + 3600*24*7, '/', '', true, true);
            $encryptedCookie = encryptCookie($username);
            setcookie('secure_user', $encryptedCookie, time() + 3600*24*7, '/', '', true, true);
            
            echo "cookie gesetzt, alles ok";
            header("Location: blank-page.php");

            exit();
        } else {
            $_SESSION['error'] = "Falsches Passwort.";
            echo "nicht geklappt";
            header("Location: blank-page.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Benutzer nicht gefunden.";
        echo "benutzer nicht gef";
        header("Location: blank-page.php");
        exit();
    }
    
    $stmt->close();
    $conn->close();
}
header("Location: blank-page.php");
ob_end_flush();
exit();
?> 
