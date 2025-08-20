<?php
namespace Gerald\Framework\Database;

use PDO;

class Connection
{
    private static $instance = null;
    public ?PDO $pdo         = null;

    private function __construct(array $config)
    {
        $driver   = $config['driver'];
        $host     = $config['host'];
        $port     = $config['port'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $dsn       = "{$driver}:host={$host};port={$port};dbname={$database}";
        $this->pdo = new PDO($dsn, $username, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function create(): static
    {
        if (null === self::$instance) {
            $configPath = __DIR__ . '/../../config/database.php';
            if (! file_exists($configPath)) {
                throw new \RuntimeException("Database configuration file not found at: {$configPath}");
            }

            try {
                $config         = require $configPath;
                self::$instance = new static($config);
            } catch (\PDOException $e) {
                // surface PDO errors (auth, host, database not found)
                throw new \RuntimeException('PDO error: ' . $e->getMessage());
            } catch (\Throwable $e) {
                throw new \RuntimeException('Database connection error: ' . $e->getMessage());
            }
        }
        return self::$instance;
    }

    public static function getConnection(): static
    {
        return self::$instance;
    }

    
}
