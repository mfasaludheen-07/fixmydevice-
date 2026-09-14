<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Review.php';

class HomeController extends Controller
{
    public function index()
    {
        $categoryModel = new Category();
        $ticketModel = new Ticket();
        $reviewModel = new Review();

        $categories = $categoryModel->getAll();
        $stats = $ticketModel->getStats();
        $recentReviews = $reviewModel->getRecentReviews(4);

        $this->render('home/index', [
            'pageTitle' => 'FixMyDevice - Hardware Repair & Service Ticket System',
            'categories' => $categories,
            'stats' => $stats,
            'reviews' => $recentReviews
        ]);
    }
}
