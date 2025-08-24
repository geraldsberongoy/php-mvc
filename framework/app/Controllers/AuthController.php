<?php
namespace App\Controllers;

use App\Models\UserCredential;
use App\Models\UserProfile;
use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Http\Request;
use Gerald\Framework\Http\Response;
use Gerald\Framework\Http\Session;

class AuthController extends AbstractController
{
    public function showlogin(): Response
    {
        $session = new Session();
        if (! empty($session->get('user_id'))) {
            header('Location: /dashboard');
            exit;
        }

        return $this->render('login.html.twig');
    }

    public function login(Request $request): Response
    {
        $session = new Session();

        $email    = $request->getPost('email') ?? null;
        $password = $request->getPost('password') ?? null;

        if (! $email || ! $password) {
            return new Response('Email and password are required', 400);
        }
        $cred    = new UserCredential();
        $userRow = $cred->verify($email, $password);

        if (! $userRow) {
            // simple failure response; you can render template with error instead
            return new Response('Invalid credentials', 401);
        }

        $profile     = new UserProfile();
        $profileData = $profile->findByUserId($userRow['user_id']);
        $wholeName   = $profileData['first_name'] . ' ' . $profileData['middle_name'] . ' ' . $profileData['last_name'];

        // Set session and update the last login
        $session->set('user_id', $userRow['user_id']);
        $session->set('full_name', $wholeName);
        $session->set('email', $email);
        $session->set('last_login', time());

        $cred->updateLastLogin($userRow['user_id']);

        header('Location: /dashboard');
        exit;
    }

    public function logout(): void
    {
        $session = new Session();
        $session->destroy();
        header('Location: /login');
        exit;
    }
}
