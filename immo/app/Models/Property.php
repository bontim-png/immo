<?php
declare(strict_types=1);

final class Property
{
    public function __construct(private PDO $db) {}

    public function all(): array
    {
        $sql = "SELECT p.*, o.name AS office_name, CONCAT(a.first_name, ' ', a.last_name) AS agent_name,
                       (SELECT file_path FROM property_photos pp WHERE pp.property_id=p.id ORDER BY pp.is_hero DESC, pp.sort_order ASC, pp.id ASC LIMIT 1) AS hero_path
                FROM properties p
                JOIN offices o ON o.id=p.office_id
                JOIN agents a ON a.id=p.agent_id
                ORDER BY p.updated_at DESC, p.id DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT p.*, o.name AS office_name, CONCAT(a.first_name, ' ', a.last_name) AS agent_name
                                    FROM properties p JOIN offices o ON o.id=p.office_id JOIN agents a ON a.id=p.agent_id
                                    WHERE p.id=? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $stmt = $this->db->prepare("SELECT * FROM property_photos WHERE property_id=? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$id]);
        $row['photos'] = $stmt->fetchAll();
        return $row;
    }

    public function offices(): array
    {
        return $this->db->query("SELECT id,name FROM offices WHERE status='active' ORDER BY name")->fetchAll();
    }

    public function agents(int $officeId): array
    {
        $stmt = $this->db->prepare("SELECT id,first_name,last_name FROM agents WHERE office_id=? AND status='active' ORDER BY last_name,first_name");
        $stmt->execute([$officeId]);
        return $stmt->fetchAll();
    }

    public function save(array $data): int
    {
        $id = (int)($data['id'] ?? 0);
        $officeId = (int)$data['office_id'];
        $agentId = (int)$data['agent_id'];
        $check = $this->db->prepare("SELECT COUNT(*) FROM agents WHERE id=? AND office_id=? AND status='active'");
        $check->execute([$agentId, $officeId]);
        if ((int)$check->fetchColumn() !== 1) throw new InvalidArgumentException('Agent does not belong to selected office.');

        $slug = trim((string)$data['slug']);
        if ($slug === '') $slug = $this->slugify((string)$data['title']);
        $slug .= $id ? '' : '-' . bin2hex(random_bytes(3));

        $fields = ['office_id','agent_id','reference_code','property_type','listing_status','transaction_type','title','slug','description','price','currency','living_area_m2','land_area_m2','bedrooms','bathrooms','address_line1','postal_code','city','country_code','latitude','longitude'];
        $values = [];
        foreach ($fields as $f) $values[] = $data[$f] ?? null;

        if ($id) {
            $sets = implode(', ', array_map(fn($f) => "$f=?", $fields));
            $stmt = $this->db->prepare("UPDATE properties SET $sets WHERE id=?");
            $stmt->execute([...$values, $id]);
            return $id;
        }
        $columns = implode(',', $fields);
        $marks = implode(',', array_fill(0, count($fields), '?'));
        $stmt = $this->db->prepare("INSERT INTO properties ($columns) VALUES ($marks)");
        $stmt->execute($values);
        return (int)$this->db->lastInsertId();
    }

    public function reorderPhotos(int $propertyId, array $ids): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("UPDATE property_photos SET sort_order=? WHERE id=? AND property_id=?");
            foreach (array_values($ids) as $order => $photoId) $stmt->execute([$order, (int)$photoId, $propertyId]);
            $this->db->commit();
        } catch (Throwable $e) { $this->db->rollBack(); throw $e; }
    }

    public function setHero(int $propertyId, int $photoId): void
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE property_photos SET is_hero=0 WHERE property_id=?")->execute([$propertyId]);
            $stmt = $this->db->prepare("UPDATE property_photos SET is_hero=1 WHERE id=? AND property_id=?");
            $stmt->execute([$photoId, $propertyId]);
            if ($stmt->rowCount() !== 1) throw new InvalidArgumentException('Photo not found.');
            $this->db->commit();
        } catch (Throwable $e) { $this->db->rollBack(); throw $e; }
    }

    public function deletePhoto(int $propertyId, int $photoId): ?string
    {
        $stmt = $this->db->prepare("SELECT file_path FROM property_photos WHERE id=? AND property_id=?");
        $stmt->execute([$photoId, $propertyId]);
        $path = $stmt->fetchColumn();
        if (!$path) return null;
        $this->db->prepare("DELETE FROM property_photos WHERE id=? AND property_id=?")->execute([$photoId, $propertyId]);
        $hero = $this->db->prepare("SELECT COUNT(*) FROM property_photos WHERE property_id=? AND is_hero=1");
        $hero->execute([$propertyId]);
        if ((int)$hero->fetchColumn() === 0) {
            $this->db->prepare("UPDATE property_photos SET is_hero=1 WHERE property_id=? ORDER BY sort_order LIMIT 1")->execute([$propertyId]);
        }
        return $path;
    }

    public function addPhoto(int $propertyId, array $file, string $path): int
    {
        $max = $this->db->prepare("SELECT COALESCE(MAX(sort_order),-1)+1 FROM property_photos WHERE property_id=?");
        $max->execute([$propertyId]);
        $order = (int)$max->fetchColumn();
        $stmt = $this->db->prepare("INSERT INTO property_photos (property_id,file_path,original_name,mime_type,file_size,sort_order,is_hero,width,height) VALUES (?,?,?,?,?,?,?, ?,?)");
        $isHero = $order === 0 ? 1 : 0;
        $stmt->execute([$propertyId,$path,$file['name'],$file['type'],$file['size'],$order,$isHero,$file['width'] ?? null,$file['height'] ?? null]);
        return (int)$this->db->lastInsertId();
    }

    private function slugify(string $value): string
    {
        $value = iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$value) ?: $value;
        $value = strtolower(preg_replace('/[^a-z0-9]+/','-', $value));
        return trim($value, '-') ?: 'property';
    }
}
