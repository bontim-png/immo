<?php
declare(strict_types=1);

namespace App;

use PDO;
use RuntimeException;

final class OfficeService
{
    public function __construct(private PDO $db) {}

    public function all(): array
    {
        return $this->db->query(
            'SELECT
                o.id,
                o.public_id,
                o.name,
                o.legal_name,
                o.email,
                o.phone,
                o.city,
                o.country_code,
                o.is_active,
                o.created_at,
                COUNT(DISTINCT u.id) AS user_count,
                COUNT(DISTINCT a.id) AS agent_count,
                COUNT(DISTINCT p.id) AS property_count
             FROM offices o
             LEFT JOIN users u
                ON u.office_id = o.id
               AND u.deleted_at IS NULL
             LEFT JOIN agents a
                ON a.office_id = o.id
               AND a.deleted_at IS NULL
             LEFT JOIN properties p
                ON p.office_id = o.id
               AND p.deleted_at IS NULL
             WHERE o.deleted_at IS NULL
             GROUP BY o.id
             ORDER BY o.name ASC'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT *
             FROM offices
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $name = trim((string)($data['name'] ?? ''));

        if ($name === '') {
            throw new RuntimeException('Office name is required.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO offices
             (
                public_id,
                name,
                legal_name,
                email,
                phone,
                website,
                address_line_1,
                address_line_2,
                postcode,
                city,
                country_code,
                default_language_id,
                timezone,
                currency_code
             )
             VALUES
             (
                :public_id,
                :name,
                :legal_name,
                :email,
                :phone,
                :website,
                :address_line_1,
                :address_line_2,
                :postcode,
                :city,
                :country_code,
                :default_language_id,
                :timezone,
                :currency_code
             )'
        );

        $stmt->execute([
            'public_id' => $this->uuid(),
            'name' => $name,
            'legal_name' => $this->nullable($data['legal_name'] ?? null),
            'email' => $this->nullable($data['email'] ?? null),
            'phone' => $this->nullable($data['phone'] ?? null),
            'website' => $this->nullable($data['website'] ?? null),
            'address_line_1' => $this->nullable($data['address_line_1'] ?? null),
            'address_line_2' => $this->nullable($data['address_line_2'] ?? null),
            'postcode' => $this->nullable($data['postcode'] ?? null),
            'city' => $this->nullable($data['city'] ?? null),
            'country_code' => strtoupper(trim((string)($data['country_code'] ?? 'FR'))) ?: 'FR',
            'default_language_id' => $this->languageId($data['default_language'] ?? 'fr'),
            'timezone' => $this->nullable($data['timezone'] ?? 'Europe/Paris') ?? 'Europe/Paris',
            'currency_code' => strtoupper(trim((string)($data['currency_code'] ?? 'EUR'))) ?: 'EUR',
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $name = trim((string)($data['name'] ?? ''));

        if (!$this->find($id)) {
            throw new RuntimeException('Office not found.');
        }

        if ($name === '') {
            throw new RuntimeException('Office name is required.');
        }

        $stmt = $this->db->prepare(
            'UPDATE offices SET
                name = :name,
                legal_name = :legal_name,
                email = :email,
                phone = :phone,
                website = :website,
                address_line_1 = :address_line_1,
                address_line_2 = :address_line_2,
                postcode = :postcode,
                city = :city,
                country_code = :country_code,
                default_language_id = :default_language_id,
                timezone = :timezone,
                currency_code = :currency_code
             WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'legal_name' => $this->nullable($data['legal_name'] ?? null),
            'email' => $this->nullable($data['email'] ?? null),
            'phone' => $this->nullable($data['phone'] ?? null),
            'website' => $this->nullable($data['website'] ?? null),
            'address_line_1' => $this->nullable($data['address_line_1'] ?? null),
            'address_line_2' => $this->nullable($data['address_line_2'] ?? null),
            'postcode' => $this->nullable($data['postcode'] ?? null),
            'city' => $this->nullable($data['city'] ?? null),
            'country_code' => strtoupper(trim((string)($data['country_code'] ?? 'FR'))) ?: 'FR',
            'default_language_id' => $this->languageId($data['default_language'] ?? 'fr'),
            'timezone' => $this->nullable($data['timezone'] ?? 'Europe/Paris') ?? 'Europe/Paris',
            'currency_code' => strtoupper(trim((string)($data['currency_code'] ?? 'EUR'))) ?: 'EUR',
        ]);
    }

    public function setActive(int $id, bool $active): void
    {
        $stmt = $this->db->prepare(
            'UPDATE offices
             SET is_active = :active
             WHERE id = :id
               AND deleted_at IS NULL'
        );
        $stmt->execute([
            'id' => $id,
            'active' => $active ? 1 : 0,
        ]);
    }

    private function languageId(string $code): ?int
    {
        $stmt = $this->db->prepare(
            'SELECT id FROM i18n_languages WHERE code = :code LIMIT 1'
        );
        $stmt->execute(['code' => $code]);
        $id = $stmt->fetchColumn();

        return $id !== false ? (int)$id : null;
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
