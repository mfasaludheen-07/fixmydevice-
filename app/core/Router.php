<?php

require_once __DIR__ . '/Controller.php';

class Router
{
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];

    // Base Controller methods that should never be directly invoked as route endpoints
    private $blockedMethods = [
        'render',
        'json',
        'redirect',
        'requireLogin',
        'requireRole',
        'validateCsrf',
        'sendSecurityHeaders'
    ];

    public function dispatch()
    {
        $url = $this->parseUrl();

        // 1. Resolve Controller
        if (isset($url[0]) && !empty($url[0])) {
            $firstSegment = strtolower(trim($url[0]));

            // Explicit shortcut endpoints
            if (in_array($firstSegment, ['login', 'register', 'logout', 'profile', 'update_profile', 'change_password'])) {
                $this->controller = 'AuthController';
                $this->method = $firstSegment;
                unset($url[0]);
            } elseif ($firstSegment === 'track') {
                $this->controller = 'TrackController';
                $this->method = 'index';
                unset($url[0]);
            } elseif ($firstSegment === 'home') {
                $this->controller = 'HomeController';
                $this->method = 'index';
                unset($url[0]);
            } else {
                $controllerName = ucfirst($firstSegment) . 'Controller';
                $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

                if (file_exists($controllerFile)) {
                    $this->controller = $controllerName;
                    unset($url[0]);
                } else {
                    // Unknown controller segment -> 404
                    $this->renderNotFound();
                    return;
                }
            }
        }

        // 2. Load Controller Class
        $controllerFile = __DIR__ . '/../controllers/' . $this->controller . '.php';
        if (!file_exists($controllerFile)) {
            $this->renderNotFound();
            return;
        }

        require_once $controllerFile;
        if (!class_exists($this->controller)) {
            $this->renderNotFound();
            return;
        }

        $controllerInstance = new $this->controller;

        // 3. Resolve Action Method
        if (isset($url[1]) && !empty($url[1])) {
            $requestedMethod = trim($url[1]);

            if ($this->isActionCallable($controllerInstance, $requestedMethod)) {
                $this->method = $requestedMethod;
                unset($url[1]);
            } else {
                // Method explicitly provided but does not exist or is not a valid public action -> 404
                $this->renderNotFound();
                return;
            }
        } else {
            // No action method provided in URL (e.g. ?url=admin or ?url=ticket)
            if ($this->isActionCallable($controllerInstance, $this->method)) {
                // default method (index) exists
            } elseif ($this->isActionCallable($controllerInstance, 'dashboard')) {
                // Fallback to dashboard for admin/ticket/technician controllers
                $this->method = 'dashboard';
            } elseif ($this->isActionCallable($controllerInstance, 'index')) {
                $this->method = 'index';
            } else {
                $this->renderNotFound();
                return;
            }
        }

        // 4. Resolve Parameters
        $this->params = $url ? array_values($url) : [];

        // 5. Execute Action
        call_user_func_array([$controllerInstance, $this->method], $this->params);
    }

    private function isActionCallable($instance, $method)
    {
        if (in_array($method, $this->blockedMethods) || substr($method, 0, 1) === '_') {
            return false;
        }

        if (!method_exists($instance, $method)) {
            return false;
        }

        try {
            $reflect = new ReflectionMethod($instance, $method);
            return $reflect->isPublic();
        } catch (ReflectionException $e) {
            return false;
        }
    }

    public function renderNotFound()
    {
        if (!headers_sent()) {
            http_response_code(404);
        }

        require_once __DIR__ . '/../controllers/ErrorController.php';
        $errorController = new ErrorController();
        $errorController->notFound();
        exit;
    }

    private function parseUrl()
    {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
