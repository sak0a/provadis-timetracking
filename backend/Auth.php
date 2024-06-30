<?php
class Auth
{
    public static function isLoggedIn(): bool {
        return isset($_SESSION['angemeldet']) && $_SESSION['angemeldet'] === true;
    }

    public static function Logout(): void {
        if (!isset($_POST['logout']))
            return;

        unset($_SESSION['angemeldet']);
        unset($_SESSION['email']);
        unset($_SESSION['user']);  // Benutzerdaten aus der Session entfernen
        if (isset($_COOKIE['secure_user'])) {
            setcookie('secure_user', '', time() - 3600*24*7, '/');
            unset($_COOKIE['secure_user']);
        }
        session_regenerate_id(true);
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    public static function Login(): void {
        if (isset($_POST['login']) && !empty($_POST['email'])) {
            $_SESSION['angemeldet'] = true;
            $_SESSION['email'] = $_POST['email'];
            session_regenerate_id(true);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    public static function CheckSession()
    {
        if (isset($_COOKIE['secure_user']) && (!isset($_SESSION['angemeldet']) || $_SESSION['angemeldet'] !== true)) {
            require_once 'crypt.php';
            if (function_exists('decryptCookie')) {
                $userId = decryptCookie($_COOKIE['secure_user']);
                if ($userId) {
                    $_SESSION['angemeldet'] = true;
                    $_SESSION['email'] = $userId;
                    session_regenerate_id(true);
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                }
            }
        }
    }

}