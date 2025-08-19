<?php

namespace App\Controllers;

use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Http\Response;

class BookController extends AbstractController{
    public function show(int $id): Response
    {
        return $this->render('book.html.twig', [
            'id' => $id
        ]);
    }

    public function create(): Response
    {
        // Logic for creating a book can be added here
        return $this->render('create_book.html.twig');
    }

    public function store(): Response
    {
        // Logic for storing a new book can be added here
        return new Response('Book created successfully', 201);
    }
}