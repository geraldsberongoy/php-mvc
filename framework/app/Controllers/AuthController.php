<?php
namespace App\Controllers;

use App\Models\UserCredential;
use App\Models\UserProfile;
use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Http\Request;
use Gerald\Framework\Http\Response;

class AuthController extends AbstractController
{
    public function showlogin(): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (! empty($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }

        return $this->render('login.html.twig');
    }

    public function login(Request $request): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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
        $_SESSION['user_id']   = $userRow['user_id'];
        $_SESSION['full_name'] = $wholeName;
        $_SESSION['email']      = $email;
        $_SESSION['last_login'] = time();

        $cred->updateLastLogin($userRow['user_id']);

        header('Location: /dashboard');
        exit;
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header('Location: /login');
        exit;
    }
}
