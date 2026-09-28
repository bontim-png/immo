<?php
declare(strict_types=1);

final class PropertyController
{
    public function __construct(private PDO $db, private I18n $i18n) {}

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $property = (new Property($this->db))->find($id);
        if (!$property) { http_response_code(404); View::render('404'); return; }
        $photos = (new Property($this->db))->photos($id);
        View::render('property', compact('property', 'photos'));
    }
}
