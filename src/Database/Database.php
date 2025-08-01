<?php

namespace App\Database;
use PDO;

define("DATABASE_HOST", "localhost");
define("DATABASE_NAME", "new_udi3");
define("DATABASE_PORT", "5432");
define("DATABASE_USER", "postgres");
define("DATABASE_PASSWORD", "admin");

class Database
{
    private PDO $pdo;
    protected string $driver = "pgsql";
    public function __construct(string $host = DATABASE_HOST, string $dbName = DATABASE_NAME, string $port = DATABASE_PORT, string $user = DATABASE_USER, string $password = DATABASE_PASSWORD)
    {

        $dsn = "$this->driver:host=$host;port=$port;dbname=$dbName;";
        $this->pdo = new PDO($dsn, $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function run(string $query, $args = null)
    {
        if (is_null($args)) {
            return $this->pdo->query($query);
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($args);
        return $stmt;
    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function commitTransaction()
    {
        $this->pdo->commit();
    }

    public function rollbackTransaction()
    {
        $this->pdo->rollBack();
    }
}
