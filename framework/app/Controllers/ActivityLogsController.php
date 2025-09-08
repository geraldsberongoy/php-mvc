<?php
namespace App\Controllers;

use App\Models\ActivityLogs;
use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Http\Response;
use Gerald\Framework\Http\Session;

class ActivityLogsController extends AbstractController
{
    private ActivityLogs $activityModel;

    public function __construct()
    {
        $this->activityModel = new ActivityLogs();
    }

    // VIEWS //

    /**
     * Display all activity logs (for admin users)
     */
    public function index(): Response
    {
        $session = new Session();
        $userId  = $session->get('user_id');

        if (! $userId) {
            return Response::redirect('/login');
        }

        // Debug: Let's see what's in the session
        $userRole = $session->get('user_role');

        // Get all activities (you might want to add role-based access control here)
        $activities = $this->activityModel->getAllActivities(100);

        return $this->render('admin/activity_logs/index.html.twig', [
            'activities'    => $activities,
            'title'         => 'System Activity Logs',
            'user_role'     => $userRole,
            'first_name'    => $session->get('first_name'),
            'current_route' => '/admin/activity-logs',
            'session'       => $session->all(),
        ]);
    }

    /**
     * Display user's own activity logs
     */
    public function userActivities(): Response
    {
        $session = new Session();
        $userId  = $session->get('user_id');

        if (! $userId) {
            return Response::redirect('/login');
        }

        $activities = $this->activityModel->getRecentActivities($userId, 10);

        return $this->render('admin/activity_logs/user.html.twig', [
            'activities'    => $activities,
            'title'         => 'My Activity History',
            'user_role'     => $session->get('user_role'),
            'first_name'    => $session->get('first_name'),
            'current_route' => '/admin/activity-logs',
            'session'       => $session->all(),

        ]);
    }

    /**
     * Get login activities specifically
     */
    public function loginActivities(): Response
    {
        $session = new Session();
        $userId  = $session->get('user_id');

        if (! $userId) {
            return Response::redirect('/login');
        }

        $loginActivities = $this->activityModel->getActivitiesByAction('login', 30);

        return $this->render('admin/activity_logs/logins.html.twig', [
            'activities'    => $loginActivities,
            'title'         => 'Login Activities',
            'user_role'     => $session->get('user_role'),
            'first_name'    => $session->get('first_name'),
            'current_route' => '/admin/activity-logs',
            'session'       => $session->all(),
        ]);
    }

    // METHODS

    /**
     * Static method to log any activity from anywhere in the application
     */
    public static function logActivity(?int $userId, string $action, string $description, ?string $ipAddress = null): bool
    {
        $activityModel = new ActivityLogs();
        // Provide default IP address if none is provided
        $ipAddress = $ipAddress ?? self::getUserIP();
        return $activityModel->log($userId, $action, $description, $ipAddress);
    }

    /**
     * Helper method to get user's IP address
     */
    public static function getUserIP(): string
    {
        // Check for various headers that might contain the real IP
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
}
