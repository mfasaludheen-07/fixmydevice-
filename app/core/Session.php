<?php

class Session
{
    public static function init()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            // Fortify session cookie parameters
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                       || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

            ini_set('session.use_only_cookies', '1');
            ini_set('session.use_strict_mode', '1');

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_start();

            // Session timeout check (2 hours of inactivity)
            $now = time();
            if (isset($_SESSION['_last_activity']) && ($now - $_SESSION['_last_activity'] > 7200)) {
                self::destroy();
                session_start();
            }
            $_SESSION['_last_activity'] = $now;
        }
    }

    public static function regenerate()
    {
        self::init();
        if (!headers_sent()) {
            session_regenerate_id(true);
        }
    }

    public static function set($key, $value)
    {
        self::init();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null)
    {
        self::init();
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key)
    {
        self::init();
        return isset($_SESSION[$key]);
    }

    public static function remove($key)
    {
        self::init();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function setFlash($key, $message)
    {
        self::init();
        $_SESSION['_flash'][$key] = $message;
    }

    public static function getFlash($key)
    {
        self::init();
        if (isset($_SESSION['_flash'][$key])) {
            $msg = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $msg;
        }
        return null;
    }

    public static function user()
    {
        self::init();
        return $_SESSION['user'] ?? null;
    }

    public static function isLoggedIn()
    {
        self::init();
        return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }

    public static function role()
    {
        $user = self::user();
        return $user['role'] ?? 'guest';
    }

    /* CSRF Token Generator & Validator */
    public static function generateCsrfToken()
    {
        self::init();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrfToken($token)
    {
        self::init();
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function destroy()
    {
        self::init();
        $_SESSION = array();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_destroy();
            session_start();
        }
    }
}
