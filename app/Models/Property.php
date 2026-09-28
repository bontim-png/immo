<?php
declare(strict_types=1);

final class Property
{
    public function __construct(private PDO $db) {}

    public function latest(int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        $sql = "SELECT p.*, o.name AS office_name, CONCAT(a.first_name, ' ', a.last_name) AS agent_name,
                       (SELECT pp.file_path FROM property_photos pp WHERE pp.property_id=p.id ORDER BY pp.is_hero DESC, pp.sort_order ASC, pp.id ASC LIMIT 1) AS hero_photo
                FROM properties p
                JOIN offices o ON o.id=p.office_id
                JOIN agents a ON a.id=p.agent_id
                ORDER BY p.updated_at DESC LIMIT {$limit}";
        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT p.*, o.name AS office_name, CONCAT(a.first_name, " ", a.last_name) AS agent_name FROM properties p JOIN offices o ON o.id=p.office_id JOIN agents a ON a.id=p.agent_id WHERE p.id=?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function photos(int $id): array
    {
        $stmt = $this->db->prepare('SELECT * FROM property_photos WHERE property_id=? ORDER BY sort_order ASC, id ASC');
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
}
