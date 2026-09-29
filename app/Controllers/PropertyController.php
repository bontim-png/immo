<?php
declare(strict_types=1);

final class PropertyController
{
    public function __construct(private PDO $db, private I18n $i18n) {}
    private function model(): Property { return new Property($this->db); }

    public function index(): void
    {
        View::render('properties', ['properties'=>$this->model()->all(), 'i18n'=>$this->i18n]);
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $property = $id ? $this->model()->find($id) : null;
        if ($id && !$property) { http_response_code(404); View::render('404'); return; }
        $officeId = (int)($property['office_id'] ?? ($_GET['office_id'] ?? 0));
        View::render('property_edit', [
            'property'=>$property,
            'offices'=>$this->model()->offices(),
            'agents'=>$officeId ? $this->model()->agents($officeId) : [],
            'i18n'=>$this->i18n
        ]);
    }

    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $property = $this->model()->find($id);
        if (!$property) { http_response_code(404); View::render('404'); return; }
        View::render('property', ['property'=>$property, 'i18n'=>$this->i18n]);
    }

    public function save(): void
    {
        $data = $_POST;
        if (trim((string)($data['title'] ?? '')) === '' || trim((string)($data['reference_code'] ?? '')) === '') {
            $this->json(['ok'=>false,'message'=>$this->i18n->t('required_fields')], 422); return;
        }
        try { $id=$this->model()->save($data); $this->json(['ok'=>true,'id'=>$id,'redirect'=>'/property/edit?id='.$id]); }
        catch (Throwable $e) { $this->json(['ok'=>false,'message'=>$e->getMessage()],422); }
    }

    public function agents(): void
    {
        $officeId=(int)($_GET['office_id'] ?? 0);
        $this->json(['ok'=>true,'agents'=>$this->model()->agents($officeId)]);
    }

    public function uploadPhotos(): void
    {
        $propertyId=(int)($_POST['property_id'] ?? 0);
        if (!$this->model()->find($propertyId)) { $this->json(['ok'=>false,'message'=>'Property not found.'],404); return; }
        if (!isset($_FILES['photos'])) { $this->json(['ok'=>false,'message'=>'No files received.'],422); return; }
        $root=realpath(__DIR__.'/../../public');
        $dir=$root.'/uploads/properties/'.$propertyId;
        if (!is_dir($dir) && !mkdir($dir,0755,true) && !is_dir($dir)) throw new RuntimeException('Unable to create upload directory.');
        $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        $files=$_FILES['photos']; $created=[];
        foreach ($files['tmp_name'] as $i=>$tmp) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            $info=@getimagesize($tmp); $mime=$info['mime'] ?? '';
            if (!isset($allowed[$mime])) continue;
            if ((int)$files['size'][$i] > 12*1024*1024) continue;
            $name=bin2hex(random_bytes(12)).'.'.$allowed[$mime];
            if (!move_uploaded_file($tmp,$dir.'/'.$name)) continue;
            $relative='/uploads/properties/'.$propertyId.'/'.$name;
            $created[]=$this->model()->addPhoto($propertyId,['name'=>$files['name'][$i],'type'=>$mime,'size'=>$files['size'][$i],'width'=>$info[0]??null,'height'=>$info[1]??null],$relative);
        }
        $this->json(['ok'=>true,'created'=>$created]);
    }

    public function reorder(): void { $this->model()->reorderPhotos((int)$_POST['property_id'], (array)($_POST['ids']??[])); $this->json(['ok'=>true]); }
    public function hero(): void { $this->model()->setHero((int)$_POST['property_id'],(int)$_POST['photo_id']); $this->json(['ok'=>true]); }
    public function deletePhoto(): void
    {
        $propertyId=(int)$_POST['property_id']; $path=$this->model()->deletePhoto($propertyId,(int)$_POST['photo_id']);
        if ($path) { $file=realpath(__DIR__.'/../../public').$path; if ($file && is_file($file)) @unlink($file); }
        $this->json(['ok'=>true]);
    }
    private function json(array $data,int $status=200): void { http_response_code($status); header('Content-Type: application/json; charset=utf-8'); echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); }
}
