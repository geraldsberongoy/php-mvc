<?php

namespace App\Controllers;

use Gerald\Framework\Http\Response;

class BookController{
    public function show(int $id): Response
    {
        $content = '<h1>Book Details</h1>';
        $content .= '<p>Book ID: ' . htmlspecialchars($id) . '</p>';

        return new Response($content);
    }
}