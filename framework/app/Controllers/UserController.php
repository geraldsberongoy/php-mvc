<?php
namespace App\Controllers;

use App\Models\UserProfile;
use App\Models\User;
use App\Models\UserCredential;
use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Http\Response;

class UserController extends AbstractController
{
    public function create(): Response
    {
        return $this->render('add_user.html.twig');
    }

    public function store(): Response
    {
        // Minimal handling: trust $_POST for this simple example
        $role       = $_POST['role'] ?? 'student';
        $email      = $_POST['email'] ?? null;
        $password   = $_POST['password'] ?? null;
        $first_name = $_POST['first_name'] ?? null;
        $last_name  = $_POST['last_name'] ?? null;

        if (! $email || ! $password) {
            return new Response('Email and password are required', 400);
        }

        // create user
        $userModel = new User();
        $userId    = $userModel->create(['role' => $role]);

        // credentials
        $credModel = new UserCredential();
        $credModel->createCredential($userId, $email, $password);

        // profile
        $profileModel = new UserProfile();
        $profileModel->createUserProfile($userId, [
            'first_name'  => $first_name,
            'last_name'   => $last_name,
            'middle_name' => $_POST['middle_name'] ?? null,
            'gender'      => $_POST['gender'] ?? null,
            'birthdate'   => $_POST['birthdate'] ?? null,
        ]);

        return new Response('User created successfully', 201);
    }
}
