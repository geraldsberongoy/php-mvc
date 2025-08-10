<?php

namespace App\Controllers;

use Gerald\Framework\Http\Response;

class AboutController
{
    public function index(): Response
    {
        $content = '<h1>About Page</h1>';
        $content .= '<p>This is the about page of our application.</p>';

        return new Response($content);
    }
}