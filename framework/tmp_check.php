<?php
require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use Gerald\Framework\Database\Connection;

try {
    $conn = Connection::create();
    $user = new User();
    echo "OK\n";
} catch (\Throwable $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}
