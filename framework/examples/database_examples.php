<?php
// Database Query Examples for your PHP MVC Framework
// Copy these examples into your controllers when needed

use App\Models\ActivityLogs;
use App\Models\User;
use App\Models\UserProfile;

class DatabaseExamples
{
    public function getUserExamples()
    {
        $userModel = new User();

        // ===== METHOD 1: Get specific field by ID =====
        // Example: Get role of user ID 5
        $userRole = $userModel->getUserRole(5);
        echo "User 5 role: " . $userRole; // Output: "student" or "teacher" or "admin"

        // Example: Get any field by ID
        $userEmail = $userModel->getUserField(5, 'created_at');
        echo "User 5 created at: " . $userEmail;

        // ===== METHOD 2: Get multiple fields for one user =====
        // Get specific fields
        $userData = $userModel->getUserData(5, ['id', 'role', 'created_at']);
        /*
        Output:
        [
            'id' => 5,
            'role' => 'student',
            'created_at' => '2025-08-20 10:30:00'
        ]
        */

                                                   // Get all fields
        $allUserData = $userModel->getUserData(5); // Gets all columns

        // ===== METHOD 3: Find user by ID (inherited from BaseModel) =====
        $user = $userModel->find(5);
        if ($user) {
            echo "User role: " . $user['role'];
            echo "User created: " . $user['created_at'];
        }

        // ===== METHOD 4: Get users with conditions =====
        // Find all teachers
        $teachers = $userModel->getUsersWhere(['role' => 'teacher']);

                                                                             // Find users created after a date
        $recentUsers = $userModel->getUsersWhere(['role' => 'student'], 10); // limit to 10

        // ===== METHOD 5: Using existing methods =====
        $allTeachers = $userModel->findByRole('teacher');
        $allUsers    = $userModel->all();
    }

    public function getUserProfileExamples()
    {
        $profileModel = new UserProfile();

        // Get user's profile by user_id
        $profile = $profileModel->findByUserId(5);
        if ($profile) {
            echo "Name: " . $profile['first_name'] . " " . $profile['last_name'];
            echo "Gender: " . $profile['gender'];
            echo "Birthdate: " . $profile['birthdate'];
        }

        // Get full name
        $fullName = $profileModel->getFullName($profile);
        echo "Full name: " . $fullName;
    }

    public function getActivityLogExamples()
    {
        $activityModel = new ActivityLogs();

        // Get recent activities for user 5
        $userActivities = $activityModel->getRecentActivities(5, 10);

        // Get all login activities
        $loginActivities = $activityModel->getActivitiesByAction('login', 20);

        // Get all activities
        $allActivities = $activityModel->getAllActivities(50);
    }

    public function customQueryExamples()
    {
        // ===== CUSTOM QUERIES IN YOUR MODELS =====
        // Add these methods to your models when needed

        // Example: Get user with their profile in one query
        /*
        public function getUserWithProfile(int $userId): ?array 
        {
            $sql = "SELECT u.*, up.first_name, up.last_name, up.gender, up.birthdate 
                    FROM users u 
                    LEFT JOIN user_profiles up ON u.id = up.user_id 
                    WHERE u.id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        }
        */

        // Example: Get user statistics
        /*
        public function getUserStats(int $userId): array 
        {
            $sql = "SELECT 
                        COUNT(CASE WHEN action = 'login' THEN 1 END) as login_count,
                        COUNT(CASE WHEN action = 'logout' THEN 1 END) as logout_count,
                        MAX(created_at) as last_activity
                    FROM activity_logs 
                    WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        }
        */

        // Example: Get users with activity count
        /*
        public function getUsersWithActivityCount(): array 
        {
            $sql = "SELECT u.id, u.role, up.first_name, up.last_name, 
                           COUNT(al.id) as activity_count
                    FROM users u
                    LEFT JOIN user_profiles up ON u.id = up.user_id
                    LEFT JOIN activity_logs al ON u.id = al.user_id
                    GROUP BY u.id
                    ORDER BY activity_count DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        */
    }
}

// ===== USAGE EXAMPLES IN CONTROLLERS =====

// Example Controller Method
/*
public function showUserProfile(int $userId): Response 
{
    $userModel = new User();
    $profileModel = new UserProfile();
    
    // Get user basic info
    $user = $userModel->find($userId);
    if (!$user) {
        return new Response('User not found', 404);
    }
    
    // Get user's profile
    $profile = $profileModel->findByUserId($userId);
    
    // Get user's recent activities
    $activityModel = new ActivityLogs();
    $activities = $activityModel->getRecentActivities($userId, 5);
    
    return $this->render('user_profile.html.twig', [
        'user' => $user,
        'profile' => $profile,
        'activities' => $activities
    ]);
}
*/

// Quick Examples for your specific needs:
/*

// 1. Get role of user ID 5
$userModel = new User();
$role = $userModel->getUserRole(5);

// 2. Get user 5's email from credentials table
$credModel = new UserCredential();
$userCred = $credModel->findByUserId(5);
$email = $userCred['email'];

// 3. Get user 5's first name
$profileModel = new UserProfile();
$profile = $profileModel->findByUserId(5);
$firstName = $profile['first_name'];

// 4. Get all user 5's data at once
$user = $userModel->find(5);
$profile = $profileModel->findByUserId(5);
$activities = $activityModel->getRecentActivities(5);

*/
