<?php

require_once __DIR__ . '/Session.php';

abstract class Controller
{
    public function __construct()
    {
        $this->sendSecurityHeaders();
    }

    protected function sendSecurityHeaders()
    {
        if (!headers_sent()) {
            header('X-Frame-Options: SAMEORIGIN');
            header('X-Content-Type-Options: nosniff');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: camera=(), microphone=(), geolocation=(self)');
        }
    }

    public function render($viewName, $data = [], $withLayout = true)
    {
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $cleanDir = rtrim(str_replace('\\', '/', $scriptDir), '/');
        
        // If running in local subfolder like /FixMyDevice where assets reside under public/
        $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
        if ($cleanDir !== '' && !file_exists($docRoot . $cleanDir . '/assets') && file_exists(__DIR__ . '/../../public/assets')) {
            $baseUrl = $cleanDir . '/public';
        } else {
            $baseUrl = $cleanDir;
        }
        if ($baseUrl === '') {
            $baseUrl = '/';
        }
        $data['baseUrl'] = $baseUrl;

        extract($data);

        $viewFile = __DIR__ . '/../views/' . $viewName . '.php';

        if (file_exists($viewFile)) {
            if ($withLayout) {
                require_once __DIR__ . '/../views/layouts/header.php';
                require_once __DIR__ . '/../views/layouts/navbar.php';
                require_once $viewFile;
                require_once __DIR__ . '/../views/layouts/footer.php';
            } else {
                require_once $viewFile;
            }
        } else {
            die("View '{$viewName}' not found.");
        }
    }

    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($path)
    {
        if (strpos($path, 'http') === 0) {
            header("Location: " . $path);
            exit;
        }

        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $cleanDir = rtrim(str_replace('\\', '/', $scriptDir), '/');
        $path = ltrim($path, '/');

        $useQueryRouting = isset($_GET['url']) || (isset($_SERVER['QUERY_STRING']) && strpos($_SERVER['QUERY_STRING'], 'url=') !== false);

        if ($cleanDir !== '' && $cleanDir !== '/') {
            if ($useQueryRouting) {
                $target = $cleanDir . '/index.php?url=' . $path;
            } else {
                $target = $cleanDir . '/' . $path;
            }
        } else {
            if ($useQueryRouting) {
                $target = '/index.php?url=' . $path;
            } else {
                $target = '/' . ($path === 'home' ? '' : $path);
            }
        }

        header("Location: " . $target);
        exit;
    }

    protected function validateCsrf()
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Session::validateCsrfToken($token)) {
            Session::setFlash('error', 'Security verification token invalid or expired. Please try again.');
            $this->redirect('home');
        }
    }

    protected function requireLogin()
    {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Please log in to access this page.');
            $this->redirect('login');
        }
    }

    protected function requireRole($roles)
    {
        $this->requireLogin();
        $userRole = Session::role();

        if (is_string($roles)) {
            $roles = [$roles];
        }

        if (!in_array($userRole, $roles)) {
            Session::setFlash('error', 'Unauthorized access.');
            $this->redirect('home');
        }
    }
}
