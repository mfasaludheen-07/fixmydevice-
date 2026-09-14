<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Ticket.php';

class TrackController extends Controller
{
    public function index()
    {
        $code = trim($_GET['code'] ?? $_POST['code'] ?? '');
        $ticket = null;
        $history = [];
        $searched = false;

        if (!empty($code)) {
            $searched = true;
            $ticketModel = new Ticket();
            $ticket = $ticketModel->findByCode($code);
            if ($ticket) {
                $history = $ticketModel->getHistory($ticket['id']);
            }
        }

        $this->render('track/index', [
            'pageTitle' => 'Track Hardware Complaint Status - FixMyDevice',
            'code' => $code,
            'ticket' => $ticket,
            'history' => $history,
            'searched' => $searched
        ]);
    }
}
