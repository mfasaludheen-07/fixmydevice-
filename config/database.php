<?php

class Database
{
    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;
    private static $instance = null;
    private $pdo = null;

    private static function loadEnv()
    {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile) && is_readable($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                    continue;
                }
                list($key, $val) = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val, " \t\n\r\0\x0B\"'");
                if (getenv($key) === false && !isset($_ENV[$key])) {
                    putenv("{$key}={$val}");
                    $_ENV[$key] = $val;
                }
            }
        }
    }

    private static function getEnvVar($key, $default = '')
    {
        $val = getenv($key);
        if ($val !== false && $val !== '') {
            return $val;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        return $default;
    }

    public function __construct()
    {
        self::loadEnv();

        // Read database configuration with TiDB Cloud, generic cloud MySQL, and local XAMPP fallbacks
        $this->host = self::getEnvVar('DB_HOST', self::getEnvVar('TIDB_HOST', self::getEnvVar('MYSQLHOST', 'localhost')));
        $this->port = self::getEnvVar('DB_PORT', self::getEnvVar('TIDB_PORT', self::getEnvVar('MYSQLPORT', '3306')));
        $this->dbname = self::getEnvVar('DB_NAME', self::getEnvVar('TIDB_DATABASE', self::getEnvVar('MYSQLDATABASE', 'fixmydevice')));
        $this->username = self::getEnvVar('DB_USER', self::getEnvVar('TIDB_USER', self::getEnvVar('MYSQLUSER', 'root')));
        $this->password = self::getEnvVar('DB_PASSWORD', self::getEnvVar('TIDB_PASSWORD', self::getEnvVar('MYSQLPASSWORD', '')));

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            // SSL / TLS encryption handling for managed cloud providers (e.g. TiDB Cloud)
            $isTiDB = (strpos($this->host, 'tidbcloud.com') !== false) || ($this->port == '4000') || (self::getEnvVar('DB_SSL') === 'true');
            if ($isTiDB) {
                $caCert = self::getEnvVar('DB_SSL_CA', self::getEnvVar('TIDB_SSL_CA', ''));
                if ($caCert && file_exists($caCert)) {
                    $options[PDO::MYSQL_ATTR_SSL_CA] = $caCert;
                } elseif (file_exists('/etc/ssl/certs/ca-certificates.crt')) {
                    $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
                } elseif (file_exists('/etc/pki/tls/certs/ca-bundle.crt')) {
                    $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/pki/tls/certs/ca-bundle.crt';
                }
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
            }

            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);

        } catch (PDOException $e) {
            error_log("FixMyDevice Database Connection Error: " . $e->getMessage());

            if (!headers_sent()) {
                http_response_code(500);
            }

            die("<div style='font-family:sans-serif;max-width:520px;margin:60px auto;padding:28px;border:1px solid #e2e8f0;border-radius:12px;text-align:center;box-shadow:0 4px 6px -1px rgba(0,0,0,0.1);'>
                <h3 style='color:#e11d48;margin-bottom:12px;'>Database Connection Error</h3>
                <p style='color:#64748b;line-height:1.6;'>Could not connect to the database service. Please verify your database server is running and configuration environment variables are set.</p>
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