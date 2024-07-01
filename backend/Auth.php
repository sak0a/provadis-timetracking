<?php

require_once 'Crypt.php';

class Auth
{
    public const BASE_COOKIE_NAME = 'secure_user';
    public const SESSION_AUTH_KEY = 'authenticated';
    public const SESSION_EMAIL_KEY = 'email';
    public const SESSION_COOKIE_NAME_KEY = 'unique_cookie_name';

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION[self::SESSION_AUTH_KEY]) && $_SESSION[self::SESSION_AUTH_KEY] === true;
    }

    public static function logout(): void
    {
        if (!isset($_POST['logout'])) {
            return;
        }

        self::clearSession();
        self::clearCookie();
        session_regenerate_id(true);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    public static function login(): void
    {
        if (isset($_POST['login']) && !empty($_POST['email'])) {
            $email = $_POST['email'];
            // Perform user authentication here (e.g., check password, database lookup)

            // Assuming authentication is successful:
            $_SESSION[self::SESSION_AUTH_KEY] = true;
            $_SESSION[self::SESSION_EMAIL_KEY] = $email;

            // Set secure cookie
            self::setSecureCookie($email);

            session_regenerate_id(true);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    public static function checkSession(): void
    {
        if (isset($_SESSION[self::SESSION_COOKIE_NAME_KEY]) && !self::isLoggedIn()) {
            $uniqueCookieName = $_SESSION[self::SESSION_COOKIE_NAME_KEY];
            if (isset($_COOKIE[$uniqueCookieName])) {
                $userId = decryptCookie($_COOKIE[$uniqueCookieName]);
                if ($userId) {
                    $_SESSION[self::SESSION_AUTH_KEY] = true;
                    $_SESSION[self::SESSION_EMAIL_KEY] = $userId;
                    session_regenerate_id(true);
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                }
            }
        }
    }

    public static function setSecureCookie(string $userId): void
    {
        $baseName = self::BASE_COOKIE_NAME;
        $uniqueCookieName = self::generateUniqueCookieName($baseName, $userId);

        // Store the unique cookie name in session
        $_SESSION[self::SESSION_COOKIE_NAME_KEY] = $uniqueCookieName;

        $encryptedValue = encryptCookie($userId);
        // Ensure consistent cookie attributes
        setcookie($uniqueCookieName, $encryptedValue, time() + (3600 * 24 * 7), '/', '', true, true);
    }

    private static function generateUniqueCookieName(string $baseName, string $userIdentifier): string
    {
        $salt = bin2hex(random_bytes(16));
        return hash('sha256', $baseName . $userIdentifier . $salt);
    }

    private static function clearSession(): void
    {
        unset($_SESSION[self::SESSION_AUTH_KEY]);
        unset($_SESSION[self::SESSION_EMAIL_KEY]);
        unset($_SESSION[self::SESSION_COOKIE_NAME_KEY]);
    }

    private static function clearCookie(): void
    {
        if (isset($_SESSION[self::SESSION_COOKIE_NAME_KEY])) {
            $uniqueCookieName = $_SESSION[self::SESSION_COOKIE_NAME_KEY];
            // Debugging statement
            // Ensure consistent cookie attributes
            setcookie($uniqueCookieName, '', time() - 3600, '/', '', true, true);
            unset($_COOKIE[$uniqueCookieName]);
        }
    }
}
?>