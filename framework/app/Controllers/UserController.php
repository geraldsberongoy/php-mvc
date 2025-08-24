<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\UserCredential;
use App\Models\UserProfile;
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
        $role        = $this->request->getPost('role') ?? 'student';
        $email       = $this->request->getPost('email') ?? null;
        $password    = $this->request->getPost('password') ?? null;
        $first_name  = $this->request->getPost('first_name') ?? null;
        $middle_name = $this->request->getPost('middle_name') ?? null;
        $last_name   = $this->request->getPost('last_name') ?? null;
        $gender      = $this->request->getPost('gender') ?? null;
        $birthdate   = $this->request->getPost('birthdate') ?? null;

        if (! $email || ! $password) {
            return new Response('Email and password are required', 400);
        }

        // create user
        $userModel = new User();
        $userId    = $userModel->create(['role' => $role]);

        // credentials
        $userCredModel = new UserCredential();
        $userCredModel->createCredential($userId, $email, $password);

        // profile
        $profileModel = new UserProfile();
        $profileModel->createUserProfile($userId, [
            'first_name'  => $first_name,
            'last_name'   => $last_name,
            'middle_name' => $middle_name,
            'gender'      => $gender,
            'birthdate'   => $birthdate,
        ]);

        return new Response('User created successfully', 201);
    }
}
