<?php
namespace App\Controllers;

use App\Models\User;
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
            return Response::redirect('/dashboard');
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

        return Response::redirect('/dashboard');
    }

    public function logout(): Response
    {
        $session = new Session();
        $session->destroy();
        return Response::redirect('/login');
    }

    public function showRegister(): Response
    {
        return $this->render('register.html.twig');
    }

    public function register(): Response
    {
        $email                 = $this->request->getPost('email') ?? null;
        $password              = $this->request->getPost('password') ?? null;
        $password_confirmation = $this->request->getPost('password_confirmation') ?? null;
        $first_name            = $this->request->getPost('first_name') ?? null;
        $middle_name           = $this->request->getPost('middle_name') ?? null;
        $last_name             = $this->request->getPost('last_name') ?? null;

        $errors = [];
        if (! $email) {
            $errors[] = 'Email is required';
        }
        if (! $password) {
            $errors[] = 'Password is required';
        }
        if ($password !== $password_confirmation) {
            $errors[] = 'Password and confirmation do not match';
        }

        if (! empty($errors)) {
            return $this->render('register.html.twig', [
                'errors' => $errors,
                'old'    => ['email' => $email],
            ]);
        }

        // Check if email already exists
        $cred    = new UserCredential();
        $userRow = $cred->findByEmail($email);
        if ($userRow) {
            return $this->render('register.html.twig', [
                'errors' => ['Email is already registered'],
                'old'    => ['email' => $email],
            ]);
        }

        // Create user with default role 'student'
        $userModel = new User();
        $userId    = $userModel->create(['role' => 'student']);

        // Create credentials
        $cred->createCredential($userId, $email, $password);

        // Optionally, create an empty profile
        $profileModel = new UserProfile();
        $profileModel->createUserProfile($userId, [
            'first_name'  => $first_name,
            'middle_name' => $middle_name,
            'last_name'   => $last_name,
        ]);

        // Redirect to login with success message (could be improved to show flash messages)
        return $this->render('login.html.twig', [
            'success' => 'Registration successful. Please log in.',
            'old'     => ['email' => $email],
        ]);
    }
}
