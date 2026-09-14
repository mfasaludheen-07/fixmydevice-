<?php

class Database
{
    private $host = "localhost";
    private $dbname = "fixmydevice";
    private $username = "root";
    private $password = "";
    private static $instance = null;
    private $pdo = null;

    public function __construct()
    {
        try {
            // Connect without dbname first to ensure database exists
            $pdoInit = new PDO(
                "mysql:host={$this->host};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $pdoInit->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdoInit->exec("CREATE DATABASE IF NOT EXISTS `{$this->dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

            // Now connect to the target database
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        } catch (PDOException $e) {
            error_log("FixMyDevice Database Connection Error: " . $e->getMessage());
            die("<div style='font-family:sans-serif;max-width:500px;margin:60px auto;padding:24px;border:1px solid #e2e8f0;border-radius:12px;text-align:center;'>
                <h3 style='color:#e11d48;'>Database Connection Error</h3>
                <p style='color:#64748b;'>Could not connect to the database service. Please verify your MySQL server is running in XAMPP.</p>
            </div>");
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function connect()
    {
        return $this->pdo;
    }
}