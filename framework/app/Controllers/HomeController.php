<?php
namespace App\Controllers;

use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Http\Response;

class HomeController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('home.html.twig');
    }

    public function showDashboard(): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        return $this->render('dashboard.html.twig', [
            'user_id' => $_SESSION['user_id'],
            'first_name' => $_SESSION['first_name'] ?? 'Guest'
        ]);
    }
}
