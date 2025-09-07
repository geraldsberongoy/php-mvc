<?php
namespace App\Controllers\Student;

use App\Models\User;
use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Database\Connection;
use Gerald\Framework\Http\Request;
use Gerald\Framework\Http\Response;
use Gerald\Framework\Http\Session;

abstract class BaseStudentController extends AbstractController
{
    protected Session $session;
    protected int $userId;
    protected $userData;

    public function __construct(Request $request, Connection $connection)
    {
        parent::__construct($request, $connection);
        $this->session = new Session();
        $this->requireStudentRole();
    }

    /**
     * Ensure the current user is a student
     */
    private function requireStudentRole(): void
    {
        if (! $this->session->has('user_id')) {
            Response::redirect('/login')->send();
            exit;
        }

        $this->userId = $this->session->get('user_id');
        $userModel    = new User();
        $userData     = $userModel->find($this->userId);

        if (! $userData) {
            Response::redirect('/login')->send();
            exit;
        }

        $this->userData = $userData;

        if (($this->userData['role'] ?? 'student') !== 'student') {
            Response::redirect('/dashboard')->send();
            exit;
        }
    }

    /**
     * Get common template variables for student views
     */
    protected function getBaseTemplateVars(): array
    {
        return [
            'user_id'    => $this->userId,
            'user_role'  => $this->session->get('user_role'),
            'first_name' => $this->session->get('first_name'),
            'last_name'  => $this->session->get('last_name'),
            'session'    => $this->session->all(),
        ];
    }

    /**
     * Render student template with base variables
     */
    protected function renderStudent(string $template, array $data = []): Response
    {
        return $this->render($template, array_merge($this->getBaseTemplateVars(), $data));
    }
}
