<?php

namespace App\Repositories;

use App\Database\Database;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail(string $email): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_OBJ);
        return $user ?: null;
    }
}