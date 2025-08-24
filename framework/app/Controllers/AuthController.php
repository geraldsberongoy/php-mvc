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

    public function login(): Response
    {
        $session = new Session();

        $email    = $this->request->getPost('email') ?? null;
        $password = $this->request->getPost('password') ?? null;

        if (! $email || ! $password) {
            return $this->render('login.html.twig', [
                'error' => 'Email and password are required',
                'old'   => ['email' => $email],
            ]);
        }
        $cred    = new UserCredential();
        $userRow = $cred->verify($email, $password);

        // when invalid credentials
        if (! $userRow) {
            return $this->render('login.html.twig', [
                'error' => 'Invalid credentials',
                'old'   => ['email' => $email],
            ]);
        }

        $profile     = new UserProfile();
        $profileData = $profile->findByUserId($userRow['user_id']);
        $wholeName   = $profile->getFullName($profileData);
        // Set session and update the last login
        $session->set('user_id', $userRow['user_id']);
        $session->set('first_name', $profileData['first_name'] ?? 'User');
        $session->set('middle_name', $profileData['middle_name'] ?? '');
        $session->set('last_name', $profileData['last_name'] ?? '');
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
