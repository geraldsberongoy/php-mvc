<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\UserCredential;
use App\Models\UserProfile;
use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Http\Request;
use Gerald\Framework\Http\Response;

class UserController extends AbstractController
{
    public function create(): Response
    {
        return $this->render('add_user.html.twig');
    }

    public function store(Request $request): Response
    {

        $role        = $request->getPost('role') ?? 'student';
        $email       = $request->getPost('email') ?? null;
        $password    = $request->getPost('password') ?? null;
        $first_name  = $request->getPost('first_name') ?? null;
        $middle_name = $request->getPost('middle_name') ?? null;
        $last_name   = $request->getPost('last_name') ?? null;
        $gender      = $request->getPost('gender') ?? null;
        $birthdate   = $request->getPost('birthdate') ?? null;

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
