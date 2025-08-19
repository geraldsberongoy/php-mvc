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
}
