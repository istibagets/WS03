<?php

namespace Framework;

use PDO;
use PDOException;
use Exception;

class Database
{
    public $conn;

    public function __construct(array $config)
    {
        // 1. Build the DSN safely
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']}";
        
        // Add port only if it is explicitly defined in the config array
        if (isset($config['port'])) {
            $dsn .= ";port={$config['port']}";
        }

        // Add charset (utf8mb4 is the modern standard for emojis/special chars)
        $charset = $config['charset'] ?? 'utf8mb4';
        $dsn .= ";charset={$charset}";

        // 2. Setup robust PDO options
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES   => false // Native prepares = better security & true types
        ];

        try {
            $this->conn = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            // Include the error message so you can actually debug why it failed
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function query($query, $params = [])
    {
        try {
            $sth = $this->conn->prepare($query);

            // 3. execute($params) is vastly superior to the manual foreach loop.
            // It natively handles named params (['id' => 1]), pre-colon params ([':id' => 1]), 
            // AND positional params (['value1', 'value2']).
            $sth->execute($params);

            return $sth;
        } catch (PDOException $e) {
            throw new Exception("Failed to execute query: " . $e->getMessage());
        }
    }
}