<?php

declare(strict_types=1);

namespace App\Infra\Db;

use PDO;

final class PdoFactory
{
    private ?PDO $pdo = null;

    public function get(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        $dsn = getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=fleet';
        $user = getenv('DB_USER') ?: 'postgres';
        $pass = getenv('DB_PASSWORD') ?: 'admin';

        $this->pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        return $this->pdo;
    }
}
