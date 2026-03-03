<?php

namespace App\Models;

use App\Database\Database;
use PDO;
use JsonSerializable;

class Hackathon implements JsonSerializable
{
    private ?int $id = null;
    private string $nom = '';
    private string $description = '';
    private string $date_event = '';
    private float $prix = 0.0;
    private float $latitude = 0.0;
    private float $longitude = 0.0;
    private string $ville = '';
    private string $email_organisateur = '';
    private ?string $photo_url = null;

    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    public function __isset(string $name): bool
    {
        return property_exists($this, $name);
    }

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): self { $this->id = $id; return $this; }

    public function getNom(): string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }

    public function getDateEvent(): string { return $this->date_event; }
    public function setDateEvent(string $date_event): self { $this->date_event = $date_event; return $this; }

    public function getPrix(): float { return $this->prix; }
    public function setPrix(float $prix): self { $this->prix = $prix; return $this; }

    public function getLatitude(): float { return $this->latitude; }
    public function setLatitude(float $latitude): self { $this->latitude = $latitude; return $this; }

    public function getLongitude(): float { return $this->longitude; }
    public function setLongitude(float $longitude): self { $this->longitude = $longitude; return $this; }

    public function getVille(): string { return $this->ville; }
    public function setVille(string $ville): self { $this->ville = $ville; return $this; }

    public function getEmailOrganisateur(): string { return $this->email_organisateur; }
    public function setEmailOrganisateur(string $email_organisateur): self { $this->email_organisateur = $email_organisateur; return $this; }

    public function getPhotoUrl(): ?string { return $this->photo_url; }
    public function setPhotoUrl(?string $photo_url): self { $this->photo_url = $photo_url; return $this; }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'description' => $this->description,
            'date_event' => $this->date_event,
            'prix' => $this->prix,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'ville' => $this->ville,
            'email_organisateur' => $this->email_organisateur,
            'photo_url' => $this->photo_url,
        ];
    }

    public static function findAll(): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM hackathons ORDER BY date_event ASC");
        return array_map(fn($row) => self::hydrate($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function findPaginated(int $page, int $limit): array
    {
        $db = Database::getInstance()->getConnection();
        $offset = ($page - 1) * $limit;
        $stmt = $db->prepare("SELECT * FROM hackathons ORDER BY date_event ASC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return array_map(fn($row) => self::hydrate($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function findById(int $id): ?self
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM hackathons WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? self::hydrate($data) : null;
    }

    public function save(): bool
    {
        $db = Database::getInstance()->getConnection();
        $data = $this->jsonSerialize();
        unset($data['id']);

        if ($this->id === null) {
            $sql = "INSERT INTO hackathons (nom, description, date_event, prix, latitude, longitude, ville, email_organisateur, photo_url) 
                    VALUES (:nom, :description, :date_event, :prix, :latitude, :longitude, :ville, :email_organisateur, :photo_url)";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute($data);
            if ($success) {
                $this->id = (int)$db->lastInsertId();
            }
            return $success;
        }

        $data['id'] = $this->id;
        $sql = "UPDATE hackathons SET nom = :nom, description = :description, date_event = :date_event, 
                prix = :prix, latitude = :latitude, longitude = :longitude, ville = :ville, 
                email_organisateur = :email_organisateur, photo_url = :photo_url WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete(): bool
    {
        if ($this->id === null) {
            return false;
        }
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM hackathons WHERE id = :id");
        return $stmt->execute([':id' => $this->id]);
    }

    private static function hydrate(array $data): self
    {
        return (new self())
            ->setId($data['id'] ?? null)
            ->setNom($data['nom'] ?? '')
            ->setDescription($data['description'] ?? '')
            ->setDateEvent($data['date_event'] ?? '')
            ->setPrix((float)($data['prix'] ?? 0))
            ->setLatitude((float)($data['latitude'] ?? 0))
            ->setLongitude((float)($data['longitude'] ?? 0))
            ->setVille($data['ville'] ?? '')
            ->setEmailOrganisateur($data['email_organisateur'] ?? '')
            ->setPhotoUrl($data['photo_url'] ?? null);
    }

    public static function search(string $term): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM hackathons WHERE nom ILIKE :term OR ville ILIKE :term ORDER BY date_event ASC");
        $stmt->execute([':term' => '%' . $term . '%']);

        return array_map(fn($row) => self::hydrate($row), $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }
}