<?php

require_once __DIR__ . '/../core/Controller.php';

class ErrorController extends Controller
{
    public function index()
    {
        $this->notFound();
    }

    public function notFound()
    {
        if (!headers_sent()) {
            http_response_code(404);
        }
        $this->render('errors/404', [
            'pageTitle' => '404 - Page Not Found - FixMyDevice'
        ]);
    }
}
