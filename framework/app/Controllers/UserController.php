<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\UserCredential;
use App\Models\UserProfile;
use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Http\Response;
use Gerald\Framework\Http\Session;

class UserController extends AbstractController
{
    // Admin - List all users
    public function index(): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        // Check if user is admin
        $userId    = $session->get('user_id');
        $userModel = new User();
        $userData  = $userModel->find($userId);

        if (($userData['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        // Get all users with pagination
        $page    = $this->request->getQuery('page', 1);
        $perPage = 20;

        $users      = $userModel->getAllWithPagination((int) $page, $perPage);
        $totalUsers = $userModel->count();
        $totalPages = ceil($totalUsers / $perPage);

        // Get messages from URL parameters
        $successMessage = $this->request->getQuery('success');
        $errorMessage   = $this->request->getQuery('error');

        return $this->render('admin/users/index.html.twig', [
            'users'           => $users,
            'currentPage'     => (int) $page,
            'totalPages'      => $totalPages,
            'totalUsers'      => $totalUsers,
            'user_role'       => 'admin',
            'first_name'      => $session->get('first_name') ?? 'Admin',
            'success_message' => $successMessage,
            'error_message'   => $errorMessage,
        ]);
    }

    // Admin - Show create user form
    public function create(): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        // Check if user is admin
        $userId    = $session->get('user_id');
        $userModel = new User();
        $userData  = $userModel->find($userId);

        if (($userData['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        return $this->render('admin/users/create.html.twig', [
            'user_role'  => 'admin',
            'first_name' => $session->get('first_name') ?? 'Admin',
        ]);
    }

    // Admin - Store new user
    public function store(): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        // Check if user is admin
        $userId    = $session->get('user_id');
        $userModel = new User();
        $userData  = $userModel->find($userId);

        if (($userData['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        $role        = $this->request->getPost('role') ?? 'student';
        $email       = $this->request->getPost('email') ?? null;
        $password    = $this->request->getPost('password') ?? null;
        $first_name  = $this->request->getPost('first_name') ?? null;
        $middle_name = $this->request->getPost('middle_name') ?? null;
        $last_name   = $this->request->getPost('last_name') ?? null;
        $gender      = $this->request->getPost('gender') ?? null;
        $birthdate   = $this->request->getPost('birthdate') ?? null;

        if (! $email || ! $password) {
            return $this->render('admin/users/create.html.twig', [
                'error'      => 'Email and password are required',
                'user_role'  => 'admin',
                'first_name' => $session->get('first_name') ?? 'Admin',
            ]);
        }

        try {
            // Check if email already exists
            $credModel    = new UserCredential();
            $existingUser = $credModel->findByEmail($email);
            if ($existingUser) {
                return $this->render('admin/users/create.html.twig', [
                    'error'      => 'Email already exists',
                    'user_role'  => 'admin',
                    'first_name' => $session->get('first_name') ?? 'Admin',
                ]);
            }

            // create user
            $newUserId = $userModel->create(['role' => $role]);

            // credentials
            $credModel->createCredential($newUserId, $email, $password);

            // profile
            $profileModel = new UserProfile();
            $profileModel->createUserProfile($newUserId, [
                'first_name'  => $first_name,
                'last_name'   => $last_name,
                'middle_name' => $middle_name,
                'gender'      => $gender,
                'birthdate'   => $birthdate,
            ]);

            return Response::redirect('/admin/users?success=User created successfully');
        } catch (\Exception $e) {
            return $this->render('admin/users/create.html.twig', [
                'error'      => 'Error creating user: ' . $e->getMessage(),
                'user_role'  => 'admin',
                'first_name' => $session->get('first_name') ?? 'Admin',
            ]);
        }
    }

    // Admin - Show edit user form
    public function edit(string $id): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        // Check if user is admin
        $currentUserId = $session->get('user_id');
        $userModel     = new User();
        $currentUser   = $userModel->find($currentUserId);

        if (($currentUser['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        $userToEdit = $userModel->findWithDetails((int) $id);
        if (! $userToEdit) {
            return Response::redirect('/admin/users?error=User not found');
        }

        // Get error message from URL parameters
        $errorMessage = $this->request->getQuery('error');

        return $this->render('admin/users/edit.html.twig', [
            'user'          => $userToEdit,
            'user_role'     => 'admin',
            'first_name'    => $session->get('first_name') ?? 'Admin',
            'error_message' => $errorMessage,
        ]);
    }

    // Admin - Update user
    public function update(string $id): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        // Check if user is admin
        $currentUserId = $session->get('user_id');
        $userModel     = new User();
        $currentUser   = $userModel->find($currentUserId);

        if (($currentUser['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        $userToEdit = $userModel->find((int) $id);
        if (! $userToEdit || ! is_array($userToEdit)) {
            return Response::redirect('/admin/users?error=User not found');
        }

        $role        = $this->request->getPost('role') ?? $userToEdit['role'];
        $first_name  = $this->request->getPost('first_name') ?? null;
        $middle_name = $this->request->getPost('middle_name') ?? null;
        $last_name   = $this->request->getPost('last_name') ?? null;
        $gender      = $this->request->getPost('gender') ?? null;
        $birthdate   = $this->request->getPost('birthdate') ?? null;

        try {
            // Update user role
            $userModel->updateUser((int) $id, ['role' => $role]);

            // Update profile
            $profileModel = new UserProfile();
            $profileModel->updateUserProfile((int) $id, [
                'first_name'  => $first_name,
                'last_name'   => $last_name,
                'middle_name' => $middle_name,
                'gender'      => $gender,
                'birthdate'   => $birthdate,
            ]);

            return Response::redirect('/admin/users?success=User updated successfully');
        } catch (\Exception $e) {
            return Response::redirect('/admin/users/' . $id . '/edit?error=Error updating user: ' . $e->getMessage());
        }
    }

    // Admin - Delete user
    public function delete(string $id): Response
    {
        $session = new Session();
        if (! $session->has('user_id')) {
            return Response::redirect('/login');
        }

        // Check if user is admin
        $currentUserId = $session->get('user_id');
        $userModel     = new User();
        $currentUser   = $userModel->find($currentUserId);

        if (($currentUser['role'] ?? 'student') !== 'admin') {
            return Response::redirect('/dashboard');
        }

        if ($id == $currentUserId) {
            return Response::redirect('/admin/users?error=Cannot delete your own account');
        }

        $userToDelete = $userModel->find((int) $id);
        if (! $userToDelete) {
            return Response::redirect('/admin/users?error=User not found');
        }

        try {
            // Delete user profile
            $profileModel = new UserProfile();
            $profileModel->deleteUserProfile((int) $id);

            // Delete user credentials
            $credModel = new UserCredential();
            $credModel->deleteUserCredential((int) $id);

            // Delete user
            $userModel->delete((int) $id);

            return Response::redirect('/admin/users?success=User deleted successfully');
        } catch (\Exception $e) {
            return Response::redirect('/admin/users?error=Error deleting user: ' . $e->getMessage());
        }
    }
}
