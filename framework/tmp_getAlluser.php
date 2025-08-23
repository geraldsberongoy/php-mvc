<?php
require __DIR__ . '/vendor/autoload.php';

use App\Models\User;

$u     = new User();
$users = $u->all();

if (empty($users)) {
    echo "No users found\n";
    exit;
}

foreach ($users as $user) {
    echo sprintf("%d | %s | %s | %s\n", $user['id'], $user['role'] ?? '', $user['created_at'] ?? '', $user['updated_at'] ?? '');
}

use App\Models\UserProfile;

$profileModel = new UserProfile();
$profiles = $profileModel->all();

if (empty($profiles)) {
    echo "No user profiles found\n";
    exit;
}

foreach ($profiles as $profile) {
    echo sprintf("%d | %s | %s | %s | %s\n", $profile['user_id'], $profile['first_name'] ?? '', $profile['last_name'] ?? '', $profile['gender'] ?? '', $profile['birthdate'] ?? '');
}

use App\Models\UserCredential;

$userCredentialModel = new UserCredential();
$userCredentials = $userCredentialModel->all();

if (empty($userCredentials)){
    echo "No user credentials found\n";
    exit;
}

foreach ($userCredentials as $userCredential){
    echo sprintf("%d | %s | %s | %s\n", $userCredential['user_id'], $userCredential['email'] ?? '', $userCredential['password'] ?? '', $userCredential['created_at'] ?? '');
}
