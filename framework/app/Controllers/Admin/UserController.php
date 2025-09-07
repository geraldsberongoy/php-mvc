<?php
namespace App\Controllers\Admin;

use App\Models\User;
use App\Models\UserCredential;
use App\Models\UserProfile;
use Gerald\Framework\Http\Response;

class UserController extends BaseAdminController
{
    // ADMIN - List all users
    public function index(): Response
    {
        // Get all users with pagination
        $page    = $this->request->getQuery('page', 1);
        $perPage = 20;

        $userModel  = new User();
        $users      = $userModel->getAllWithPagination((int) $page, $perPage);
        $totalUsers = $userModel->count();
        $totalPages = ceil($totalUsers / $perPage);

        // Get messages from URL parameters
        $successMessage = $this->request->getQuery('success');
        $errorMessage   = $this->request->getQuery('error');

        return $this->renderAdmin('admin/users/index.html.twig', [
            'users'           => $users,
            'currentPage'     => (int) $page,
            'totalPages'      => $totalPages,
            'totalUsers'      => $totalUsers,
            'success_message' => $successMessage,
            'error_message'   => $errorMessage,
            'current_route'   => '/admin/users',
        ]);
    }

    // ADMIN - Show create user form
    public function create(): Response
    {
        return $this->renderAdmin('admin/users/create.html.twig', [
            'current_route' => '/admin/users',
        ]);
    }

    public function edit(string $id): Response
    {
        $userModel  = new User();
        $userToEdit = $userModel->findWithDetails((int) $id);
        if (! $userToEdit) {
            return Response::redirect('/admin/users?error=User not found');
        }

        // Get error message from URL parameters
        $errorMessage = $this->request->getQuery('error');

        return $this->renderAdmin('admin/users/edit.html.twig', [
            'user'          => $userToEdit,
            'error_message' => $errorMessage,
            'current_route' => '/admin/users',
        ]);
    }

    // Admin - View archived users
    public function archived(): Response
    {
        // Get archived users with pagination
        $page    = $this->request->getQuery('page', 1);
        $perPage = 20;

        $userModel     = new User();
        $archivedUsers = $userModel->getArchivedWithPagination((int) $page, $perPage);
        $totalArchived = $userModel->countArchived();
        $totalPages    = ceil($totalArchived / $perPage);

        // Get messages from URL parameters
        $successMessage = $this->request->getQuery('success');
        $errorMessage   = $this->request->getQuery('error');

        return $this->renderAdmin('admin/users/archived.html.twig', [
            'users'           => $archivedUsers,
            'currentPage'     => (int) $page,
            'totalPages'      => $totalPages,
            'totalUsers'      => $totalArchived,
            'success_message' => $successMessage,
            'error_message'   => $errorMessage,
            'current_route'   => '/admin/users',
        ]);
    }

    // ACTIONS //

    // ADMIN - Store new user
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
            return $this->renderAdmin('admin/users/create.html.twig', [
                'error' => 'Email and password are required',
            ]);
        }

        try {
            // Check if email already exists
            $credModel    = new UserCredential();
            $existingUser = $credModel->findByEmail($email);
            if ($existingUser) {
                return $this->renderAdmin('admin/users/create.html.twig', [
                    'error' => 'Email already exists',
                ]);
            }

            // create user
            $userModel = new User();
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
            return $this->renderAdmin('admin/users/create.html.twig', [
                'error' => 'Error creating user: ' . $e->getMessage(),
            ]);
        }
    }

    // Admin - Update user
    public function update(string $id): Response
    {
        $userModel  = new User();
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
        if ($id == $this->userId) {
            return Response::redirect('/admin/users?error=Cannot delete your own account');
        }

        $userModel     = new User();
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
        try {
            // Restore user (set status back to active)
            $userModel = new User();
            $userModel->restoreUser((int) $id);

            return Response::redirect('/admin/users/archived?' . http_build_query(['success' => 'User restored successfully']));
        } catch (\Exception $e) {
            return Response::redirect('/admin/users/archived?error=Error restoring user: ' . $e->getMessage());
        }
    }
}
