<?php

namespace App\Repositories;

use App\Database\Database;
use PDO;

class HackathonRepository
{
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM hackathons ORDER BY date_event ASC");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}