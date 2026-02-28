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

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM hackathons WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM hackathons WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ?: null;
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO hackathons (nom, description, date_event, prix, latitude, longitude, ville, email_organisateur) 
                VALUES (:nom, :description, :date_event, :prix, :latitude, :longitude, :ville, :email_organisateur)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $sql = "UPDATE hackathons SET nom = :nom, description = :description, date_event = :date_event, 
                prix = :prix, latitude = :latitude, longitude = :longitude, ville = :ville, 
                email_organisateur = :email_organisateur WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function search(string $term): array
    {
        $stmt = $this->db->prepare("SELECT * FROM hackathons WHERE nom ILIKE :term OR ville ILIKE :term ORDER BY date_event ASC");
        $stmt->execute([':term' => '%' . $term . '%']);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getLastInsertId(): int
    {
        return (int)$this->db->lastInsertId();
    }
}