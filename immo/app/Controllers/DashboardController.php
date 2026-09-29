<?php
declare(strict_types=1);

final class DashboardController
{
    public function __construct(private PDO $db, private I18n $i18n) {}

    public function index(): void
    {
        $counts = [
            'properties' => (int) $this->db->query('SELECT COUNT(*) FROM properties')->fetchColumn(),
            'published' => (int) $this->db->query("SELECT COUNT(*) FROM properties WHERE listing_status='published'")->fetchColumn(),
            'offices' => (int) $this->db->query('SELECT COUNT(*) FROM offices WHERE status="active"')->fetchColumn(),
            'agents' => (int) $this->db->query('SELECT COUNT(*) FROM agents WHERE status="active"')->fetchColumn(),
        ];
        $properties = (new Property($this->db))->latest(8);
        View::render('dashboard', compact('counts', 'properties'));
    }
}
