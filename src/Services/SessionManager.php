<?php

namespace grp1\STAGEUP\Services;

class SessionManager
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            //PARAMS SECU
            ini_set('session.cookie_httponly', 1); //Cookies JS
            ini_set('session.use_only_cookies', 1); //Activation cookies de session
            ini_set('session.cookie_samesite', 'Lax'); //Protec contre vuln CSRF

            //Durée d'une session 30 min
            ini_set('session.gc_maxlifetime', 1800);
            ini_set('session.cookie_lifetime', 1800);

            //ID de session régénérée
            if (isset($_SESSION['last_regeneration']) &&
            time() - $_SESSION['last_regeneration'] > 600) { //10 min
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }

            session_start();

            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
            }
        }
    }

    public static function regenerateSessionId(): void {
        self::startSession();
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }

    public static function isUserLoggedIn(): bool
    {
        self::startSession();
        return isset($_SESSION['user_id']);
    }

    public static function loginUser($userId): void
    {
        self::startSession();
        self::regenerateSessionId(); //Régénère l'ID de session à la connexion
        $_SESSION['user_id'] = $userId;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }

    public static function updateLastActivity(): void {
        self::startSession();
        $_SESSION['last_activity'] = time();
    }

    public static function isSessionExpired($maxIdleTime = 1800): bool {
        self::startSession();
        if(!isset($_SESSION['last_activity'])) {
            return true;
        }

        if (time() - $_SESSION['last_activity'] > $maxIdleTime) {
            return true;
        }

        return false;
    }

    public static function validateSession(): bool {
        self::startSession();

        //Session expirée
        if (self::isSessionExpired()) {
            self::logoutUser();
            return false;
        }

        //Vérification IP ou agent utilisateur changé
        if (isset($_SESSION['ip_address'], $_SESSION['user_agent'])) {
            if ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR'] ||
                $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
                self::logoutUser();
                return false;
            }
        }

        self::updateLastActivity();

        return true;
    }

    public static function logoutUser(): void
    {
        self::startSession();
        session_unset();
        session_destroy();
    }

    public static function getCurrentUserId()
    {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }
}