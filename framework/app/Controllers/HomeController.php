<?php
namespace App\Controllers;

use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Http\Response;
use Gerald\Framework\Http\Session;

class HomeController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('home.html.twig');
    }

    public function showDashboard(): Response
    {
        $session = new Session();
        if ($session->has('user_id')) {
            return $this->render('dashboard.html.twig', [
                'user_id' => $session->get('user_id'),
                'first_name' => $session->get('first_name') ?? 'Guest'
            ]);
        }

        return Response::redirect('/');
    }
}
