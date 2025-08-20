<?php

require_once 'vendor/autoload.php';

try {
    // Load the database config
    $config = require 'config/database.php';
    
    echo "Database Configuration:\n";
    print_r($config);
    
    // Try to create PDO connection
    $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['database']}";
    echo "\nDSN: $dsn\n";
    echo "Username: {$config['username']}\n";
    echo "Password: " . (empty($config['password']) ? '(empty)' : '(set)') . "\n";
    
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "\n✅ Database connection successful!\n";
    
    // Test query
    $stmt = $pdo->query('SELECT VERSION() as version');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "MySQL Version: " . $result['version'] . "\n";
    
} catch (PDOException $e) {
    echo "\n❌ PDO Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "\n❌ General Error: " . $e->getMessage() . "\n";
}
