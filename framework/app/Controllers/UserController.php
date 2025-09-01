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

    //// VIEWS ////

    // ADMIN - List all users
    public function showUsers(): Response
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
            'user_role'       => $session->get('user_role'),
            'first_name'      => $session->get('first_name'),
            'success_message' => $successMessage,
            'error_message'   => $errorMessage,
            'current_route'   => '/admin/users',
            'session'         => $session->all(),
        ]);
    }

    // ADMIN - Show create user form
    public function showCreateUser(): Response
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
            'user_role'     => $session->get('user_role'),
            'first_name'    => $session->get('first_name'),
            'last_name'     => $session->get('last_name'),
            'current_route' => '/admin/users',
            'session'       => $session->all(),

        ]);
    }

    // Admin - Show edit user form
    public function showEditUser(string $id): Response
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
            'user_role'     => $session->get('user_role') ?? 'admin',
            'first_name'    => $session->get('first_name') ?? 'Admin',
            'error_message' => $errorMessage,
            'current_route' => '/admin/users',
            'session'       => $session->all(),
        ]);
    }

    // Admin - View archived users
    public function showArchivedUsers(): Response
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

        // Get archived users with pagination
        $page    = $this->request->getQuery('page', 1);
        $perPage = 20;

        $archivedUsers = $userModel->getArchivedWithPagination((int) $page, $perPage);
        $totalArchived = $userModel->countArchived();
        $totalPages    = ceil($totalArchived / $perPage);

        // Get messages from URL parameters
        $successMessage = $this->request->getQuery('success');
        $errorMessage   = $this->request->getQuery('error');

        return $this->render('admin/users/archived.html.twig', [
            'users'           => $archivedUsers,
            'currentPage'     => (int) $page,
            'totalPages'      => $totalPages,
            'totalUsers'      => $totalArchived,
            'user_role'       => $session->get('user_role'),
            'first_name'      => $session->get('first_name'),
            'success_message' => $successMessage,
            'error_message'   => $errorMessage,
            'current_route'   => '/admin/users',
            'session'         => $session->all(),
        ]);
    }

    // ACTIONS //

    // ADMIN - Store new user
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
                'user_role'  => $session->get('user_role'),
                'first_name' => $session->get('first_name'),
            ]);
        }

        try {
            // Check if email already exists
            $credModel    = new UserCredential();
            $existingUser = $credModel->findByEmail($email);
            if ($existingUser) {
                return $this->render('admin/users/create.html.twig', [
                    'error'      => 'Email already exists',
                    'user_role'  => $session->get('user_role') ?? 'admin',
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

            return Response::redirect('/admin/users?' . http_build_query(['success' => 'User created successfully']));
        } catch (\Exception $e) {
            return $this->render('admin/users/create.html.twig', [
                'error'      => 'Error creating user: ' . $e->getMessage(),
                'user_role'  => $session->get('user_role'),
                'first_name' => $session->get('first_name'),
            ]);
        }
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

            return Response::redirect('/admin/users?' . http_build_query(['success' => 'User updated successfully']));
        } catch (\Exception $e) {
            return Response::redirect('/admin/users/' . $id . '/edit?error=Error updating user: ' . $e->getMessage());
        }
    }

    // Admin - Archive user (soft delete)
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

        $userToArchive = $userModel->find((int) $id);
        if (! $userToArchive) {
            return Response::redirect('/admin/users?error=User not found');
        }

        try {
            // Archive user (soft delete) - this will make the user inactive
            $userModel->archiveUser((int) $id);

            return Response::redirect('/admin/users?' . http_build_query(['success' => 'User archived successfully']));
        } catch (\Exception $e) {
            return Response::redirect('/admin/users?error=Error archiving user: ' . $e->getMessage());
        }
    }

    // Admin - Restore archived user
    public function restore(string $id): Response
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

        try {
            // Restore user (set status back to active)
            $userModel->restoreUser((int) $id);

            return Response::redirect('/admin/users/archived?' . http_build_query(['success' => 'User restored successfully']));
        } catch (\Exception $e) {
            return Response::redirect('/admin/users/archived?error=Error restoring user: ' . $e->getMessage());
        }
    }
}
