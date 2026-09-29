<?php
namespace App\Models;

use App\Database\Database;
use PDO;

abstract class Model
{
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $guarded = [];
    protected $casts = [];
    protected $dates = ['created_at', 'updated_at'];

    public function __construct()
    {
        if (empty($this->table)) {
            $this->table = $this->getTableName();
        }
    }

    protected function getTableName(): string
    {
        $class = get_class($this);
        $parts = explode('\\', $class);
        $name = end($parts);
        return strtolower(preg_replace('/([A-Z])/', '_$1', $name)) . 's';
    }

    public function all(): array
    {
        $stmt = Database::get()->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = Database::get()->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findOrFail($id)
    {
        $result = $this->find($id);
        if (!$result) {
            throw new \Exception("Record not found");
        }
        return $result;
    }

    public function where(string $column, $operator, $value = null): array
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $stmt = Database::get()->prepare("SELECT * FROM {$this->table} WHERE {$column} {$operator} ?");
        $stmt->execute([$value]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function firstWhere(string $column, $operator, $value = null)
    {
        $results = $this->where($column, $operator, $value);
        return $results[0] ?? null;
    }

    public function create(array $data): int
    {
        $this->validateFillable($data);
        
        $columns = array_keys($data);
        $placeholders = array_map(fn($c) => "?", $columns);
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = Database::get()->prepare($sql);
        $stmt->execute(array_values($data));
        
        return Database::get()->lastInsertId();
    }

    public function update($id, array $data): int
    {
        $this->validateFillable($data);
        
        $sets = [];
        foreach ($data as $key => $value) {
            $sets[] = "{$key} = ?";
        }

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            $this->table,
            implode(', ', $sets),
            $this->primaryKey
        );

        $values = array_values($data);
        $values[] = $id;

        $stmt = Database::get()->prepare($sql);
        $stmt->execute($values);
        
        return $stmt->rowCount();
    }

    public function delete($id): int
    {
        $stmt = Database::get()->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }

    public function count(): int
    {
        $stmt = Database::get()->query("SELECT COUNT(*) as count FROM {$this->table}");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($result['count'] ?? 0);
    }

    public function query(string $sql, array $params = []): array
    {
        $stmt = Database::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function first(string $sql, array $params = [])
    {
        $results = $this->query($sql, $params);
        return $results[0] ?? null;
    }

    protected function validateFillable(array &$data): void
    {
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }

        if (!empty($this->guarded)) {
            foreach ($this->guarded as $key) {
                unset($data[$key]);
            }
        }
    }

    public function castValue(string $key, $value)
    {
        if (isset($this->casts[$key])) {
            $cast = $this->casts[$key];
            
            switch ($cast) {
                case 'int':
                case 'integer':
                    return (int)$value;
                case 'float':
                    return (float)$value;
                case 'bool':
                case 'boolean':
                    return (bool)$value;
                case 'json':
                    return json_decode($value, true);
                case 'date':
                case 'datetime':
                    return $value; // Already handled by PDO
            }
        }
        
        return $value;
    }

    public function formatDate($value): string
    {
        if ($value === null) {
            return '';
        }
        
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }
        
        return (string)$value;
    }
}
