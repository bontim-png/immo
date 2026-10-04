<?php
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
$auth->requireLogin();
if (isset($_GET['lang'])) {
    $requestedLang = strtolower(trim((string)$_GET['lang']));
    $langStmt = $db->prepare('SELECT id, code FROM i18n_languages WHERE code=:code AND is_active=1 LIMIT 1');
    $langStmt->execute(['code'=>$requestedLang]);
    if ($langRow=$langStmt->fetch()) {
        $db->prepare('UPDATE users SET preferred_language_id=:l WHERE id=:u')->execute(['l'=>(int)$langRow['id'],'u'=>(int)$auth->id()]);
        $i18n->setLanguage((string)$langRow['code']);
    }
}
function e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function factIcon(string $name): string {
    $map = [
        'living' => '<svg viewBox="0 0 24 24"><path d="M3 20V9l9-6 9 6v11"/><path d="M7 20v-7h10v7"/></svg>',
        'land' => '<svg viewBox="0 0 24 24"><path d="M4 20c5-1 9-4 12-10 1-2 3-4 5-5-1 6-4 12-17 15Z"/><path d="M5 19c3-4 6-7 11-10"/></svg>',
        'bedrooms' => '<svg viewBox="0 0 24 24"><path d="M4 18v-7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v7"/><path d="M4 14h16M7 9V7a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"/><path d="M3 18h18"/></svg>',
        'bathrooms' => '<svg viewBox="0 0 24 24"><path d="M5 11h14v5a4 4 0 0 1-4 4H9a4 4 0 0 1-4-4v-5Z"/><path d="M7 11V6a3 3 0 0 1 5-2l1 1"/><path d="M8 20v2M16 20v2"/></svg>',
        'location' => '<svg viewBox="0 0 24 24"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>',
    ];
    return $map[$name] ?? '';
}
function formatLandHeader(mixed $value): string {
    if ($value === null || $value === '' || !is_numeric($value)) return '—';
    $m2 = (float)$value;
    if ($m2 >= 10000) return number_format($m2 / 10000, 2, ',', ' ') . ' ha';
    return number_format($m2, 0, ',', ' ') . ' m²';
}

function featureIcon(string $code, string $category=''): string {
    $map = [
        'pool'=>'<svg viewBox="0 0 24 24"><path d="M3 16c2.5 0 2.5 2 5 2s2.5-2 5-2 2.5 2 5 2 2.5-2 3-2"/><path d="M5 12c2 0 2-1.5 4-1.5S11 12 13 12s2-1.5 4-1.5S19 12 21 12"/><path d="M8 8V5a2 2 0 0 1 4 0v2"/></svg>',
        'garden'=>'<svg viewBox="0 0 24 24"><path d="M12 20V9"/><path d="M12 13c-4 0-6-2-6-5 4 0 6 2 6 5Z"/><path d="M12 10c0-4 2-6 6-6 0 4-2 6-6 6Z"/></svg>',
        'terrace'=>'<svg viewBox="0 0 24 24"><path d="M4 19h16M6 19V9h12v10M3 9h18M8 5h8"/></svg>',
        'garage'=>'<svg viewBox="0 0 24 24"><path d="M3 20V8l9-5 9 5v12M7 20v-7h10v7M9 16h6"/></svg>',
        'parking'=>'<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M10 17V7h3a3 3 0 0 1 0 6h-3"/></svg>',
        'fireplace'=>'<svg viewBox="0 0 24 24"><path d="M5 20h14M7 20V10h10v10M9 10c0-2 2-3 2-6 3 2 4 4 3 6"/></svg>',
        'air_conditioning'=>'<svg viewBox="0 0 24 24"><path d="M4 9h16M7 9V5M12 9V3M17 9V5M8 14c1.5 2 3 2 4 0s2.5-2 4 0M8 18c1.5 2 3 2 4 0s2.5-2 4 0"/></svg>',
        'gite'=>'<svg viewBox="0 0 24 24"><path d="m3 11 9-8 9 8M5 10v10h14V10M10 20v-6h4v6"/></svg>',
        'annex'=>'<svg viewBox="0 0 24 24"><path d="m4 11 8-7 8 7M6 10v10h12V10M10 20v-5h4v5"/></svg>',
        'renovated'=>'<svg viewBox="0 0 24 24"><path d="m14 6 4 4M5 19l2.5-7.5L16 3l5 5-8.5 8.5L5 19Z"/></svg>',
    ];
    return $map[$code] ?? '<svg viewBox="0 0 24 24"><path d="M12 3v18M3 12h18"/></svg>';
}

function t(string $key, string $fallback): string { return e(__t($key, $fallback)); }
function dbI18nLabel(?string $nameKey, ?string $code, string $fallback): string {
    $nameKey=trim((string)$nameKey);
    $code=trim((string)$code);
    $candidates=[];
    $add=function($key) use (&$candidates){$key=trim((string)$key);if($key!==''&&!in_array($key,$candidates,true))$candidates[]=$key;};
    $add($nameKey);
    if($nameKey!==''){
        $short=preg_replace('/^(property_types|property_type|features|feature)\./','',$nameKey);
        foreach([$nameKey,$short] as $k){
            if($k==='')continue;
            $add('property_types.'.$k);$add('property_type.'.$k);$add('features.'.$k);$add('feature.'.$k);
        }
    }
    if($code!==''){
        $normalized=strtolower(str_replace([' ','-'],'_',$code));
        foreach([$code,$normalized] as $k){
            $add('property_types.'.$k);$add('property_type.'.$k);$add('features.'.$k);$add('feature.'.$k);
        }
    }
    foreach($candidates as $key){
        $marker='__I18N_MISSING__';
        $value=__t($key,$marker);
        if($value!==$marker&&trim((string)$value)!=='')return (string)$value;
    }
    return $fallback;
}
function dbFeatureCategoryLabel(?string $category): string {
    $category=trim((string)$category);
    if($category==='')return (string)__t('features.category.other','Other');
    $marker='__I18N_MISSING__';
    foreach(['features.category.'.$category,'feature_category.'.$category] as $key){
        $value=__t($key,$marker);
        if($value!==$marker&&trim((string)$value)!=='')return (string)$value;
    }
    return ucfirst(str_replace(['_','-'],' ',$category));
}
function uuidv4(): string { $d=random_bytes(16);$d[6]=chr((ord($d[6])&15)|64);$d[8]=chr((ord($d[8])&63)|128);return vsprintf('%s%s-%s-%s-%s-%s%s%s',str_split(bin2hex($d),4)); }
function sanitizeRichHtml(string $html): string {
    $html = trim($html);
    if ($html === '') return '';
    $html = preg_replace('/<!--.*?-->/s', '', $html) ?? '';
    $html = preg_replace('/<\/?(script|style|iframe|object|embed|form|input|button|textarea|select|option)[^>]*>/is', '', $html) ?? $html;
    $html = preg_replace('/\s+on[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
    $html = preg_replace('/\s+(?:style|class|id|data-[a-z0-9_-]+)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
    $html = strip_tags($html, '<p><br><strong><em><ul><ol><li><h2><h3><h4><a>');
    $html = preg_replace_callback('/<\/?(p|br|strong|em|ul|ol|li|h2|h3|h4)\b[^>]*>/i', static fn($m) => $m[0][1] === '/' ? '</'.$m[1].'>' : '<'.$m[1].'>', $html) ?? $html;
    $html = preg_replace_callback('/<a\b([^>]*)>/i', static function($m) {
        if (preg_match('/href\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $m[1], $hm)) {
            $href = trim($hm[1] ?? $hm[2] ?? $hm[3] ?? '');
            $scheme = strtolower((string)parse_url($href, PHP_URL_SCHEME));
            if (in_array($scheme, ['http','https','mailto'], true)) {
                return '<a href="'.e($href).'">';
            }
        }
        return '<a>';
    }, $html) ?? $html;
    return trim($html);
}
function richHtmlForEditor(?string $html): string {
    $safe = sanitizeRichHtml((string)$html);
    if ($safe === '') return '';
    return $safe;
}
function formatCommissionPercent($value, $amount=null, $total=null): string {
    if ($value === null || $value === '') {
        if (is_numeric($amount) && is_numeric($total) && (float)$total > 0) {
            $value = ((float)$amount / (float)$total) * 100;
        } else {
            return '';
        }
    }
    $s = number_format((float)$value, 2, '.', '');
    return rtrim(rtrim($s, '0'), '.');
}
$role=(string)$auth->role();$userId=(int)$auth->id();$officeScope=$auth->officeId();$agentScope=null;
if($role==='agent'){$s=$db->prepare('SELECT id,office_id FROM agents WHERE user_id=:u AND deleted_at IS NULL AND is_active=1 LIMIT 1');$s->execute(['u'=>$userId]);$a=$s->fetch();if(!$a){http_response_code(403);exit('No active agent profile is linked to this user.');}$agentScope=(int)$a['id'];$officeScope=(int)$a['office_id'];}
$id=(int)($_GET['id']??$_POST['id']??0);$property=null;$message='';$error='';
if($id){$where='p.id=:id AND p.deleted_at IS NULL';$params=['id'=>$id];if($role==='office_admin'){$where.=' AND p.office_id=:o';$params['o']=(int)$officeScope;}elseif($role==='agent'){$where.=' AND p.agent_id=:a';$params['a']=(int)$agentScope;}
 $s=$db->prepare("SELECT p.* FROM properties p WHERE $where LIMIT 1");$s->execute($params);$property=$s->fetch();if(!$property){http_response_code(404);exit('Property not found or access denied.');}}
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{
  \App\Csrf::verify($_POST['_csrf']??'');$action=(string)($_POST['action']??'save');
  if(!$property && $action!=='save') throw new RuntimeException('Invalid property.');
  if($action==='save'){
   $officeId=$role==='super_admin'?(int)($_POST['office_id']??0):(int)$officeScope;
   $agentId=$role==='agent'?$agentScope:((int)($_POST['agent_id']??0)?:null);
   if($officeId<1)throw new RuntimeException('Office is required.');
   if($agentId!==null){$s=$db->prepare('SELECT id FROM agents WHERE id=:a AND office_id=:o AND deleted_at IS NULL AND is_active=1 LIMIT 1');$s->execute(['a'=>$agentId,'o'=>$officeId]);if(!$s->fetchColumn())throw new RuntimeException('Selected agent does not belong to the selected office.');}
   $typeId=(int)($_POST['property_type_id']??0);$s=$db->prepare('SELECT id FROM property_types WHERE id=:id AND is_active=1 LIMIT 1');$s->execute(['id'=>$typeId]);if(!$s->fetchColumn())throw new RuntimeException('Please select a valid property type.');
   $tx=in_array($_POST['transaction_type']??'', ['sale','rent'],true)?$_POST['transaction_type']:'sale';$status=in_array($_POST['status']??'', ['draft','active','sold','rented','withdrawn'],true)?$_POST['status']:'draft';
   // References are generated once for new properties from the office code + 5 digits.
   if (!$property) {
      $oc=$db->prepare('SELECT office_code FROM offices WHERE id=:o AND deleted_at IS NULL LIMIT 1');
      $oc->execute(['o'=>$officeId]); $officeCode=strtoupper(trim((string)$oc->fetchColumn()));
      if (!preg_match('/^[A-Z]{3}$/',$officeCode)) throw new RuntimeException(__t('properties.office_code_required','The selected office needs a valid 3-letter office code before a property can be created.'));
      $nr=$db->prepare("SELECT COALESCE(MAX(CAST(SUBSTRING(reference,5,5) AS UNSIGNED)),0)+1 FROM properties WHERE office_id=:o AND reference LIKE :prefix AND deleted_at IS NULL");
      $nr->execute(['o'=>$officeId,'prefix'=>$officeCode.'-_____']);
      $next=(int)$nr->fetchColumn(); if($next<1)$next=1; if($next>99999)throw new RuntimeException(__t('properties.reference_limit','The property reference sequence for this office has reached its limit.'));
      $ref=$officeCode.'-'.str_pad((string)$next,5,'0',STR_PAD_LEFT);
   } else { $ref=(string)$property['reference']; }
   $num=function($key){$v=trim((string)($_POST[$key]??'')); return $v===''?null:(float)$v;};
   $basePrice=$tx==='rent'?$num('rent_price'):$num('price');
   $saleCommissionAmount=$num('sale_commission_amount'); $saleCommissionPercent=$num('sale_commission_percent');
   $rentCommissionAmount=$num('rent_commission_amount'); $rentDeposit=$num('rent_deposit');
   $rentAvailableFrom=trim((string)($_POST['rent_available_from']??''))?:null;
   $soldAmount=$num('sold_amount'); $soldCommissionAmount=$num('sold_commission_amount'); $soldCommissionPercent=$num('sold_commission_percent'); $soldToName=trim((string)($_POST['sold_to_name']??''))?:null; $soldToEmail=trim((string)($_POST['sold_to_email']??''))?:null; $soldToPhone=trim((string)($_POST['sold_to_phone']??''))?:null; $soldDate=trim((string)($_POST['sold_date']??''))?:null;
   $mandateNumber=trim((string)($_POST['mandate_number']??''))?:null; $mandateType=in_array($_POST['mandate_type']??'', ['simple','exclusive','semi_exclusive'],true)?$_POST['mandate_type']:null;
   $mandateStart=trim((string)($_POST['mandate_start_date']??''))?:null; $mandateEnd=trim((string)($_POST['mandate_end_date']??''))?:null;
   $mandatePartyName=trim((string)($_POST['mandate_party_name']??''))?:null; $mandatePartyEmail=trim((string)($_POST['mandate_party_email']??''))?:null; $mandatePartyPhone=trim((string)($_POST['mandate_party_phone']??''))?:null;
   $energyLabel=strtolower(trim((string)($_POST['energy_label']??'')));
   $co2Label=strtolower(trim((string)($_POST['co2_label']??'')));
   $energyLabel=in_array($energyLabel,['a','b','c','d','e','f','g'],true)?$energyLabel:null;
   $co2Label=in_array($co2Label,['a','b','c','d','e','f','g'],true)?$co2Label:null;
   $featureIds=array_values(array_unique(array_filter(array_map('intval',$_POST['features']??[]),fn($v)=>$v>0)));$heatingChoice=(int)($_POST['heating_feature_id']??0);
$underfloorChoice=((int)($_POST['underfloor_heating']??0)===1);$heatingFeatureIds=array_map('intval',$db->query("SELECT id FROM features WHERE code IN ('heating_oil','heating_wood','heating_gas','heating_heat_pump')")->fetchAll(PDO::FETCH_COLUMN));$underfloorFeatureId=(int)($db->query("SELECT id FROM features WHERE code='underfloor_heating' LIMIT 1")->fetchColumn() ?: 0);
$airconFeatureId=(int)($db->query("SELECT id FROM features WHERE code='air_conditioning' LIMIT 1")->fetchColumn() ?: 0);$featureIds=array_values(array_diff($featureIds,$heatingFeatureIds));if($underfloorFeatureId>0){$featureIds=array_values(array_diff($featureIds,[$underfloorFeatureId]));if($underfloorChoice)$featureIds[]=$underfloorFeatureId;}
if($airconFeatureId>0){$featureIds=array_values(array_diff($featureIds,[$airconFeatureId]));if($airconChoice)$featureIds[]=$airconFeatureId;}if($heatingChoice>0 && in_array($heatingChoice,$heatingFeatureIds,true))$featureIds[]=$heatingChoice;$poolFeatureId=(int)$db->query("SELECT id FROM features WHERE code='pool' LIMIT 1")->fetchColumn();$hasPool=($poolFeatureId>0 && in_array($poolFeatureId,$featureIds,true))?1:0;$poolL=$hasPool&&trim((string)($_POST['pool_length_m']??''))!==''?(float)$_POST['pool_length_m']:null;$poolW=$hasPool&&trim((string)($_POST['pool_width_m']??''))!==''?(float)$_POST['pool_width_m']:null;$poolD=$hasPool&&trim((string)($_POST['pool_depth_m']??''))!==''?(float)$_POST['pool_depth_m']:null;if($hasPool===1 && ($poolL===null || $poolW===null || $poolL<=0 || $poolW<=0)){throw new RuntimeException(__t('properties.pool_dimensions_required','Pool length and width are required.'));}if($hasPool===0){$poolL=$poolW=$poolD=null;}
   $fields=['office_id'=>$officeId,'agent_id'=>$agentId,'property_type_id'=>$typeId,'reference'=>$ref,'status'=>$status,'transaction_type'=>$tx,'title'=>trim((string)($_POST['title']??''))?:null,'short_description'=>sanitizeRichHtml((string)($_POST['short_description']??''))?:null,'description'=>sanitizeRichHtml((string)($_POST['description']??''))?:null,'price'=>$basePrice,'living_area_m2'=>trim((string)($_POST['living_area_m2']??''))?:null,'land_area_m2'=>trim((string)($_POST['land_area_m2']??''))?:null,'bedrooms'=>trim((string)($_POST['bedrooms']??''))?:null,'bathrooms'=>trim((string)($_POST['bathrooms']??''))?:null,'year_built'=>trim((string)($_POST['year_built']??''))?:null,'has_pool'=>$hasPool,'pool_length_m'=>$poolL,'pool_width_m'=>$poolW,'pool_depth_m'=>$poolD,'address_line_1'=>trim((string)($_POST['address_line_1']??''))?:null,'address_line_2'=>trim((string)($_POST['address_line_2']??''))?:null,'postcode'=>trim((string)($_POST['postcode']??''))?:null,'city'=>trim((string)($_POST['city']??''))?:null,'region'=>trim((string)($_POST['region']??''))?:null,'is_featured'=>isset($_POST['is_featured'])?1:0,'is_published'=>isset($_POST['is_published'])?1:0,'sale_commission_amount'=>$saleCommissionAmount,'sale_commission_percent'=>$saleCommissionPercent,'rent_commission_amount'=>$rentCommissionAmount,'rent_deposit'=>$rentDeposit,'rent_available_from'=>$rentAvailableFrom,'sold_amount'=>$soldAmount,'sold_commission_amount'=>$soldCommissionAmount,'sold_commission_percent'=>$soldCommissionPercent,'sold_to_name'=>$soldToName,'sold_to_email'=>$soldToEmail,'sold_to_phone'=>$soldToPhone,'sold_date'=>$soldDate,'mandate_number'=>$mandateNumber,'mandate_type'=>$mandateType,'mandate_start_date'=>$mandateStart,'mandate_end_date'=>$mandateEnd,'mandate_party_name'=>$mandatePartyName,'mandate_party_email'=>$mandatePartyEmail,'mandate_party_phone'=>$mandatePartyPhone,'energy_label'=>$energyLabel,'co2_label'=>$co2Label];
   if($fields['is_published'] && (!$property || !$property['published_at'])){$publishedAt=date('Y-m-d H:i:s');}else{$publishedAt=$property['published_at']??null;}
   if($property){$set=[];foreach($fields as $k=>$v){$set[]="$k=:$k";}$fields['id']=$id;$fields['published_at']=$publishedAt;$s=$db->prepare('UPDATE properties SET '.implode(',',$set).', published_at=:published_at WHERE id=:id');$s->execute($fields);}else{$fields['public_id']=uuidv4();$fields['currency_code']='EUR';$fields['country_code']='FR';$fields['published_at']=$publishedAt;$cols=array_keys($fields);$s=$db->prepare('INSERT INTO properties ('.implode(',',$cols).') VALUES (:'.implode(',:',$cols).')');$s->execute($fields);$id=(int)$db->lastInsertId();}
   if (!empty($_FILES['mandate_attachment']['name']) && is_uploaded_file($_FILES['mandate_attachment']['tmp_name'])) {
      $tmp=$_FILES['mandate_attachment']['tmp_name']; $size=(int)$_FILES['mandate_attachment']['size'];
      if ($size>15*1024*1024) throw new RuntimeException(__t('properties.mandate_attachment_too_large','Mandate attachment is too large (max 15 MB).'));
      $finfo=new finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file($tmp);
      $allowed=['application/pdf'=>'pdf','application/msword'=>'doc','application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx','image/jpeg'=>'jpg','image/png'=>'png'];
      if(!isset($allowed[$mime])) throw new RuntimeException(__t('properties.mandate_attachment_invalid','Please upload a PDF, Word document or image.'));
      $base=dirname(__DIR__).'/media/storage/properties/'.$id.'/mandate'; if(!is_dir($base)&&!mkdir($base,0755,true)&&!is_dir($base))throw new RuntimeException(__t('properties.mandate_attachment_storage','Could not create mandate storage directory.'));
      $name='mandate-'.bin2hex(random_bytes(10)).'.'.$allowed[$mime]; $dest=$base.'/'.$name;
      if(!move_uploaded_file($tmp,$dest))throw new RuntimeException(__t('properties.mandate_attachment_failed','Could not save the mandate attachment.'));
      $path='media/storage/properties/'.$id.'/mandate/'.$name;
      $db->prepare('UPDATE properties SET mandate_attachment_path=:path WHERE id=:id')->execute(['path'=>$path,'id'=>$id]);
   }
   $db->beginTransaction();$db->prepare('DELETE FROM property_features WHERE property_id=:p')->execute(['p'=>$id]);if($featureIds){$s=$db->prepare('INSERT INTO property_features (property_id,feature_id) VALUES (:p,:f)');foreach($featureIds as $fid){$s->execute(['p'=>$id,'f'=>$fid]);}}$db->commit();
   $redirect='property-edit.php?id='.(int)$id;
   if(isset($_GET['lang']) && $_GET['lang']!=='') $redirect.='&lang='.rawurlencode((string)$_GET['lang']);
   if(($_SERVER['HTTP_X_REQUESTED_WITH']??'')==='XMLHttpRequest'){header('Content-Type: application/json; charset=utf-8');echo json_encode(['success'=>true,'property_id'=>$id,'is_published'=>(int)$fields['is_published'],'is_featured'=>(int)$fields['is_featured'],'published_at'=>$publishedAt]);exit;}
   header('Location: '.$redirect); exit;
  } elseif($action==='upload'){
   if(empty($_FILES['photos']['name'][0]))throw new RuntimeException('Please select at least one photo.');$base=dirname(__DIR__).'/media/storage/properties/'.$id;if(!is_dir($base)&&!mkdir($base,0755,true)&&!is_dir($base))throw new RuntimeException('Could not create media directory.');@chmod($base,0755);$finfo=new finfo(FILEINFO_MIME_TYPE);$allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];$next=(int)$db->query('SELECT COALESCE(MAX(sort_order),-1)+1 FROM property_media WHERE property_id='.(int)$id.' AND deleted_at IS NULL')->fetchColumn();$primary=(int)$db->query('SELECT COUNT(*) FROM property_media WHERE property_id='.(int)$id.' AND deleted_at IS NULL AND is_primary=1')->fetchColumn()===0;
   foreach($_FILES['photos']['tmp_name'] as $i=>$tmp){if($_FILES['photos']['error'][$i]!==UPLOAD_ERR_OK)continue;if($_FILES['photos']['size'][$i]>12*1024*1024)continue;$mime=$finfo->file($tmp);if(!isset($allowed[$mime]))continue;$ext=$allowed[$mime];$name=bin2hex(random_bytes(12)).'.'.$ext;$dest=$base.'/'.$name;if(!move_uploaded_file($tmp,$dest))continue;@chmod($dest,0644);$info=@getimagesize($dest);$s=$db->prepare('INSERT INTO property_media (public_id,property_id,media_type,original_filename,file_path,mime_type,file_size,width,height,sort_order,is_primary) VALUES (:pub,:p,"photo",:orig,:path,:mime,:size,:w,:h,:sort,:primary)');$s->execute(['pub'=>uuidv4(),'p'=>$id,'orig'=>$_FILES['photos']['name'][$i],'path'=>'media/storage/properties/'.$id.'/'.$name,'mime'=>$mime,'size'=>filesize($dest),'w'=>$info[0]??null,'h'=>$info[1]??null,'sort'=>$next++,'primary'=>$primary?1:0]);$primary=false;}$message=__t('properties.photos_uploaded','Photos uploaded successfully.');
  } elseif($action==='video_upload'){
   if(empty($_FILES['videos']['name'][0])) throw new RuntimeException(__t('videos.no_files','Please select at least one video.'));
   $base=dirname(__DIR__).'/media/storage/properties/'.$id.'/videos';
   if(!is_dir($base)&&!mkdir($base,0755,true)&&!is_dir($base)) throw new RuntimeException(__t('videos.storage_error','Could not create video directory.'));
   @chmod($base,0755);
   $finfo=new finfo(FILEINFO_MIME_TYPE);
   $allowed=['video/mp4'=>'mp4','video/webm'=>'webm','video/ogg'=>'ogv','video/quicktime'=>'mov'];
   $maxVideo=250*1024*1024;
   $next=(int)$db->query('SELECT COALESCE(MAX(sort_order),-1)+1 FROM property_media WHERE property_id='.(int)$id.' AND deleted_at IS NULL')->fetchColumn();
   foreach($_FILES['videos']['tmp_name'] as $i=>$tmp){
     if($_FILES['videos']['error'][$i]!==UPLOAD_ERR_OK) continue;
     if($_FILES['videos']['size'][$i]>$maxVideo) continue;
     $mime=$finfo->file($tmp); if(!isset($allowed[$mime])) continue;
     $ext=$allowed[$mime];$name=bin2hex(random_bytes(12)).'.'.$ext;$dest=$base.'/'.$name;
     if(!move_uploaded_file($tmp,$dest)) continue; @chmod($dest,0644);
     $original=(string)$_FILES['videos']['name'][$i];
     $s=$db->prepare('INSERT INTO property_media (public_id,property_id,media_type,original_filename,file_path,mime_type,file_size,sort_order,is_primary) VALUES (:pub,:p,"video",:orig,:path,:mime,:size,:sort,0)');
     $s->execute(['pub'=>uuidv4(),'p'=>$id,'orig'=>$original,'path'=>'media/storage/properties/'.$id.'/videos/'.$name,'mime'=>$mime,'size'=>filesize($dest),'sort'=>$next++]);
   }
   $message=__t('videos.uploaded','Videos uploaded successfully.');
  } elseif($action==='video_external'){
   $url=trim((string)($_POST['video_url']??''));$title=trim((string)($_POST['video_title']??''));
   if(!filter_var($url,FILTER_VALIDATE_URL)) throw new RuntimeException(__t('videos.invalid_url','Please enter a valid video URL.'));
   $parts=parse_url($url);$host=strtolower((string)($parts['host']??''));$host=preg_replace('/^www\./','',$host);
   if(!in_array($host,['youtube.com','youtu.be','vimeo.com'],true)) throw new RuntimeException(__t('videos.provider','Only YouTube and Vimeo links are supported.'));
   if($title==='') $title='Video';
   $next=(int)$db->query('SELECT COALESCE(MAX(sort_order),-1)+1 FROM property_media WHERE property_id='.(int)$id.' AND deleted_at IS NULL')->fetchColumn();
   $s=$db->prepare('INSERT INTO property_media (public_id,property_id,media_type,original_filename,file_path,mime_type,file_size,sort_order,is_primary) VALUES (:pub,:p,"video",:orig,:path,"text/url",NULL,:sort,0)');
   $s->execute(['pub'=>uuidv4(),'p'=>$id,'orig'=>$title,'path'=>$url,'sort'=>$next]);
   $message=__t('videos.external_added','Video link added successfully.');
  } elseif($action==='video_update'){
   $mid=(int)($_POST['media_id']??0);$title=trim((string)($_POST['video_title']??''));
   if($title==='') throw new RuntimeException(__t('videos.title_required','A video title is required.'));
   $s=$db->prepare('UPDATE property_media SET original_filename=:title WHERE id=:id AND property_id=:p AND media_type="video" AND deleted_at IS NULL');
   $s->execute(['title'=>$title,'id'=>$mid,'p'=>$id]);
   if($s->rowCount()===0) throw new RuntimeException(__t('videos.not_found','Video not found.'));
   $message=__t('videos.updated','Video title updated.');
  } elseif($action==='document_upload'){
   if(empty($_FILES['documents']['name'][0])) throw new RuntimeException(__t('documents.no_files','Please select at least one document.'));
   $base=dirname(__DIR__).'/media/storage/properties/'.$id.'/documents';
   if(!is_dir($base)&&!mkdir($base,0755,true)&&!is_dir($base)) throw new RuntimeException(__t('documents.storage_error','Could not create document directory.'));
   @chmod($base,0755);
   $finfo=new finfo(FILEINFO_MIME_TYPE);
   $allowed=['application/pdf'=>'pdf','application/msword'=>'doc','application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'docx','application/vnd.ms-excel'=>'xls','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'xlsx','text/csv'=>'csv','text/plain'=>'txt','application/rtf'=>'rtf','application/vnd.oasis.opendocument.text'=>'odt','application/vnd.oasis.opendocument.spreadsheet'=>'ods','application/vnd.ms-powerpoint'=>'ppt','application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'pptx'];
   $next=(int)$db->query('SELECT COALESCE(MAX(sort_order),-1)+1 FROM property_documents WHERE property_id='.(int)$id.' AND deleted_at IS NULL')->fetchColumn();
   foreach($_FILES['documents']['tmp_name'] as $i=>$tmp){
     if($_FILES['documents']['error'][$i]!==UPLOAD_ERR_OK) continue;
     if($_FILES['documents']['size'][$i]>25*1024*1024) continue;
     $mime=$finfo->file($tmp); if(!isset($allowed[$mime])) continue;
     $ext=$allowed[$mime];$name=bin2hex(random_bytes(12)).'.'.$ext;$dest=$base.'/'.$name;
     if(!move_uploaded_file($tmp,$dest)) continue; @chmod($dest,0644);
     $original=(string)$_FILES['documents']['name'][$i];
     $title=pathinfo($original,PATHINFO_FILENAME); if($title==='') $title=$original;
     $cat='other';
     $s=$db->prepare('INSERT INTO property_documents (public_id,property_id,category,title,original_filename,file_path,mime_type,file_size,sort_order) VALUES (:pub,:p,:cat,:title,:orig,:path,:mime,:size,:sort)');
     $s->execute(['pub'=>uuidv4(),'p'=>$id,'cat'=>$cat,'title'=>$title,'orig'=>$original,'path'=>'media/storage/properties/'.$id.'/documents/'.$name,'mime'=>$mime,'size'=>filesize($dest),'sort'=>$next++]);
   }
   $message=__t('documents.uploaded','Documents uploaded successfully.');
  } elseif($action==='document_update'){
   $did=(int)($_POST['document_id']??0);$category=(string)($_POST['category']??'other');$title=trim((string)($_POST['document_title']??''));
   $allowedCats=['administrative','legal','contracts','diagnostics','energy','technical','financial','ownership','marketing','other'];
   if(!in_array($category,$allowedCats,true))$category='other'; if($title==='') throw new RuntimeException(__t('documents.title_required','A document title is required.'));
   $s=$db->prepare('UPDATE property_documents SET title=:title,category=:cat WHERE id=:id AND property_id=:p AND deleted_at IS NULL');$s->execute(['title'=>$title,'cat'=>$category,'id'=>$did,'p'=>$id]);if($s->rowCount()===0)throw new RuntimeException(__t('documents.not_found','Document not found.'));$message=__t('documents.updated','Document updated.');
  } elseif($action==='document_delete'){
   $did=(int)($_POST['document_id']??0);$s=$db->prepare('SELECT file_path FROM property_documents WHERE id=:id AND property_id=:p AND deleted_at IS NULL LIMIT 1');$s->execute(['id'=>$did,'p'=>$id]);$doc=$s->fetch();if(!$doc)throw new RuntimeException(__t('documents.not_found','Document not found.'));
   $db->beginTransaction();$db->prepare('DELETE FROM property_documents WHERE id=:id AND property_id=:p')->execute(['id'=>$did,'p'=>$id]);$rows=$db->prepare('SELECT id FROM property_documents WHERE property_id=:p AND deleted_at IS NULL ORDER BY sort_order,id');$rows->execute(['p'=>$id]);$ids=$rows->fetchAll(PDO::FETCH_COLUMN);$u=$db->prepare('UPDATE property_documents SET sort_order=:sort WHERE id=:id AND property_id=:p');foreach($ids as $idx=>$rid)$u->execute(['sort'=>$idx,'id'=>(int)$rid,'p'=>$id]);$db->commit();$path=dirname(__DIR__).'/'.ltrim((string)$doc['file_path'],'/');if(is_file($path))@unlink($path);$message=__t('documents.deleted','Document deleted.');
  } elseif(in_array($action,['primary','photo_delete','video_delete'],true)){
   $mid=(int)($_POST['media_id']??0);
   $s=$db->prepare('SELECT id,file_path FROM property_media WHERE id=:m AND property_id=:p AND deleted_at IS NULL LIMIT 1');
   $s->execute(['m'=>$mid,'p'=>$id]);$mediaRow=$s->fetch();
   if(!$mediaRow) throw new RuntimeException($action==='video_delete' ? 'Video not found.' : 'Photo not found.');
   if($action==='primary'){
      $db->beginTransaction();
      $db->prepare('UPDATE property_media SET is_primary=0 WHERE property_id=:p AND deleted_at IS NULL')->execute(['p'=>$id]);
      $db->prepare('UPDATE property_media SET is_primary=1 WHERE id=:m AND property_id=:p')->execute(['m'=>$mid,'p'=>$id]);
      $db->commit();$message=__t('properties.primary_updated','Primary photo updated.');
   } else {
      $db->beginTransaction();
      $db->prepare('DELETE FROM property_media WHERE id=:m AND property_id=:p')->execute(['m'=>$mid,'p'=>$id]);
      $remaining=$db->prepare('SELECT id FROM property_media WHERE property_id=:p AND deleted_at IS NULL ORDER BY sort_order,id');
      $remaining->execute(['p'=>$id]);
      $rows=$remaining->fetchAll(PDO::FETCH_COLUMN);
      $reorder=$db->prepare('UPDATE property_media SET sort_order=:sort WHERE id=:id AND property_id=:p');
      foreach($rows as $idx=>$rid){$reorder->execute(['sort'=>$idx,'id'=>(int)$rid,'p'=>$id]);}
      $hasPrimary=(int)$db->query('SELECT COUNT(*) FROM property_media WHERE property_id='.(int)$id.' AND deleted_at IS NULL AND is_primary=1')->fetchColumn();
      if(!$hasPrimary && $rows){$db->prepare('UPDATE property_media SET is_primary=1 WHERE id=:id AND property_id=:p')->execute(['id'=>(int)$rows[0],'p'=>$id]);}
      $db->commit();
      $path=dirname(__DIR__).'/'.ltrim((string)$mediaRow['file_path'],'/');
      if(is_file($path)) @unlink($path);
      $message='Photo deleted.';
   }
  }
 }catch(Throwable $ex){if($db->inTransaction())$db->rollBack();$error=$ex->getMessage();}
}
if($id){$s=$db->prepare('SELECT * FROM properties WHERE id=:id AND deleted_at IS NULL LIMIT 1');$s->execute(['id'=>$id]);$property=$s->fetch();}
$offices=$role==='super_admin'?$db->query('SELECT id,name FROM offices WHERE is_active=1 AND deleted_at IS NULL ORDER BY name')->fetchAll():[];
$agents=$db->query('SELECT id,office_id,first_name,last_name FROM agents WHERE is_active=1 AND deleted_at IS NULL ORDER BY last_name,first_name')->fetchAll();
$types=$db->query('SELECT id,code,name_key FROM property_types WHERE is_active=1 ORDER BY sort_order,code')->fetchAll();
$features=$db->query('SELECT id,code,name_key,category FROM features WHERE is_active=1 ORDER BY category,sort_order,code')->fetchAll();$selected=[];if($id){$s=$db->prepare('SELECT feature_id FROM property_features WHERE property_id=:p');$s->execute(['p'=>$id]);$selected=array_map('intval',$s->fetchAll(PDO::FETCH_COLUMN));}$heatingCodes=['heating_oil','heating_wood','heating_gas','heating_heat_pump'];$heatingFeatures=array_values(array_filter($features,fn($f)=>in_array($f['code'],$heatingCodes,true)));$heatingSelected=0;foreach($heatingFeatures as $hf){if(in_array((int)$hf['id'],$selected,true)){$heatingSelected=(int)$hf['id'];break;}}$underfloorFeatureId=0;foreach($features as $uf){if(($uf['code']??'')==='underfloor_heating'){$underfloorFeatureId=(int)$uf['id'];break;}}$underfloorSelected=$underfloorFeatureId>0 && in_array($underfloorFeatureId,$selected,true);
$media=[];if($id){$s=$db->prepare('SELECT id,media_type,original_filename,file_path,mime_type,file_size,alt_text,sort_order,is_primary,width,height FROM property_media WHERE property_id=:p AND deleted_at IS NULL ORDER BY sort_order,id');$s->execute(['p'=>$id]);$media=$s->fetchAll();}$photos=array_values(array_filter($media,fn($m)=>(string)$m['media_type']==='photo'));$videos=array_values(array_filter($media,fn($m)=>(string)$m['media_type']==='video'));$documents=[];if($id){$s=$db->prepare('SELECT id,category,title,original_filename,file_path,mime_type,file_size,sort_order FROM property_documents WHERE property_id=:p AND deleted_at IS NULL ORDER BY sort_order,id');$s->execute(['p'=>$id]);$documents=$s->fetchAll();}$csrf=\App\Csrf::token();
$languages=$db->query('SELECT code,native_name FROM i18n_languages WHERE is_active=1 ORDER BY id')->fetchAll();
$headerUserStmt=$db->prepare('SELECT first_name,last_name,office_id FROM users WHERE id=:id LIMIT 1');
$headerUserStmt->execute(['id'=>$userId]);
$headerUser=$headerUserStmt->fetch() ?: [];
$headerOfficeName='Platform';
if(!empty($headerUser['office_id'])){
    $headerOfficeStmt=$db->prepare('SELECT name FROM offices WHERE id=:id AND deleted_at IS NULL LIMIT 1');
    $headerOfficeStmt->execute(['id'=>(int)$headerUser['office_id']]);
    $headerOfficeName=(string)($headerOfficeStmt->fetchColumn() ?: 'Platform');
}
$headerUserName=trim((string)($headerUser['first_name']??'').' '.(string)($headerUser['last_name']??''));
if($headerUserName==='')$headerUserName='User';
$headerInitials='';
foreach(preg_split('/\s+/', $headerUserName) as $part){ if($part!=='') $headerInitials.=strtoupper(substr($part,0,1)); }
$headerInitials=substr($headerInitials,0,2);
function val(array|false|null $p,string $k): string{return e($p[$k]??'');}
?><!doctype html><html lang="<?=e($i18n->getLanguage())?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($property?'Edit property':'Create property')?> - Prrepl</title><style>
body{margin:0;background:#f5f7fb;color:#172033;font-family:Inter,system-ui,sans-serif}.wrap{max-width:1300px;margin:auto;padding:28px 22px 60px}.top{display:flex;justify-content:space-between;gap:20px;align-items:center}.top-actions{display:flex;align-items:center;gap:12px;flex-wrap:wrap}.nav{display:flex;gap:8px}.nav a{padding:8px 11px;border:1px solid #d0d5dd;border-radius:9px;text-decoration:none;color:#172033;background:#fff}.languages{display:flex;gap:4px}.lang{font-size:20px;text-decoration:none;padding:5px 7px;border-radius:8px;opacity:.65}.lang.active{opacity:1;background:#eef2f6;box-shadow:inset 0 0 0 1px #d0d5dd}.card{background:#fff;border:1px solid #e4e7ec;border-radius:14px;padding:22px;margin:18px 0}.grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px}.span2{grid-column:span 2}.span4{grid-column:1/-1}label{display:block;font-size:13px;font-weight:650;margin-bottom:6px}input,select,textarea{width:100%;box-sizing:border-box;padding:10px;border:1px solid #d0d5dd;border-radius:9px;font:inherit}textarea{min-height:130px}.actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}button,.btn{background:#111827;color:#fff;border:0;border-radius:9px;padding:10px 14px;text-decoration:none;cursor:pointer}.light{background:#fff;color:#172033;border:1px solid #d0d5dd}.danger{background:#b42318}.notice,.error{padding:12px;border-radius:9px}.notice{background:#ecfdf3;color:#067647}.error{background:#fef3f2;color:#b42318}.checks{display:grid;grid-template-columns:repeat(3,1fr);gap:9px}.feature{display:flex;gap:8px;align-items:center;padding:8px;background:#f8fafc;border-radius:8px}.feature input{width:auto}.photos{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}.photo{border:1px solid #e4e7ec;border-radius:10px;padding:8px}.photo img{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:7px}.photo .actions{margin-top:8px}.small{font-size:12px;color:#667085}.pool-fields{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.pool-fields[hidden]{display:none!important}@media(max-width:900px){.grid{grid-template-columns:repeat(2,1fr)}.photos{grid-template-columns:repeat(2,1fr)}}@media(max-width:600px){.grid,.checks,.pool-fields{grid-template-columns:1fr}.span2,.span4{grid-column:auto}.photos{grid-template-columns:1fr}.top{align-items:flex-start;flex-direction:column}}
.rich-editor{border:1px solid #d0d5dd;border-radius:10px;background:#fff;overflow:hidden}.rich-toolbar{display:flex;gap:4px;flex-wrap:wrap;padding:8px;background:#f8fafc;border-bottom:1px solid #eaecf0}.rich-toolbar button{background:#fff;color:#344054;border:1px solid #d0d5dd;border-radius:6px;padding:5px 9px;cursor:pointer}.rich-content{min-height:120px;padding:12px;outline:none;line-height:1.55}.rich-editor[data-editor="description"] .rich-content{min-height:260px}.rich-content:focus{box-shadow:inset 0 0 0 2px #dbeafe}.rich-content p{margin:0 0 10px}.rich-content h2,.rich-content h3{margin:12px 0 8px}.rich-content ul,.rich-content ol{padding-left:24px}.rich-note{font-size:12px;color:#667085;margin-top:6px}.rich-source{display:none}
.dropzone{border:2px dashed #cfd4dc;border-radius:14px;padding:28px;text-align:center;background:#fafbfc;cursor:pointer;transition:.15s}.dropzone.dragover{border-color:#111827;background:#f2f4f7}.drop-title{font-weight:700;font-size:16px;margin-bottom:5px}.dropzone input{display:none}.upload-preview{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}.upload-preview .preview{width:110px}.upload-preview img{width:110px;height:80px;object-fit:cover;border-radius:8px}.photo-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-top:20px}.photo{position:relative;border:1px solid #e4e7ec;border-radius:12px;padding:10px;background:#fff}.photo.dragging{opacity:.45}.photo.drag-over{outline:2px dashed #111827}.photo img{display:block;width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:8px;background:#f2f4f7}.drag-handle{position:absolute;top:16px;right:16px;background:rgba(255,255,255,.92);border-radius:7px;padding:5px 9px;cursor:grab;font-weight:800;z-index:2}.photo .actions{flex-wrap:wrap}.photo .actions form{margin:0}.empty{padding:28px;text-align:center;color:#667085;background:#fafbfc;border-radius:10px}.video-drag-handle{float:right;background:#fff;border:1px solid #d0d5dd;border-radius:7px;padding:4px 8px;cursor:grab;position:relative;z-index:3}.video-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-top:20px}.video-card{border:1px solid #e4e7ec;border-radius:12px;padding:12px;background:#fff}.video-preview{background:#111827;border-radius:9px;overflow:hidden;min-height:180px;display:flex;align-items:center;justify-content:center}.video-preview video{display:block;width:100%;max-height:360px;background:#000}.external-video{color:#fff;display:flex;flex-direction:column;align-items:center;gap:10px;padding:30px;text-align:center}.external-video span{font-size:34px}.external-video a{color:#fff;text-decoration:underline}.video-meta{display:flex;justify-content:space-between;gap:10px;margin:10px 0;font-size:13px}.video-edit{display:flex;gap:8px;margin:8px 0}.video-edit input{flex:1}.video-external{margin-top:18px;padding-top:18px;border-top:1px solid #eaecf0}@media(max-width:900px){.video-grid{grid-template-columns:1fr}}
.document-grid{display:grid;gap:10px;margin-top:18px}.document-row{display:grid;grid-template-columns:48px minmax(180px,1.4fr) 180px 150px auto;gap:10px;align-items:center;border:1px solid #e4e7ec;border-radius:12px;padding:10px;background:#fff}.doc-icon{width:40px;height:40px;border-radius:9px;background:#f2f4f7;display:grid;place-items:center;font-size:21px}.document-row input,.document-row select{padding:8px}.doc-meta{font-size:12px;color:#667085}.document-filter{display:flex;gap:10px;align-items:center;margin-top:14px}.document-filter select{max-width:260px}@media(max-width:900px){.document-row{grid-template-columns:42px 1fr}.document-row .doc-category,.document-row .doc-actions,.document-row .doc-meta{grid-column:2}.document-filter{flex-wrap:wrap}}@media(max-width:1000px){.photo-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:700px){.photo-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
/* v19 property UX */
.property-shell{margin-top:18px}.property-tabs{position:sticky;top:0;z-index:20;display:flex;gap:4px;overflow-x:auto;padding:6px;background:rgba(255,255,255,.96);backdrop-filter:blur(10px);border:1px solid #e4e7ec;border-radius:14px;box-shadow:0 4px 18px rgba(16,24,40,.06)}
.property-tab{flex:0 0 auto;border:0!important;background:transparent!important;color:#667085!important;font-weight:650;padding:10px 14px!important;border-radius:9px!important;white-space:nowrap}.property-tab:hover{background:#f2f4f7!important;color:#172033!important}.property-tab.active{background:#172033!important;color:#fff!important}
.tab-panel{display:none}.tab-panel.active{display:block}.tab-panel>.card{margin-top:16px}.tab-panel-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px}.tab-panel-grid .span2{grid-column:span 2}.tab-panel-grid .span4{grid-column:1/-1}
.form-footer{position:sticky;bottom:16px;z-index:15;display:flex;justify-content:flex-end;gap:10px;margin-top:16px;padding:12px 14px;background:rgba(255,255,255,.94);backdrop-filter:blur(10px);border:1px solid #e4e7ec;border-radius:12px;box-shadow:0 8px 30px rgba(16,24,40,.10)}
.section-intro{margin:-4px 0 18px;color:#667085;font-size:13px}.field-group{background:#f8fafc;border:1px solid #eaecf0;border-radius:12px;padding:14px}.feature-section{background:#fff}.feature-category{margin-bottom:18px}.feature-category:last-child{margin-bottom:0}.feature-category-title{font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:#667085;font-weight:750;margin:0 0 8px}.checks{grid-template-columns:repeat(3,minmax(0,1fr))}.feature{background:#f8fafc;border:1px solid #eaecf0}.feature:has(input:checked){background:#eef4ff;border-color:#b2ddff}.media-card{margin-top:16px}.media-card h2{margin-bottom:6px}.media-header{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap}.tab-badge{font-size:12px;font-weight:700;padding:4px 8px;border-radius:999px;background:#f2f4f7;color:#475467}.top h1{letter-spacing:-.02em}.top .small{margin-top:5px}
@media(max-width:900px){.tab-panel-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.checks{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:600px){.tab-panel-grid,.checks{grid-template-columns:1fr}.tab-panel-grid .span2,.tab-panel-grid .span4{grid-column:auto}.property-tabs{top:0}.property-tab{padding:9px 11px!important;font-size:13px}.form-footer{bottom:8px}}

/* v20 premium SaaS layer */
:root{--immo-bg:#f4f6fa;--immo-card:#fff;--immo-ink:#172033;--immo-muted:#667085;--immo-line:#e6eaf0;--immo-accent:#315cf6;--immo-success:#087443;--immo-warning:#b54708;--immo-danger:#b42318;--immo-radius:18px;--immo-shadow:0 10px 34px rgba(16,24,40,.055)}
body{background:var(--immo-bg);color:var(--immo-ink);font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
.wrap{max-width:1480px;margin:0 auto;padding:22px 30px 80px}.top{display:none}.card{border:1px solid var(--immo-line);border-radius:var(--immo-radius);box-shadow:var(--immo-shadow);background:var(--immo-card);padding:24px}.property-hero{display:grid;grid-template-columns:112px minmax(0,1fr) auto;gap:20px;align-items:center;background:#fff;border:1px solid var(--immo-line);border-radius:24px;padding:18px 20px;box-shadow:var(--immo-shadow)}
.property-hero-photo{width:112px;height:90px;border-radius:15px;object-fit:cover;background:#eef1f5}.property-hero-empty{display:grid;place-items:center;font-size:30px;color:#98a2b3}.property-hero h1{margin:0;font-size:27px;line-height:1.15;letter-spacing:-.025em}.property-kicker{display:flex;gap:8px;align-items:center;flex-wrap:wrap;color:var(--immo-muted);font-size:12px;margin-top:7px}.property-price{font-size:22px;font-weight:800;margin-top:9px;letter-spacing:-.015em}.property-facts{display:flex;gap:14px;flex-wrap:wrap;color:var(--immo-muted);font-size:12px;margin-top:7px}.property-status{display:flex;align-items:flex-end;flex-direction:column;gap:10px}.property-badges{display:flex;gap:7px;flex-wrap:wrap;justify-content:flex-end}.pbadge{display:inline-flex;align-items:center;gap:5px;border-radius:999px;padding:6px 10px;background:#f1f3f6;color:#475467;font-size:11px;font-weight:800}.pbadge.published{background:#ecfdf3;color:var(--immo-success)}.pbadge.featured{background:#fff7e6;color:var(--immo-warning)}.property-actions{display:flex;gap:8px;align-items:center}.property-actions .save{background:var(--immo-accent);color:#fff;border:0;border-radius:11px;padding:10px 15px;font-weight:750;cursor:pointer}.property-actions .save:hover{background:#2348d8}.property-actions .back{color:var(--immo-muted);text-decoration:none;font-size:12px}
.property-complete{display:flex;align-items:center;gap:14px;margin-top:13px;padding:11px 15px;background:#fff;border:1px solid var(--immo-line);border-radius:14px}.property-complete .bar{height:6px;flex:1;background:#edf0f4;border-radius:999px;overflow:hidden}.property-complete .bar span{display:block;height:100%;background:var(--immo-accent);border-radius:999px}.property-complete .label{font-size:12px;font-weight:750}.property-complete .items{display:flex;gap:10px;flex-wrap:wrap;color:var(--immo-muted);font-size:11px}.property-complete .ok{color:var(--immo-success)}
.property-shell{margin-top:15px}.property-tabs{position:sticky;top:0;z-index:40;padding:7px;background:rgba(255,255,255,.88);backdrop-filter:blur(14px);border:1px solid var(--immo-line);border-radius:16px;box-shadow:0 7px 24px rgba(16,24,40,.055)}.property-tab{padding:10px 14px!important;border-radius:10px!important;font-size:13px!important}.property-tab.active{background:#172033!important;box-shadow:0 2px 5px rgba(16,24,40,.12)}
.tab-panel>.card{margin-top:16px}.section-intro{font-size:12px;color:var(--immo-muted);margin-bottom:20px}.tab-panel-grid{gap:18px}.field-group,.feature-section{border:0!important;background:transparent!important}.feature{border:1px solid var(--immo-line)!important;border-radius:13px!important;background:#fff!important;padding:12px!important;transition:.15s}.feature:hover{border-color:#c6d0e3!important}.feature:has(input:checked){background:#f4f7ff!important;border-color:#afbeff!important}.form-footer{position:sticky;bottom:15px;z-index:35;border:1px solid var(--immo-line);background:rgba(255,255,255,.92);backdrop-filter:blur(14px);border-radius:14px;box-shadow:0 10px 32px rgba(16,24,40,.10)}
@media(max-width:900px){.wrap{padding:15px 14px 60px}.property-hero{grid-template-columns:76px 1fr}.property-hero-photo{width:76px;height:66px}.property-status{grid-column:1/-1;align-items:flex-start}.property-badges{justify-content:flex-start}.property-actions{width:100%}.property-actions .save{flex:1}.property-complete{align-items:flex-start;flex-direction:column}.property-complete .bar{width:100%;flex:none}}

/* v27 media and workflow */
#videos,#documents{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(320px,.75fr);column-gap:22px;align-items:start}
#videos>h2,#videos>p,#documents>h2,#documents>p{grid-column:1/-1}
#videoUploadForm,#documentUploadForm{grid-column:2;grid-row:3;position:sticky;top:86px;background:#f8fafc;border:1px solid var(--immo-line);border-radius:16px;padding:16px}
#videos .video-external{grid-column:2;grid-row:4;margin-top:0;padding:16px;background:#f8fafc;border:1px solid var(--immo-line);border-radius:16px}
#videos .video-grid{grid-column:1;grid-row:3 / span 2;margin-top:0}
#documents .document-filter{grid-column:2;grid-row:4;padding:12px 14px;background:#f8fafc;border:1px solid var(--immo-line);border-radius:12px}
#documents .document-grid{grid-column:1;grid-row:3 / span 2;margin-top:0}
.photo-grid{grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}.photo{padding:8px;border-radius:16px;box-shadow:0 5px 18px rgba(16,24,40,.05);transition:transform .16s,box-shadow .16s}.photo:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(16,24,40,.10)}.photo img{border-radius:11px}.photo.drag-over{outline:2px solid var(--immo-accent);outline-offset:2px}.video-card{border-radius:16px;box-shadow:0 5px 18px rgba(16,24,40,.05)}.document-row{box-shadow:0 3px 12px rgba(16,24,40,.035)}
@media(max-width:700px){.photo-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}

.status-grid-note{font-size:12px;color:#667085;margin-top:6px}.sold-only[style*="display: none"]{display:none!important}.property-tab .tab-icon{display:inline-flex;align-items:center;justify-content:center;min-width:18px;margin-right:4px;font-size:15px;line-height:1;color:inherit}.property-tab[data-tab="energy"] .tab-icon{color:inherit}
</style><style>
.energy-label-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.energy-label-card{border:1px solid #e5e7eb;border-radius:14px;padding:18px;background:#fff}
.energy-label-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:16px}
.energy-label-head h3{margin:3px 0 0;font-size:20px}
.energy-label-head select{min-width:80px;height:40px;border:1px solid #d0d5dd;border-radius:8px;padding:0 10px;background:#fff}
.energy-label-image{min-height:220px;display:flex;align-items:center;justify-content:center}
.energy-label-image img{max-width:100%;max-height:220px;width:auto;height:auto;object-fit:contain}
@media(max-width:760px){.energy-label-grid{grid-template-columns:1fr}}
</style><link rel="stylesheet" href="/immobilier/assets/css/immo-saas.css?v=31"><link rel="stylesheet" href="/immobilier/assets/css/property-edit-shell.css?v=40"><link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style> .autosave-status{font-size:12px;font-weight:700;color:#667085}.autosave-status.saving{color:#b54708}.autosave-status.saved{color:#087443}.autosave-status.error{color:#b42318;background:transparent;padding:0}.property-fact svg{width:18px;height:18px}.property-fact{display:inline-flex;gap:7px;align-items:center}.property-fact span{display:flex;flex-direction:column}.property-fact small{font-size:10px;color:#98a2b3}.location-layout,.publication-layout{display:grid;grid-template-columns:1fr 1.25fr;gap:18px}.property-map{height:390px;border-radius:16px;overflow:hidden;background:#eef2f6;border:1px solid #e4e7ec}.map-placeholder{height:100%;display:grid;place-items:center;font-size:40px;color:#98a2b3}.location-note{margin-top:14px;font-size:12px;color:#667085;background:#f8fafc;padding:10px 12px;border-radius:10px}.map-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}.toggle-row{display:grid;grid-template-columns:20px 38px 1fr;gap:10px;align-items:center;padding:15px 0;border-bottom:1px solid #eaecf0}.toggle-row:last-child{border-bottom:0}.toggle-row input{position:absolute;opacity:0;pointer-events:none}.toggle-ui{width:38px;height:22px;border-radius:999px;background:#d0d5dd;position:relative;transition:.2s}.toggle-ui:after{content:"";position:absolute;width:16px;height:16px;left:3px;top:3px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:.2s}.toggle-row input:checked+.toggle-ui{background:#315cf6}.toggle-row input:checked+.toggle-ui:after{transform:translateX(16px)}.toggle-row small{display:block;color:#667085;margin-top:3px}.pub-status-pill{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;background:#f2f4f7;font-weight:800;margin:12px 0}.pub-status-pill span{width:8px;height:8px;border-radius:50%;background:#98a2b3}.pub-status-pill.is-published{background:#ecfdf3;color:#087443}.pub-status-pill.is-published span{background:#12b76a}@media(max-width:900px){.location-layout,.publication-layout{grid-template-columns:1fr}.property-map{height:320px}}</style><style>
.form-footer{display:none!important}.heating-group{margin-top:24px}.heating-options{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.heating-option{display:flex;align-items:center;gap:11px;padding:14px;border:1px solid var(--immo-line);border-radius:13px;background:#fff;cursor:pointer;transition:.15s}.heating-option:hover{border-color:#afbeff}.heating-option input{position:absolute;opacity:0;pointer-events:none}.heating-radio{width:18px;height:18px;border:2px solid #c4cad4;border-radius:50%;position:relative;flex:0 0 auto}.heating-option input:checked~.heating-radio{border-color:var(--immo-accent)}.heating-option input:checked~.heating-radio:after{content:"";position:absolute;inset:3px;border-radius:50%;background:var(--immo-accent)}.heating-option:has(input:checked){background:#f4f7ff;border-color:#afbeff;box-shadow:0 0 0 1px rgba(49,92,246,.05)}.heating-option strong{display:block}.heating-option small{display:block;color:var(--immo-muted);margin-top:3px}.publication-choice{display:flex;justify-content:space-between;gap:20px;align-items:center;padding:18px 0;border-bottom:1px solid var(--immo-line)}.publication-choice:last-child{border-bottom:0}.publication-choice small{display:block;color:var(--immo-muted);margin-top:4px}.switch{position:relative;width:46px;height:26px;flex:0 0 auto}.switch input{position:absolute;opacity:0}.switch span{position:absolute;inset:0;background:#d0d5dd;border-radius:999px;transition:.2s}.switch span:after{content:"";position:absolute;width:20px;height:20px;left:3px;top:3px;background:#fff;border-radius:50%;box-shadow:0 1px 4px rgba(16,24,40,.18);transition:.2s}.switch input:checked+span{background:var(--immo-accent)}.switch input:checked+span:after{transform:translateX(20px)}.publication-state-card{margin-top:8px;padding:18px;border-radius:14px;background:#f8fafc;border:1px solid var(--immo-line)}.publication-featured-state{display:flex;justify-content:space-between;gap:12px;margin-top:18px;padding-top:15px;border-top:1px solid var(--immo-line);font-size:13px}.publication-featured-state strong{color:var(--immo-accent)}@media(max-width:1000px){.heating-options{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.heating-options{grid-template-columns:1fr}}

.heating-trigger-row{display:none}.heating-tile{width:100%;text-align:left;border:1px solid var(--immo-line);background:#fff}.heating-tile:hover{border-color:#afbeff;background:#f8faff}.heating-tile .feature-check{font-size:24px;color:var(--immo-muted)}
.heating-modal[hidden]{display:none!important}.heating-modal{position:fixed;inset:0;z-index:10000;display:grid;place-items:center;padding:20px}.heating-modal-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.48);backdrop-filter:blur(3px)}.heating-dialog{position:relative;width:min(560px,100%);background:#fff;border-radius:20px;box-shadow:0 24px 80px rgba(15,23,42,.25);overflow:hidden}.heating-dialog-head{display:flex;justify-content:space-between;align-items:flex-start;padding:22px 24px;border-bottom:1px solid var(--immo-line)}.heating-dialog-head h2{margin:4px 0 0}.heating-close{border:0;background:#f2f4f7;width:34px;height:34px;border-radius:50%;font-size:23px;cursor:pointer}.heating-dialog-body{padding:24px}.heating-field+.heating-field{margin-top:24px}.heating-field-label{display:block;font-weight:700;font-size:13px;margin-bottom:10px}.heating-modal-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.heating-modal .heating-option{position:relative}.heating-modal .heating-option input{position:absolute;opacity:0;pointer-events:none}.heating-modal .heating-option:has(input:checked){background:#f4f7ff;border-color:#afbeff}.heating-toggle{display:inline-flex;align-items:center;gap:12px;cursor:pointer;font-weight:600}.heating-toggle input{position:absolute;opacity:0}.heating-toggle-track{width:46px;height:26px;border-radius:99px;background:#d0d5dd;position:relative;transition:.18s}.heating-toggle-track:after{content:"";position:absolute;left:3px;top:3px;width:20px;height:20px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.2);transition:.18s}.heating-toggle input:checked+.heating-toggle-track{background:var(--immo-accent)}.heating-toggle input:checked+.heating-toggle-track:after{transform:translateX(20px)}.heating-dialog-foot{display:flex;justify-content:flex-end;gap:10px;padding:16px 24px;background:#fafbfc;border-top:1px solid var(--immo-line)}@media(max-width:600px){.heating-modal-options{grid-template-columns:1fr}.heating-dialog{max-height:90vh;overflow:auto}}

/* v35 Comfort / Heating */
.heating-tile{cursor:pointer}
.heating-tile.selected{background:#eef4ff;border-color:#b2ddff}

</style>

<style>
.feature-summary{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px;margin:0 0 18px;padding:10px;background:#f8fafc;border:1px solid var(--immo-line);border-radius:16px}.feature-summary-item{display:flex;align-items:center;gap:10px;min-width:0;padding:10px 12px;background:#fff;border:1px solid var(--immo-line);border-radius:12px}.feature-summary-icon{width:30px;height:30px;display:grid;place-items:center;border-radius:9px;background:#f2f4f7;color:#667085;font-size:16px;flex:0 0 auto}.feature-summary-item span:last-child{display:flex;flex-direction:column;min-width:0}.feature-summary-item strong{font-size:14px;font-weight:800;color:#172033;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.feature-summary-item small{font-size:10px;color:#98a2b3;margin-top:2px}.sold-result-summary{margin-top:16px;padding:16px;background:#f8fafc;border:1px solid var(--immo-line);border-radius:15px}.sold-summary-head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px}.sold-summary-head h3{margin:3px 0 0;font-size:16px}.sold-edit-button{font:inherit;font-size:12px;padding:8px 11px}.sold-summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.sold-summary-grid>div{padding:10px 12px;background:#fff;border:1px solid #eaecf0;border-radius:11px;min-width:0}.sold-summary-grid small{display:block;color:#667085;font-size:10px;margin-bottom:4px}.sold-summary-grid strong{font-size:14px;font-weight:800;color:#172033;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.sold-summary-person{grid-column:1/-1}.sold-summary-person span{display:block;color:#667085;font-size:11px;margin-top:4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.status-helper{margin-top:12px;color:#98a2b3;font-size:11px}.sold-modal[hidden]{display:none!important}.sold-modal{position:fixed;inset:0;z-index:10050;display:grid;place-items:center;padding:20px}.sold-modal-backdrop{position:absolute;inset:0;background:rgba(15,23,42,.48);backdrop-filter:blur(4px)}.sold-dialog{position:relative;width:min(760px,100%);max-height:min(88vh,760px);overflow:auto;background:#fff;border-radius:20px;box-shadow:0 24px 80px rgba(15,23,42,.25)}.sold-dialog-head{display:flex;justify-content:space-between;gap:18px;padding:24px;border-bottom:1px solid var(--immo-line)}.sold-dialog-head h2{margin:4px 0 5px}.sold-dialog-head p{margin:0;color:#667085;font-size:12px;line-height:1.5;max-width:580px}.sold-close{border:0;background:#f2f4f7;width:34px;height:34px;border-radius:50%;font-size:23px;cursor:pointer;flex:0 0 auto}.sold-dialog-body{padding:24px}.sold-dialog-foot{display:flex;justify-content:flex-end;gap:10px;padding:16px 24px;background:#fafbfc;border-top:1px solid var(--immo-line)}.sold-dialog-foot .btn-secondary,.sold-dialog-foot .btn-primary{font:inherit;border-radius:10px;padding:10px 14px;cursor:pointer}.sold-dialog-foot .btn-secondary{background:#fff;color:#172033;border:1px solid #d0d5dd}.sold-dialog-foot .btn-primary{background:#172033;color:#fff;border:0}@media(max-width:1000px){.feature-summary{grid-template-columns:repeat(3,minmax(0,1fr))}.sold-summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.feature-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.sold-summary-grid{grid-template-columns:1fr}.sold-summary-person{grid-column:auto}.sold-dialog{max-height:94vh}.sold-dialog-head,.sold-dialog-body{padding:18px}}
</style>

<style>
.feature-edit-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:16px;margin:0 0 22px;padding:18px 20px;background:#f8f9fb;border:1px solid #e4e7ec;border-radius:14px;}
.feature-edit-grid>div{min-width:0;}
.feature-edit-grid label{display:block;margin:0 0 7px;font-size:11px;font-weight:700;color:#475467;line-height:1.35;}
.feature-edit-grid input{width:100%;box-sizing:border-box;height:44px;padding:10px 12px;border:1px solid #d0d5dd;border-radius:9px;background:#fff;color:#172033;font-family:inherit;font-size:15px;line-height:1.35;outline:none;}
.feature-edit-grid input:focus{border-color:#98a2b3;box-shadow:0 0 0 3px rgba(16,24,40,.06);}
@media(max-width:1100px){.feature-edit-grid{grid-template-columns:repeat(3,minmax(0,1fr));}}
@media(max-width:700px){.feature-edit-grid{grid-template-columns:repeat(2,minmax(0,1fr));}}
</style>

<style id="immo-modal-hidden-fix">.sold-modal[hidden],.heating-modal[hidden],.modal-backdrop[hidden]{display:none!important;pointer-events:none!important}</style>
</head><body class="prrepl-app"><div class="pr-app-shell">
  <aside class="pr-sidebar" aria-label="Main navigation">
  <div class="pr-brand"><span class="pr-brand-mark">P</span><span class="pr-brand-text"><?=t('common.platform','Platform')?></span></div>
  <div class="pr-workspace"><span class="pr-workspace-label"><?=t('common.workspace','WORKSPACE')?></span><strong><?=e($headerOfficeName)?></strong></div>
  <nav class="pr-nav">
    <a class="pr-nav-item" href="/immobilier/views/dashboard.php"><span>⌂</span><b><?=e(__t('navigation.dashboard','Dashboard'))?></b></a>
    <a class="pr-nav-item active" href="/immobilier/views/properties.php"><span>▣</span><b><?=e(__t('navigation.properties','Properties'))?></b></a>
    <?php if($auth->can('users.view')): ?><a class="pr-nav-item" href="/immobilier/admin/employees.php"><span>♙</span><b><?=e(__t('navigation.employees','Employees'))?></b></a><?php endif; ?>
    <?php if($auth->can('offices.view')): ?><a class="pr-nav-item" href="/immobilier/admin/offices.php"><span>⌂</span><b><?=e(__t('navigation.offices','Offices'))?></b></a><?php endif; ?>
    <a class="pr-nav-item" href="/immobilier/admin/settings.php"><span>⚙</span><b><?=e(__t('navigation.settings','Settings'))?></b></a>
  </nav>
  <div class="pr-sidebar-bottom"><a class="pr-nav-item" href="/immobilier/logout.php"><span>↪</span><b><?=e(__t('auth.sign_out','Sign out'))?></b></a></div>
</aside>
<main class="pr-main">
  <div class="wrap">
<?php
$heroPhoto=null; foreach($photos as $ph){if((int)$ph['is_primary']===1){$heroPhoto=$ph;break;}} if(!$heroPhoto && $photos){$heroPhoto=$photos[0];}
$typeLabel=''; foreach($types as $ty){if((int)$ty['id']===(int)($property['property_type_id']??0)){$typeLabel=dbI18nLabel((string)($ty['name_key'] ?? ''),(string)($ty['code'] ?? ''),(string)$ty['code']);break;}}
$completion=20; if($property){$completion=35; if(trim((string)($property['title']??''))!=='')$completion+=10; if(trim((string)($property['description']??''))!=='')$completion+=10; if($selected)$completion+=10; if($photos)$completion+=10; if($documents)$completion+=5; if((int)($property['is_published']??0)===1)$completion+=10;}
?>
<div class="property-hero">
 <?php if($heroPhoto): ?><img class="property-hero-photo" src="/immobilier/<?=e(ltrim($heroPhoto['file_path'],'/'))?>" alt=""><?php else: ?><div class="property-hero-photo property-hero-empty">⌂</div><?php endif; ?>
 <div class="property-hero-main">
   <div class="property-kicker"><a href="/immobilier/views/properties.php" class="property-breadcrumb">← <?=e(__t('navigation.properties','Properties'))?></a><span class="kicker-dot">·</span><span><?=e($property['reference']??__t('properties.new_property','New property'))?></span><?php if($typeLabel): ?><span class="kicker-dot">·</span><span><?=e($typeLabel)?></span><?php endif; ?><span class="kicker-dot">·</span><span><?=e(__t('properties.transaction.'.((string)($property['transaction_type']??'sale')),ucfirst((string)($property['transaction_type']??'sale'))))?></span></div>
   <h1><?=e($property ? ($property['title'] ?: $property['reference']) : 'New property')?></h1>
   <div class="property-price"><?= $property && $property['price']!==null ? '€ '.number_format((float)$property['price'],0,',','.') : '—' ?></div>
   <div class="property-facts">
    <span class="property-fact"><?=factIcon('living')?><span><strong><?=e($property['living_area_m2']??'—')?> m²</strong><small><?=e(__t('properties.fact.living','Living area'))?></small></span></span>
    <span class="property-fact"><?=factIcon('land')?><span><strong><?=e(formatLandHeader($property['land_area_m2']??null))?></strong><small><?=e(__t('properties.fact.land','Land'))?></small></span></span>
    <span class="property-fact"><?=factIcon('bedrooms')?><span><strong><?=e($property['bedrooms']??'—')?></strong><small><?=e(__t('properties.fact.bedrooms','Bedrooms'))?></small></span></span>
    <span class="property-fact"><?=factIcon('bathrooms')?><span><strong><?=e($property['bathrooms']??'—')?></strong><small><?=e(__t('properties.fact.bathrooms','Bathrooms'))?></small></span></span>
    <?php if($property && $property['city']): ?><span class="property-fact property-fact-location"><?=factIcon('location')?><span><strong><?=e($property['city'])?></strong><small><?=e(__t('properties.fact.location','Location'))?></small></span></span><?php endif; ?>
   </div>
 </div>
 <div class="property-status">
   <div class="property-badges"><span id="headerPublishedBadge" class="pbadge <?=((int)($property['is_published']??0)===1)?'published':''?>"><span class="status-dot"></span><span id="headerPublishedText"><?=e(__t(((int)($property['is_published']??0)===1)?'properties.publication.published_badge':'properties.publication.draft_badge',((int)($property['is_published']??0)===1)?'Published':'Draft'))?></span></span><span id="headerFeaturedBadge" class="pbadge <?=((int)($property['is_featured']??0)===1)?'featured':''?>"><span class="status-star">★</span><span id="headerFeaturedText"><?=e(__t(((int)($property['is_featured']??0)===1)?'properties.publication.featured_badge':'properties.publication.not_featured_badge',((int)($property['is_featured']??0)===1)?'Featured':'Not featured'))?></span></span></div>
   <div class="property-actions"><span id="autosaveStatus" class="autosave-status"><?=t('properties.all_changes_saved','All changes saved')?></span><button class="save" type="submit" form="propertyForm"><?=t('common.save_changes','Save changes')?></button></div>
 </div>
</div>
<div class="property-complete" id="completion"><div class="label"><?=e(__t('properties.completeness','Completeness'))?> <strong id="completionPercent"><?=e((string)$completion)?>%</strong></div><div class="bar"><span id="completionBar" style="width:<?=e((string)$completion)?>%"></span></div><div class="items"><span id="completeDetails">✓ <?=e(__t('properties.completeness.details','Details'))?></span><span id="completeDescription">✓ <?=e(__t('properties.completeness.description','Description'))?></span><span id="completeFeatures">✓ <?=e(__t('properties.completeness.features','Features'))?></span><span id="completePhotos">✓ <?=e(__t('properties.completeness.photos','Photos'))?></span><span id="completeDocuments"><?= $documents ? '✓ ' : '○ ' ?><?=e(__t('properties.completeness.documents','Documents'))?></span><span id="completePublish"><?= ((int)($property['is_published']??0)===1) ? '✓ ' : '○ ' ?><?=e(__t('properties.completeness.published','Published'))?></span></div></div>
<?php if($message):?><div class="notice"><?=e($message)?></div><?php endif;?><?php if($error):?><div class="error"><?=e($error)?></div><?php endif;?>
<div class="card property-shell"><form id="propertyForm" method="post" enctype="multipart/form-data"><input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=$id?>">
<div class="property-tabs" role="tablist" aria-label="Property sections">
<button type="button" class="property-tab active" data-tab="general" role="tab"><span class="tab-icon">⌂</span><?=t('properties.tab_general','General')?></button>
<button type="button" class="property-tab" data-tab="description" role="tab"><span class="tab-icon">✎</span><?=t('properties.tab_description','Description')?></button>
<button type="button" class="property-tab" data-tab="features" role="tab"><span class="tab-icon">✦</span><?=t('properties.tab_features','Features')?></button>
<button type="button" class="property-tab" data-tab="photos" role="tab"><span class="tab-icon">▧</span><?=t('properties.tab_photos','Photos')?><?php if($property && count($photos)): ?><span class="tab-badge" style="margin-left:6px"><?=count($photos)?></span><?php endif; ?></button>
<button type="button" class="property-tab" data-tab="videos" role="tab"><span class="tab-icon">▶</span><?=t('properties.tab_videos','Videos')?><?php if($property && count($videos)): ?><span class="tab-badge" style="margin-left:6px"><?=count($videos)?></span><?php endif; ?></button>
<button type="button" class="property-tab" data-tab="documents" role="tab"><span class="tab-icon">▤</span><?=t('properties.tab_documents','Documents')?><?php if($property && count($documents)): ?><span class="tab-badge" style="margin-left:6px"><?=count($documents)?></span><?php endif; ?></button>
<button type="button" class="property-tab" data-tab="location" role="tab"><span class="tab-icon">⌖</span><?=t('properties.tab_location','Location')?></button>
<button type="button" class="property-tab" data-tab="publication" role="tab"><span class="tab-icon">◉</span><?=t('properties.status_tab','Status')?></button>
<button type="button" class="property-tab" data-tab="mandate" role="tab"><span class="tab-icon">▤</span><?=t('properties.mandate_tab','Mandate')?></button>
<button type="button" class="property-tab" data-tab="energy" role="tab"><span class="tab-icon">ϟ</span><?=t('properties.energy_tab','Energy')?></button>
<button type="button" class="property-tab" data-tab="history" role="tab"><span class="tab-icon">↻</span><?=t('properties.history_tab','History')?></button>
</div>
<div class="tab-panel active" data-tab-panel="general">
 <div class="section-intro">Core property information, pricing and specifications.</div>
 <div class="general-layout">
  <section class="ux-card general-card">
   <div class="ux-card-head"><div><div class="eyebrow"><?=t('properties.property_kicker','Property')?></div><h2><?=t('properties.property_details','Property details')?></h2></div><span class="ux-card-icon">⌂</span></div>
   <div class="field-grid">
    <?php if($role==='super_admin'):?><div><label><?=t('properties.office','Office *')?></label><select name="office_id" id="office" required><option value=""><?=t('properties.select_office','Select office')?></option><?php foreach($offices as $o):?><option value="<?=$o['id']?>" <?=((int)($property['office_id']??0)==$o['id']?'selected':'')?>><?=e($o['name'])?></option><?php endforeach;?></select></div><?php else:?><input type="hidden" name="office_id" value="<?=e((string)$officeScope)?>"><?php endif;?>
    <div><label><?=t('properties.agent','Agent')?></label><select name="agent_id" id="agent"><option value=""><?=t('properties.unassigned','Unassigned')?></option><?php foreach($agents as $a):?><option value="<?=$a['id']?>" data-office="<?=$a['office_id']?>" <?=((int)($property['agent_id']??0)==$a['id']?'selected':'')?>><?=e($a['first_name'].' '.$a['last_name'])?></option><?php endforeach;?></select></div>
    <div><label><?=t('properties.property_type','Property type *')?></label><select name="property_type_id" required><option value=""><?=t('properties.select_type','Select type')?></option><?php foreach($types as $t):?><option value="<?=$t['id']?>" <?=((int)($property['property_type_id']??0)==$t['id']?'selected':'')?>><?=e(dbI18nLabel((string)($t['name_key'] ?? ''),(string)($t['code'] ?? ''),(string)$t['code']))?></option><?php endforeach;?></select></div>
    <div><label><?=t('properties.transaction','Transaction')?></label><select name="transaction_type" id="transactionType"><option value="sale" <?=($property['transaction_type']??'sale')==='sale'?'selected':''?>><?=t('properties.sale','Sale')?></option><option value="rent" <?=($property['transaction_type']??'')==='rent'?'selected':''?>><?=t('properties.rent','Rent')?></option></select></div>
    <div class="field-span-2"><label><?=t('properties.title','Title')?></label><input name="title" value="<?=val($property,'title')?>"></div>
   </div>
  </section>
  <section class="ux-card general-card">
   <div class="ux-card-head"><div><div class="eyebrow"><?=t('properties.value_kicker','Value')?></div><h2><?=t('properties.pricing_specs','Pricing & specifications')?></h2></div><span class="ux-card-icon">€</span></div>
   <div class="field-grid spec-grid">
    <div class="sale-only"><label><?=t('properties.total_sale_price','Total sale price incl. commission (€)')?></label><input type="number" step="0.01" min="0" name="price" id="saleTotalPrice" value="<?=val($property,'price')?>"></div>
    <div class="sale-only"><label><?=t('properties.commission_amount','Commission (€)')?></label><input type="number" step="0.01" min="0" name="sale_commission_amount" id="saleCommissionAmount" value="<?=val($property,'sale_commission_amount')?>"></div>
    <div class="sale-only"><label><?=t('properties.commission_percent','Commission (%)')?></label><input type="number" step="0.01" min="0" max="100" name="sale_commission_percent" id="saleCommissionPercent" value="<?=e(formatCommissionPercent($property['sale_commission_percent']??null,$property['sale_commission_amount']??null,$property['price']??null))?>"></div>
    <div class="rent-only" hidden><label><?=t('properties.monthly_rent','Monthly rent (€)')?></label><input type="number" step="0.01" min="0" name="rent_price" id="rentMonthly" value="<?=val($property,'price')?>"></div>
    <div class="rent-only" hidden><label><?=t('properties.rent_commission','Commission (€)')?></label><input type="number" step="0.01" min="0" name="rent_commission_amount" value="<?=val($property,'rent_commission_amount')?>"></div>
    <div class="rent-only" hidden><label><?=t('properties.deposit','Deposit (€)')?></label><input type="number" step="0.01" min="0" name="rent_deposit" value="<?=val($property,'rent_deposit')?>"></div>
    <div class="rent-only" hidden><label><?=t('properties.available_from','Available from')?></label><input type="date" name="rent_available_from" value="<?=val($property,'rent_available_from')?>"></div>
   </div>
   <div class="spec-hint"></div>
  </section>
 </div>
</div>
<div class="tab-panel" data-tab-panel="description"><div class="section-intro description-intro"><span class="source-pill"><?=t('properties.french_source','French source')?></span> <?=t('properties.french_source_note','Content is stored in French; translations can be generated later during publication.')?></div><div class="tab-panel-grid"><div class="span4"><label><?=t('properties.short_description','Short description (French)')?></label><div class="rich-editor" data-editor="short_description"><div class="rich-toolbar"><button type="button" data-cmd="bold"><strong>B</strong></button><button type="button" data-cmd="italic"><em>I</em></button><button type="button" data-cmd="insertUnorderedList">•</button><button type="button" data-cmd="insertOrderedList">1.</button><button type="button" data-block="h3">H3</button><button type="button" data-block="p">¶</button></div><div class="rich-content" contenteditable="true" role="textbox" aria-multiline="true"><?=richHtmlForEditor($property['short_description'] ?? '')?></div><textarea class="rich-source" name="short_description" hidden></textarea></div></div>
<div class="span4"><label><?=t('properties.description','Long description (French)')?></label><div class="rich-editor" data-editor="description"><div class="rich-toolbar"><button type="button" data-cmd="bold"><strong>B</strong></button><button type="button" data-cmd="italic"><em>I</em></button><button type="button" data-cmd="insertUnorderedList">•</button><button type="button" data-cmd="insertOrderedList">1.</button><button type="button" data-block="h2">H2</button><button type="button" data-block="h3">H3</button><button type="button" data-block="p">¶</button></div><div class="rich-content" contenteditable="true" role="textbox" aria-multiline="true"><?=richHtmlForEditor($property['description'] ?? '')?></div><textarea class="rich-source" name="description" hidden></textarea></div></div></div></div>
<div class="tab-panel" data-tab-panel="features">
 <div class="feature-edit-grid">
  <div><label><?=t('properties.bedrooms','Bedrooms')?></label><input type="number" min="0" name="bedrooms" value="<?=val($property,'bedrooms')?>"></div>
  <div><label><?=t('properties.bathrooms','Bathrooms')?></label><input type="number" min="0" step="1" name="bathrooms" value="<?=val($property,'bathrooms')?>"></div>
  <div><label><?=t('properties.living_area','Living area m²')?></label><input type="number" min="0" step="0.01" name="living_area_m2" value="<?=val($property,'living_area_m2')?>"></div>
  <div><label><?=t('properties.land_area','Land area m²')?></label><input type="number" min="0" step="0.01" name="land_area_m2" value="<?=val($property,'land_area_m2')?>"></div>
  <div><label><?=t('properties.year_built','Year built')?></label><input type="number" min="1000" max="2100" step="1" name="year_built" value="<?=val($property,'year_built')?>"></div>
 </div>
 <div class="section-intro">Precise property data stays in m². Large terrain areas are shown as hectares in the property header.</div><div class="feature-board">
<?php
$featureGroups=[];
foreach($features as $f){
    if(in_array($f['code'],$heatingCodes,true) || $f['code']==='underfloor_heating') continue;
    $featureGroups[$f['category'] ?: 'other'][]=$f;
}
foreach($featureGroups as $cat=>$items):
?>
<section class="feature-group">
  <div class="feature-group-head">
    <div><div class="eyebrow"><?=e(dbFeatureCategoryLabel((string)$cat))?></div><h3><?=e(dbFeatureCategoryLabel((string)$cat))?></h3></div>
    <span class="feature-count"><?=count($items)+($cat==='comfort'?1:0)?></span>
  </div>
  <div class="feature-tiles">
<?php foreach($items as $f):
    $isChecked=in_array((int)$f['id'],$selected,true);
    $label=dbI18nLabel((string)($f['name_key'] ?? ''),(string)($f['code'] ?? ''),(string)($f['code'] ?? ''));
    $isPool=$f['code']==='pool';
    $poolSummary='';
    if($isPool && $isChecked && trim((string)($property['pool_length_m']??''))!=='' && trim((string)($property['pool_width_m']??''))!==''){
        $poolSummary=trim((string)$property['pool_length_m']).' × '.trim((string)$property['pool_width_m']).' m';
        if(trim((string)($property['pool_depth_m']??''))!=='') $poolSummary.=' × '.trim((string)$property['pool_depth_m']).' m';
    }
?>
<label class="feature-tile <?=$isChecked?'selected':''?> <?=$isPool?'is-pool':''?>" data-feature-tile data-feature-code="<?=e($f['code'])?>" data-pool-summary="<?=e($poolSummary)?>">
  <input type="checkbox" name="features[]" value="<?=$f['id']?>" data-feature-code="<?=e($f['code'])?>" <?=$isChecked?'checked':''?>>
  <span class="feature-icon"><?=featureIcon((string)$f['code'],(string)$f['category'])?></span>
  <span class="feature-copy"><strong><?=e($label)?></strong><small><?= $isPool && $isChecked ? e($poolSummary) : ($isPool ? e(__t('properties.add_dimensions','Add dimensions')) : e(__t('properties.included','Included'))) ?></small></span>
  <span class="feature-check">✓</span>
</label>
<?php endforeach; ?>

<?php if($cat==='comfort'): ?>
<button type="button" class="feature-tile heating-tile <?=$heatingSelected?'selected':''?>" id="heatingTile" aria-haspopup="dialog" aria-controls="heatingModal">
  <span class="feature-icon">♨</span>
  <span class="feature-copy">
    <strong><?=e(__t('features.heating.title','Heating'))?></strong>
    <small id="heatingSummary"><?php
      if($heatingSelected){
        foreach($heatingFeatures as $hf){
          if((int)$hf['id']===$heatingSelected){ echo e(__t($hf['name_key'],$hf['code'])); break; }
        }
      } else {
        echo e(__t('features.heating.select','Select heating'));
      }
      if($underfloorSelected) echo ' · '.e(__t('features.underfloor_heating','Underfloor heating'));
    ?></small>
  </span>
  <span class="feature-check">›</span>
</button>
<?php endif; ?>
  </div>
</section>
<?php endforeach; ?>
</div>
<div class="feature-note"><?=t('properties.features_help','Gîte, annex and other amenities are stored through the existing features/property_features relationship.')?></div>
<input type="hidden" name="pool_length_m" id="pool_length_m" value="<?=val($property,'pool_length_m')?>"><input type="hidden" name="pool_width_m" id="pool_width_m" value="<?=val($property,'pool_width_m')?>"><input type="hidden" name="pool_depth_m" id="pool_depth_m" value="<?=val($property,'pool_depth_m')?>">
</div>
<div class="heating-modal" id="heatingModal" hidden>
<div class="heating-modal-backdrop" data-heating-close></div>
<div class="heating-dialog" role="dialog" aria-modal="true" aria-labelledby="heatingModalTitle">
<div class="heating-dialog-head"><div><div class="eyebrow">Comfort</div><h2 id="heatingModalTitle"><?=e(__t('features.heating.title','Heating'))?></h2></div><button type="button" class="heating-close" data-heating-close aria-label="Close">×</button></div>
<div class="heating-dialog-body">
<div class="heating-field"><label class="heating-field-label"><?=e(__t('features.heating.method','Heating method'))?></label><div class="heating-modal-options">
<?php foreach($heatingFeatures as $hf): ?><label class="heating-option"><input type="radio" name="heating_feature_id" value="<?=((int)$hf['id'])?>" <?=$heatingSelected===(int)$hf['id']?'checked':''?>><span class="heating-radio"></span><span><strong><?=e(__t($hf['name_key'],$hf['code']))?></strong></span></label><?php endforeach; ?>
</div></div>
<div class="heating-field"><label class="heating-field-label"><?=e(__t('features.underfloor_heating','Underfloor heating'))?></label><label class="heating-toggle"><input type="checkbox" id="underfloorHeating" name="underfloor_heating" value="1" <?=$underfloorSelected?'checked':''?>><span class="heating-toggle-track"></span><span id="underfloorHeatingText"><?=$underfloorSelected?'Yes':'No'?></span></label></div>
</div>
<div class="heating-dialog-foot"><button type="button" class="btn-secondary" data-heating-close>Cancel</button><button type="button" class="btn-primary" id="heatingApply">Apply</button></div>
</div></div>
<div class="heating-trigger-placeholder" hidden></div>
<div class="modal-backdrop" id="poolModal" hidden><div class="modal" role="dialog" aria-modal="true" aria-labelledby="poolModalTitle"><button type="button" class="modal-close" id="poolModalClose" aria-label="Close">×</button><div class="modal-icon">🏊</div><div class="eyebrow">Swimming pool</div><h2 id="poolModalTitle">Pool dimensions</h2><p>Enter the pool dimensions. Length and width are required; depth is optional. After saving, hover the Pool tile to see them at a glance.</p><div class="modal-fields"><label>Length *<div class="input-unit"><input id="poolModalLength" type="number" min="0.01" step="0.01"><span>m</span></div></label><label>Width *<div class="input-unit"><input id="poolModalWidth" type="number" min="0.01" step="0.01"><span>m</span></div></label><label>Depth <div class="input-unit"><input id="poolModalDepth" type="number" min="0" step="0.01"><span>m</span></div></label></div><div class="modal-actions"><button type="button" class="btn-secondary" id="poolModalCancel">Cancel</button><button type="button" class="btn-primary" id="poolModalSave">Save dimensions</button></div></div></div>
<div class="tab-panel" data-tab-panel="location"><div class="section-intro"><?=t('properties.location_intro','Property address and approximate public location.')?></div><div class="location-layout"><section class="ux-card"><div class="ux-card-head"><div><div class="eyebrow"><?=t('properties.address','Address')?></div><h2><?=t('properties.property_location','Property location')?></h2></div><span class="ux-card-icon">⌖</span></div><div class="field-grid"><div class="field-span-2"><label><?=t('properties.address','Address')?></label><input name="address_line_1" value="<?=val($property,'address_line_1')?>"></div><div><label><?=t('properties.address_line_2','Address line 2')?></label><input name="address_line_2" value="<?=val($property,'address_line_2')?>"></div><div><label><?=t('properties.postcode','Postcode')?></label><input name="postcode" id="locationPostcode" value="<?=val($property,'postcode')?>" inputmode="numeric" maxlength="5"></div><div><label><?=t('properties.city','City')?></label><input name="city" id="locationCity" value="<?=val($property,'city')?>"></div><div><label><?=t('properties.region','Region')?></label><input name="region" value="<?=val($property,'region')?>"></div></div><div class="location-note"><?=t('properties.location_note','If a postcode is entered, the map shows the geographic contour of that postcode area. No radius is used for a postcode. If there is no postcode, the city is shown with a 10 km radius. The exact property address is never displayed as a public marker.')?></div></section><section class="ux-card map-card"><div class="map-head"><div><div class="eyebrow"><?=t('properties.public_area','Public area')?></div><h2><?=t('properties.location_map','Location map')?></h2></div><span id="mapStatus" class="small"><?=t('properties.loading','Loading…')?></span></div><div id="propertyMap" class="property-map"><div class="map-placeholder">⌖</div></div></section></div></div>
<div class="tab-panel" data-tab-panel="mandate">
 <div class="section-intro"><?=t('properties.mandate_intro','Mandate details, legal ownership contact and mandate document.')?></div>
 <div class="tab-panel-grid">
  <section class="ux-card span2"><div class="ux-card-head"><div><div class="eyebrow"><?=t('properties.mandate','Mandate')?></div><h2><?=t('properties.mandate','Mandate')?></h2></div><span class="ux-card-icon">▤</span></div>
   <div class="tab-panel-grid">
    <div><label><?=t('properties.mandate_number','Mandate number')?></label><input name="mandate_number" value="<?=val($property,'mandate_number')?>"></div>
    <div><label><?=t('properties.mandate_type','Mandate type')?></label><select name="mandate_type"><option value=""><?=t('common.select','Select')?></option><?php foreach(['simple','exclusive','semi_exclusive'] as $mt):?><option value="<?=$mt?>" <?=($property['mandate_type']??'')===$mt?'selected':''?>><?=e(__t('properties.mandate_type.'.$mt,ucwords(str_replace('_','-',$mt))))?></option><?php endforeach;?></select></div>
    <div><label><?=t('properties.mandate_start','Start date')?></label><input type="date" name="mandate_start_date" value="<?=val($property,'mandate_start_date')?>"></div>
    <div><label><?=t('properties.mandate_end','End date')?></label><input type="date" name="mandate_end_date" value="<?=val($property,'mandate_end_date')?>"></div>
   </div>
  </section>
  <section class="ux-card span2"><div class="ux-card-head"><div><div class="eyebrow"><?=t('properties.seller','Seller / Mandate contact')?></div><h2><?=t('properties.contact','Contact')?></h2></div><span class="ux-card-icon">⌁</span></div>
   <div class="tab-panel-grid">
    <div class="span2"><label><?=t('common.name','Name')?></label><input name="mandate_party_name" value="<?=val($property,'mandate_party_name')?>"></div>
    <div><label><?=t('common.email','Email')?></label><input type="email" name="mandate_party_email" value="<?=val($property,'mandate_party_email')?>"></div>
    <div><label><?=t('common.phone','Phone')?></label><input name="mandate_party_phone" value="<?=val($property,'mandate_party_phone')?>"></div>
   </div>
  </section>
  <section class="ux-card span4"><div class="ux-card-head"><div><div class="eyebrow"><?=t('properties.document','Document')?></div><h2><?=t('properties.mandate_attachment','Mandate attachment')?></h2></div><span class="ux-card-icon">⌑</span></div>
   <input type="file" name="mandate_attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
   <?php if(!empty($property['mandate_attachment_path'])):?><div class="small" style="margin-top:8px"><a href="/immobilier/<?=e(ltrim($property['mandate_attachment_path'],'/'))?>" target="_blank" rel="noopener"><?=t('properties.current_mandate','Current mandate attachment')?></a></div><?php endif;?>
  </section>
 </div>
</div>
<div class="tab-panel" data-tab-panel="energy">
 <div class="section-intro"><?=t('properties.energy_coming','Energy information will be added here.')?></div>
 <section class="ux-card">
  <div class="ux-card-head"><div><div class="eyebrow"><?=t('properties.energy_tab','Energy')?></div><h2><?=t('properties.energy_tab','Energy')?></h2></div><span class="ux-card-icon">ϟ</span></div>
  <div class="energy-label-grid">
   <div class="energy-label-card">
    <div class="energy-label-head"><div><div class="eyebrow"><?=t('properties.energy_label','Energy label')?></div><h3>DPE</h3></div>
     <select name="energy_label" id="energyLabel"><option value="">—</option><?php foreach(['a','b','c','d','e','f','g'] as $label): ?><option value="<?=$label?>" <?=($property['energy_label']??'')===$label?'selected':''?>><?=strtoupper($label)?></option><?php endforeach; ?></select>
    </div>
    <div class="energy-label-image"><img id="energyLabelImage" alt="DPE" <?=empty($property['energy_label'])?'hidden':''?> src="<?=!empty($property['energy_label'])?'/immobilier/media/dpe/dpe-'.e(strtolower($property['energy_label'])).'.png':''?>"></div>
   </div>
   <div class="energy-label-card">
    <div class="energy-label-head"><div><div class="eyebrow"><?=t('properties.co2_label','CO₂ emission label')?></div><h3>GES</h3></div>
     <select name="co2_label" id="co2Label"><option value="">—</option><?php foreach(['a','b','c','d','e','f','g'] as $label): ?><option value="<?=$label?>" <?=($property['co2_label']??'')===$label?'selected':''?>><?=strtoupper($label)?></option><?php endforeach; ?></select>
    </div>
    <div class="energy-label-image"><img id="co2LabelImage" alt="GES" <?=empty($property['co2_label'])?'hidden':''?> src="<?=!empty($property['co2_label'])?'/immobilier/media/dpe/ges-'.e(strtolower($property['co2_label'])).'.png':''?>"></div>
   </div>
  </div>
 </section>
</div>
<div class="tab-panel" data-tab-panel="history">
 <div class="section-intro"><?=t('properties.history_coming','Property history will be added here.')?></div>
 <section class="ux-card"><div class="ux-card-head"><div><div class="eyebrow"><?=t('properties.history_tab','History')?></div><h2><?=t('properties.history_tab','History')?></h2></div><span class="ux-card-icon">↻</span></div><div class="empty"><?=t('properties.history_placeholder','Price, status and other changes will be recorded here later.')?></div></section>
</div>
<div class="tab-panel" data-tab-panel="publication">
 <div class="section-intro"><?=t('properties.status_publication_intro','Manage publication and property status from one place.')?></div>
 <div class="publication-layout">
  <section class="ux-card publication-card">
   <div class="ux-card-head"><div><div class="eyebrow"><?=t('properties.publishing','Publishing')?></div><h2><?=t('properties.visibility_promotion','Visibility & promotion')?></h2></div><span class="ux-card-icon">◉</span></div>
   <div class="publication-choice"><div><strong><?=t('properties.published','Published')?></strong><small><?=t('properties.published_help','Make this property available to your public channels.')?></small></div><label class="switch"><input type="checkbox" name="is_published" value="1" <?=((int)($property['is_published']??0)===1?'checked':'')?>><span></span></label></div>
   <div class="publication-choice"><div><strong><?=t('properties.featured_property','Featured property')?></strong><small><?=t('properties.featured_help','Highlight this property in featured placements.')?></small></div><label class="switch"><input type="checkbox" name="is_featured" value="1" <?=((int)($property['is_featured']??0)===1?'checked':'')?>><span></span></label></div>
  </section>
  <section class="ux-card status-card">
   <div class="ux-card-head"><div><div class="eyebrow"><?=t('properties.status','Status')?></div><h2><?=t('properties.status','Status')?></h2></div><span class="ux-card-icon">◉</span></div>
   <label><?=t('properties.status','Status')?><select name="status" id="propertyStatus"><?php foreach(['draft','active','sold','rented','withdrawn'] as $x):?><option value="<?=$x?>" <?=($property['status']??'draft')===$x?'selected':''?>><?=e(__t('properties.status.'.$x,ucfirst($x)))?></option><?php endforeach;?></select></label>
   <div class="sold-result-summary" id="soldResultSummary" <?=($property['status']??'draft')==='sold'?'':'hidden'?>>
    <div class="sold-summary-head"><div><div class="eyebrow"><?=t('properties.sold','Sold')?></div><h3><?=t('properties.sold_result','Sale result')?></h3></div><button type="button" class="light sold-edit-button" id="openSoldDetails"><?=t('common.edit','Edit')?></button></div>
    <div class="sold-summary-grid">
      <div><small><?=t('properties.sold_amount','Sold amount (€)')?></small><strong id="soldSummaryAmount"><?=val($property,'sold_amount')?:'—'?></strong></div>
      <div><small><?=t('properties.commission_amount','Total commission (€)')?></small><strong id="soldSummaryCommission"><?=val($property,'sold_commission_amount')?:'—'?></strong></div>
      <div><small><?=t('properties.commission_percent','Commission (%)')?></small><strong id="soldSummaryPercent"><?=e(formatCommissionPercent($property['sold_commission_percent']??null,$property['sold_commission_amount']??null,$property['sold_amount']??null) ?: '—')?></strong></div>
      <div><small><?=t('properties.sold_date','Sold date')?></small><strong id="soldSummaryDate"><?=val($property,'sold_date')?:'—'?></strong></div>
      <div class="sold-summary-person"><small><?=t('properties.sold_to_name','Sold to — name')?></small><strong id="soldSummaryName"><?=val($property,'sold_to_name')?:'—'?></strong><span id="soldSummaryContact"><?php $soldContact=trim((string)($property['sold_to_email']??'')); if(!empty($property['sold_to_phone'])) $soldContact.=($soldContact?' · ':'').$property['sold_to_phone']; echo e($soldContact?:'—');?></span></div>
    </div>
   </div>
   <div class="status-helper" id="statusHelper"></div>
  </section>
 </div>
</div>

<div class="sold-modal" id="soldModal" hidden>
 <div class="sold-modal-backdrop" data-sold-close></div>
 <div class="sold-dialog" role="dialog" aria-modal="true" aria-labelledby="soldModalTitle">
  <div class="sold-dialog-head"><div><div class="eyebrow"><?=t('properties.sold','Sold')?></div><h2 id="soldModalTitle"><?=t('properties.sold_result','Sale result')?></h2></div><button type="button" class="sold-close" data-sold-close aria-label="<?=e(__t('common.close','Close'))?>">×</button></div>
  <div class="sold-dialog-body">
   <div class="tab-panel-grid">
    <div><label><?=t('properties.sold_amount','Sold amount (€)')?></label><input type="number" step="0.01" min="0" name="sold_amount" id="soldAmount" value="<?=val($property,'sold_amount')?>"></div>
    <div><label><?=t('properties.commission_amount','Total commission (€)')?></label><input type="number" step="0.01" min="0" name="sold_commission_amount" id="soldCommissionAmount" value="<?=val($property,'sold_commission_amount')?>"></div>
    <div><label><?=t('properties.commission_percent','Commission (%)')?></label><input type="number" step="0.01" min="0" max="100" name="sold_commission_percent" id="soldCommissionPercent" value="<?=e(formatCommissionPercent($property['sold_commission_percent']??null,$property['sold_commission_amount']??null,$property['sold_amount']??null))?>"></div>
    <div><label><?=t('properties.sold_date','Sold date')?></label><input type="date" name="sold_date" id="soldDate" value="<?=val($property,'sold_date')?>"></div>
    <div class="span2"><label><?=t('properties.sold_to_name','Sold to — name')?></label><input name="sold_to_name" id="soldToName" value="<?=val($property,'sold_to_name')?>"></div>
    <div><label><?=t('properties.sold_to_email','Sold to — email')?></label><input type="email" name="sold_to_email" id="soldToEmail" value="<?=val($property,'sold_to_email')?>"></div>
    <div><label><?=t('properties.sold_to_phone','Sold to — phone')?></label><input name="sold_to_phone" id="soldToPhone" value="<?=val($property,'sold_to_phone')?>"></div>
   </div>
  </div>
  <div class="sold-dialog-foot"><button type="button" class="btn-secondary" data-sold-close><?=t('common.cancel','Cancel')?></button><button type="button" class="btn-primary" id="soldDetailsDone"><?=t('common.save','Save details')?></button></div>
 </div>
</div>
</div>
</form></div>
<?php if($property):?>
<div class="card media-card" id="photos" data-media-tab="photos">
<h2><?=t('properties.photos','Photos')?></h2>
<p class="small"><?=t('properties.photos_help','Drag photos into the upload area to add them. Drag existing photos to change their order. The first/primary photo is used as the main property image.')?></p>
<form id="photoUploadForm" method="post" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?=e($csrf)?>">
<input type="hidden" name="action" value="upload">
<input type="hidden" name="id" value="<?=$id?>">
<div id="dropzone" class="dropzone" tabindex="0" role="button" aria-label="Add photos">
<div class="drop-title"><?=t('properties.drop_photos','Drop photos here')?></div><div class="small"><?=t('properties.choose_photos','or click to choose photos · JPG, PNG or WebP · max 12 MB each')?></div>
<input id="photoInput" type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple>
</div>
<div id="uploadPreview" class="upload-preview"></div>
<div class="actions"><button id="uploadButton" type="submit" disabled><?=t('properties.upload_photos','Upload photos')?></button></div>
</form>
<?php if($photos):?>
<div class="photo-grid" id="photoGrid" aria-label="Property photos">
<?php foreach($photos as $m):?>
<div class="photo" draggable="true" data-media-id="<?= (int)$m['id'] ?>">
<div class="drag-handle" title="<?=t('properties.drag_to_reorder','Drag to reorder')?>">☷</div>
<img src="/immobilier/<?=e(ltrim($m['file_path'],'/'))?>" alt="<?=e($m['alt_text']??'')?>">
<div class="small"><?=e($m['original_filename']??'Photo')?> · <?=$m['width']??'?'?>×<?=$m['height']??'?'?></div>
<div class="actions">
<form method="post"><input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" value="primary"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="media_id" value="<?=$m['id']?>"><button type="submit" class="<?=((int)$m['is_primary']===1?'':'light')?>"><?=((int)$m['is_primary']===1?'★ '.e(__t('properties.primary','Primary')):e(__t('properties.set_primary','Set primary')))?></button></form>
<form method="post" onsubmit="return confirm('<?=e(__t('properties.delete_confirm','Delete this photo permanently?'))?>')"><input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" value="video_delete"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="media_id" value="<?=$m['id']?>"><button type="submit" class="danger"><?=t('properties.delete','Delete')?></button></form>
</div></div>
<?php endforeach;?>
</div>
<?php else:?><div class="empty"><?=t('properties.no_photos','No photos yet.')?></div><?php endif;?>
</div>

<div class="card media-card" id="videos" data-media-tab="videos">
<h2><?=t('videos.title','Videos')?></h2>
<p class="small"><?=t('videos.help','Upload property videos or add a YouTube/Vimeo link. Uploaded videos are stored with the property.')?></p>
<form id="videoUploadForm" method="post" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" value="video_upload"><input type="hidden" name="id" value="<?=$id?>">
<div id="videoDropzone" class="dropzone" tabindex="0" role="button"><div class="drop-title"><?=t('videos.drop','Drop videos here')?></div><div class="small"><?=t('videos.choose','or click to choose · MP4, WebM, OGG or MOV · max 250 MB each')?></div><input id="videoInput" type="file" name="videos[]" accept="video/mp4,video/webm,video/ogg,video/quicktime" multiple></div>
<div id="videoPreview" class="upload-preview"></div><div class="actions"><button id="videoUploadButton" type="submit" disabled><?=t('videos.upload','Upload videos')?></button></div>
</form>
<div class="video-external">
<form method="post"><input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" value="video_external"><input type="hidden" name="id" value="<?=$id?>">
<div class="grid"><div class="span2"><label><?=t('videos.url','YouTube / Vimeo URL')?></label><input type="url" name="video_url" placeholder="https://www.youtube.com/..." required></div><div class="span2"><label><?=t('videos.title_field','Video title')?></label><input name="video_title" placeholder="<?=e(__t('videos.title_placeholder','Property presentation'))?>"></div></div><div class="actions"><button type="submit" class="light"><?=t('videos.add_link','Add video link')?></button></div></form>
</div>
<?php if($videos):?>
<div class="video-grid" id="videoGrid">
<?php foreach($videos as $v): $isUrl=filter_var((string)$v['file_path'],FILTER_VALIDATE_URL)!==false; $vTitle=(string)($v['original_filename']??'Video'); ?>
<div class="video-card" draggable="true" data-media-id="<?=((int)$v['id'])?>"><div class="video-drag-handle" title="<?=t('videos.drag_to_reorder','Drag to reorder')?>">☷</div>
<div class="video-preview">
<?php if($isUrl): ?><div class="external-video"><span>▶</span><strong><?=e($vTitle)?></strong><a href="<?=e($v['file_path'])?>" target="_blank" rel="noopener"><?=t('videos.open','Open video')?></a></div>
<?php else: ?><video controls preload="metadata" src="/immobilier/<?=e(ltrim($v['file_path'],'/'))?>"></video><?php endif; ?>
</div>
<div class="video-meta"><span><?=e(strtoupper(pathinfo($v['file_path'],PATHINFO_EXTENSION)?:'link'))?></span></div>
<form class="video-edit" method="post"><input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" value="video_update"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="media_id" value="<?=((int)$v['id'])?>"><input name="video_title" value="<?=e($vTitle)?>" aria-label="<?=t('videos.title_field','Video title')?>"><button type="submit" class="light"><?=t('documents.save','Save')?></button></form>
<form method="post" onsubmit="return confirm('<?=e(__t('videos.delete_confirm','Delete this video permanently?'))?>')"><input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" value="photo_delete"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="media_id" value="<?=((int)$v['id'])?>"><button type="submit" class="danger"><?=t('properties.delete','Delete')?></button></form>
</div>
<?php endforeach; ?>
</div>
<?php else: ?><div class="empty"><?=t('videos.none','No videos yet.')?></div><?php endif; ?>
</div>

<div class="card media-card" id="documents" data-media-tab="documents">
<h2><?=t('documents.title','Documents')?></h2>
<p class="small"><?=t('documents.help','Store contracts, diagnostics, technical files and other property documents. Drag and drop files to upload.')?></p>
<form id="documentUploadForm" method="post" enctype="multipart/form-data">
<input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" value="document_upload"><input type="hidden" name="id" value="<?=$id?>">
<div id="documentDropzone" class="dropzone" tabindex="0" role="button"><div class="drop-title"><?=t('documents.drop','Drop documents here')?></div><div class="small"><?=t('documents.choose','or click to choose · PDF, Word, Excel, PowerPoint, CSV, TXT, ODT/ODS · max 25 MB each')?></div><input id="documentInput" type="file" name="documents[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.rtf,.odt,.ods,.ppt,.pptx"></div><div id="documentPreview" class="upload-preview"></div><div class="actions"><button id="documentUploadButton" type="submit" disabled><?=t('documents.upload','Upload documents')?></button></div>
</form>
<div class="document-filter"><label style="margin:0"><?=t('documents.filter','Filter')?></label><select id="documentCategoryFilter"><option value="all"><?=t('documents.all_categories','All categories')?></option><?php foreach(['administrative','legal','contracts','diagnostics','energy','technical','financial','ownership','marketing','other'] as $cat):?><option value="<?=e($cat)?>"><?=e(__t('documents.category.'.$cat,ucfirst($cat)))?></option><?php endforeach;?></select></div>
<div class="document-grid" id="documentGrid">
<?php foreach($documents as $d): $ext=strtolower(pathinfo((string)$d['original_filename'],PATHINFO_EXTENSION)); $icon=in_array($ext,['pdf'])?'📕':(in_array($ext,['doc','docx','odt','rtf'])?'📘':(in_array($ext,['xls','xlsx','csv','ods'])?'📗':(in_array($ext,['ppt','pptx'])?'📙':'📄'))); $size=((int)$d['file_size']>=1048576?number_format((int)$d['file_size']/1048576,1).' MB':number_format((int)$d['file_size']/1024,0).' KB'); ?>
<div class="document-row" draggable="true" data-document-id="<?=((int)$d['id'])?>" data-category="<?=e($d['category'])?>"><div class="doc-icon" title="<?=e($d['mime_type']??$ext)?>"><?=$icon?></div><div><a href="/immobilier/admin/property-document-download.php?id=<?=(int)$d['id']?>" target="_blank" rel="noopener"><?=e($d['original_filename']??'Document')?></a><div class="doc-meta"><?=e($ext?:'file')?> · <?=e($size)?></div></div><form class="doc-category" method="post"><input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" value="document_update"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="document_id" value="<?=((int)$d['id'])?>"><input name="document_title" value="<?=e($d['title'])?>" aria-label="<?=t('documents.title_field','Document title')?>"><select name="category" aria-label="<?=t('documents.category_field','Category')?>"><?php foreach(['administrative','legal','contracts','diagnostics','energy','technical','financial','ownership','marketing','other'] as $cat):?><option value="<?=e($cat)?>" <?=$d['category']===$cat?'selected':''?>><?=e(__t('documents.category.'.$cat,ucfirst($cat)))?></option><?php endforeach;?></select><button type="submit" class="light"><?=t('documents.save','Save')?></button></form><div class="doc-meta"><?=e($d['title'])?></div><div class="doc-actions"><form method="post" onsubmit="return confirm('<?=e(__t('documents.delete_confirm','Delete this document permanently?'))?>')"><input type="hidden" name="_csrf" value="<?=e($csrf)?>"><input type="hidden" name="action" value="document_delete"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="document_id" value="<?=((int)$d['id'])?>"><button type="submit" class="danger"><?=t('properties.delete','Delete')?></button></form></div></div>
<?php endforeach;?>
</div>
</div>
<?php endif;?></div><script>
function setupRichEditor(root){
  const content=root.querySelector('.rich-content'), source=root.querySelector('.rich-source');
  if(!content||!source)return;
  const sync=()=>{ source.value=content.innerHTML; };
  root.querySelectorAll('[data-cmd]').forEach(btn=>btn.addEventListener('mousedown',e=>{e.preventDefault();content.focus();document.execCommand(btn.dataset.cmd,false,null);sync();}));
  root.querySelectorAll('[data-block]').forEach(btn=>btn.addEventListener('mousedown',e=>{e.preventDefault();content.focus();document.execCommand('formatBlock',false,btn.dataset.block);sync();}));
  content.addEventListener('input',sync);
  content.closest('form')?.addEventListener('submit',sync);
  sync();
}

(function(){
  const tx=document.getElementById('transactionType');
  const saleFields=[...document.querySelectorAll('.sale-only')];
  const rentFields=[...document.querySelectorAll('.rent-only')];
  const toggleTransaction=()=>{const sale=tx?.value!=='rent';saleFields.forEach(x=>x.hidden=!sale);rentFields.forEach(x=>x.hidden=sale);};
  tx?.addEventListener('change',toggleTransaction); toggleTransaction();
  const total=document.getElementById('saleTotalPrice'), ca=document.getElementById('saleCommissionAmount'), cp=document.getElementById('saleCommissionPercent');
  let commissionLock=false;
  const syncSaleFromPercent=()=>{if(commissionLock||!total||!cp||!ca)return;const t=parseFloat(total.value),p=parseFloat(cp.value);if(Number.isFinite(t)&&Number.isFinite(p)){commissionLock=true;ca.value=(t*p/100).toFixed(2);commissionLock=false;}};
  const syncSaleFromAmount=()=>{if(commissionLock||!total||!cp||!ca)return;const t=parseFloat(total.value),a=parseFloat(ca.value);if(Number.isFinite(t)&&t>0&&Number.isFinite(a)){commissionLock=true;cp.value=(a/t*100).toFixed(2);commissionLock=false;}};
  cp?.addEventListener('input',syncSaleFromPercent); ca?.addEventListener('input',syncSaleFromAmount);
  total?.addEventListener('input',()=>{if(cp?.value)syncSaleFromPercent();else if(ca?.value)syncSaleFromAmount();});
  const sold=document.getElementById('soldAmount'), sca=document.getElementById('soldCommissionAmount'), scp=document.getElementById('soldCommissionPercent');
  const syncSoldP=()=>{const t=parseFloat(sold?.value),p=parseFloat(scp?.value);if(Number.isFinite(t)&&Number.isFinite(p)&&sca)sca.value=(t*p/100).toFixed(2);};
  const syncSoldA=()=>{const t=parseFloat(sold?.value),a=parseFloat(sca?.value);if(Number.isFinite(t)&&t>0&&Number.isFinite(a)&&scp)scp.value=(a/t*100).toFixed(2);};
  scp?.addEventListener('input',syncSoldP); sca?.addEventListener('input',syncSoldA); sold?.addEventListener('input',()=>{if(scp?.value)syncSoldP();else if(sca?.value)syncSoldA();});
  const status=document.getElementById('propertyStatus'), soldSummary=document.getElementById('soldResultSummary'), soldModal=document.getElementById('soldModal');
  const openSold=()=>{if(!soldModal)return;soldModal.hidden=false;document.body.classList.add('modal-open');setTimeout(()=>sold?.focus(),30);};
  const closeSold=()=>{if(!soldModal)return;soldModal.hidden=true;document.body.classList.remove('modal-open');updateSoldSummary();};
  const updateSoldSummary=()=>{
    if(!soldSummary)return;
    const isSold=status?.value==='sold'; soldSummary.hidden=!isSold;
    const set=(id,v,suffix='')=>{const el=document.getElementById(id);if(el)el.textContent=(v!==undefined&&v!==null&&String(v).trim()!=='')?String(v)+suffix:'—';};
    set('soldSummaryAmount',sold?.value);set('soldSummaryCommission',sca?.value);set('soldSummaryPercent',scp?.value,'%');set('soldSummaryDate',document.getElementById('soldDate')?.value);set('soldSummaryName',document.getElementById('soldToName')?.value);
    const contact=[document.getElementById('soldToEmail')?.value,document.getElementById('soldToPhone')?.value].filter(v=>v&&String(v).trim()).join(' · ');const c=document.getElementById('soldSummaryContact');if(c)c.textContent=contact||'—';
  };
  status?.addEventListener('change',()=>{if(status.value==='sold'){updateSoldSummary();openSold();}else{updateSoldSummary();}});
  document.getElementById('openSoldDetails')?.addEventListener('click',openSold);
  document.getElementById('soldDetailsDone')?.addEventListener('click',()=>{updateSoldSummary();closeSold();});
  soldModal?.querySelectorAll('[data-sold-close]').forEach(x=>x.addEventListener('click',closeSold));
  document.addEventListener('keydown',e=>{if(e.key==='Escape'&&soldModal&&!soldModal.hidden)closeSold();});
  [sold,sca,scp,document.getElementById('soldDate'),document.getElementById('soldToName'),document.getElementById('soldToEmail'),document.getElementById('soldToPhone')].forEach(el=>el?.addEventListener('input',updateSoldSummary));
  updateSoldSummary();
  if(status?.value==='sold' && !sold?.value && !document.getElementById('soldToName')?.value && !document.getElementById('soldDate')?.value){setTimeout(openSold,180);}
})();

(function(){
  const tabs=[...document.querySelectorAll('.property-tab')];
  const panels=[...document.querySelectorAll('.tab-panel')];
  const mediaPanels={photos:document.getElementById('photos'),videos:document.getElementById('videos'),documents:document.getElementById('documents')};
  const activate=(name,replace=true)=>{
    tabs.forEach(t=>{const on=t.dataset.tab===name;t.classList.toggle('active',on);t.setAttribute('aria-selected',on?'true':'false');});
    panels.forEach(p=>p.classList.toggle('active',p.dataset.tabPanel===name));
    Object.entries(mediaPanels).forEach(([key,el])=>{if(el)el.style.display=key===name?'block':'none';});
    if(replace){try{history.replaceState(null,'','#'+name);}catch(e){}}
    window.scrollTo({top:0,behavior:'smooth'});
  };
  tabs.forEach(t=>t.addEventListener('click',()=>activate(t.dataset.tab)));
  const initial=(location.hash||'').replace('#','');
  activate(tabs.some(t=>t.dataset.tab===initial)?initial:'general',false);
})();
document.querySelectorAll('.rich-editor').forEach(setupRichEditor);

const csrf='<?=e($csrf)?>';
const propertyId='<?=$id?>';

function setupFileDropzone(zoneId,inputId,previewId,buttonId,allowed,maxBytes,kind){
  const zone=document.getElementById(zoneId),input=document.getElementById(inputId),preview=document.getElementById(previewId),button=document.getElementById(buttonId);
  if(!zone||!input||!preview||!button)return;
  let files=[];
  const valid=f=>{if(!f||f.size>maxBytes)return false; if(allowed.includes(f.type))return true; const n=(f.name||'').toLowerCase(); return kind==='photo' && /\.(jpe?g|png|webp)$/i.test(n);};
  const sync=()=>{try{const dt=new DataTransfer();files.forEach(f=>dt.items.add(f));input.files=dt.files;}catch(e){}};
  const render=()=>{preview.innerHTML='';files.forEach(f=>{const box=document.createElement('div');box.className='preview';if(kind==='photo'){const img=document.createElement('img');img.src=URL.createObjectURL(f);img.alt=f.name;box.appendChild(img);}else{const icon=document.createElement('div');icon.style.fontSize='34px';icon.textContent='🎬';box.appendChild(icon);}const label=document.createElement('div');label.className='small';label.textContent=f.name;box.appendChild(label);preview.appendChild(box);});button.disabled=!files.length;};
  const add=list=>{Array.from(list||[]).filter(valid).forEach(f=>{if(!files.some(x=>x.name===f.name&&x.size===f.size&&x.lastModified===f.lastModified))files.push(f);});sync();render();};
  const open=()=>input.click();
  zone.addEventListener('click',e=>{if(e.target!==input){e.preventDefault();open();}});
  zone.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();open();}});
  input.addEventListener('change',()=>add(input.files));
  ['dragenter','dragover'].forEach(ev=>zone.addEventListener(ev,e=>{e.preventDefault();e.stopPropagation();zone.classList.add('dragover');if(e.dataTransfer)e.dataTransfer.dropEffect='copy';}));
  ['dragleave','drop'].forEach(ev=>zone.addEventListener(ev,e=>{e.preventDefault();e.stopPropagation();if(ev==='drop')add(e.dataTransfer.files);zone.classList.remove('dragover');}));
}

setupFileDropzone('dropzone','photoInput','uploadPreview','uploadButton',['image/jpeg','image/png','image/webp'],12*1024*1024,'photo');
setupFileDropzone('videoDropzone','videoInput','videoPreview','videoUploadButton',['video/mp4','video/webm','video/ogg','video/quicktime'],250*1024*1024,'video');
setupFileDropzone('documentDropzone','documentInput','documentPreview','documentUploadButton',['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','text/csv','text/plain','application/rtf','application/vnd.oasis.opendocument.text','application/vnd.oasis.opendocument.spreadsheet','application/vnd.ms-powerpoint','application/vnd.openxmlformats-officedocument.presentationml.presentation'],25*1024*1024,'document');

const office=document.getElementById('office'),agent=document.getElementById('agent');
if(office&&agent){const opts=[...agent.options];const filterAgents=()=>opts.forEach(o=>{if(!o.value)return;o.hidden=o.dataset.office!==office.value;if(o.hidden&&o.selected)agent.value='';});office.addEventListener('change',filterAgents);filterAgents();}

const poolBox=document.querySelector('input[data-feature-code="pool"]'),poolModal=document.getElementById('poolModal');
const poolLen=document.getElementById('pool_length_m'),poolWid=document.getElementById('pool_width_m'),poolDep=document.getElementById('pool_depth_m');
const poolModalLen=document.getElementById('poolModalLength'),poolModalWid=document.getElementById('poolModalWidth'),poolModalDep=document.getElementById('poolModalDepth'),poolTile=document.querySelector('[data-feature-tile][data-feature-code="pool"]');

const openPoolModal=()=>{if(!poolModal)return;poolModalLen.value=poolLen?.value||'';poolModalWid.value=poolWid?.value||'';poolModalDep.value=poolDep?.value||'';poolModal.hidden=false;document.body.classList.add('modal-open');setTimeout(()=>poolModalLen?.focus(),30);};
const closePoolModal=()=>{if(!poolModal)return;poolModal.hidden=true;document.body.classList.remove('modal-open');};
const updatePoolTile=()=>{if(!poolTile)return;const on=!!poolBox?.checked;poolTile.classList.toggle('selected',on);let summary='';if(on&&poolLen?.value&&poolWid?.value){summary=`${poolLen.value} × ${poolWid.value} m`;if(poolDep?.value)summary+=` × ${poolDep.value} m`;}poolTile.dataset.poolSummary=summary;poolTile.title=summary?`Pool dimensions: ${summary}`:ImmoI18n.add_pool_dimensions;const small=poolTile.querySelector('.feature-copy small');if(small)small.textContent=summary||ImmoI18n.add_dimensions;};
if(poolBox){poolBox.addEventListener('change',()=>{if(poolBox.checked)openPoolModal();else{poolLen.value='';poolWid.value='';poolDep.value='';updatePoolTile();}});updatePoolTile();}
poolTile?.addEventListener('click',e=>{if(e.target===poolBox)return;if(poolBox.checked)openPoolModal();else{poolBox.checked=true;openPoolModal();}});
document.getElementById('poolModalSave')?.addEventListener('click',()=>{if(!poolModalLen.value||!poolModalWid.value){alert(ImmoI18n.enter_pool_size);return;}poolLen.value=poolModalLen.value;poolWid.value=poolModalWid.value;poolDep.value=poolModalDep.value;poolBox.checked=true;updatePoolTile();poolLen.dispatchEvent(new Event('change',{bubbles:true}));poolWid.dispatchEvent(new Event('change',{bubbles:true}));poolDep.dispatchEvent(new Event('change',{bubbles:true}));closePoolModal();});
const cancelPool=()=>{if(!poolLen.value&&!poolWid.value&&!poolDep.value)poolBox.checked=false;closePoolModal();updatePoolTile();};
document.getElementById('poolModalCancel')?.addEventListener('click',cancelPool);document.getElementById('poolModalClose')?.addEventListener('click',cancelPool);poolModal?.addEventListener('click',e=>{if(e.target===poolModal)cancelPool();});
const photoGrid=document.getElementById('photoGrid');let draggedPhoto=null;
if(photoGrid){
  photoGrid.querySelectorAll('.photo').forEach(card=>{
    // Make the whole card draggable for reliable Safari/Chrome/Firefox behaviour.
    // The drag handle remains the visual cue; interactive controls are excluded.
    card.setAttribute('draggable','true');
    card.addEventListener('dragstart',e=>{
      if(e.target.closest('button,form,input,select,a')){e.preventDefault();return;}
      draggedPhoto=card;
      card.classList.add('dragging');
      if(e.dataTransfer){e.dataTransfer.effectAllowed='move';e.dataTransfer.setData('text/plain',card.dataset.mediaId||'');}
    });
    card.addEventListener('dragend',()=>{
      card.classList.remove('dragging');
      photoGrid.querySelectorAll('.photo').forEach(x=>x.classList.remove('drag-over'));
      draggedPhoto=null;
    });
    card.addEventListener('dragover',e=>{
      if(!draggedPhoto||draggedPhoto===card)return;
      e.preventDefault();
      if(e.dataTransfer)e.dataTransfer.dropEffect='move';
      card.classList.add('drag-over');
    });
    card.addEventListener('dragleave',()=>card.classList.remove('drag-over'));
    card.addEventListener('drop',async e=>{
      e.preventDefault();
      e.stopPropagation();
      card.classList.remove('drag-over');
      if(!draggedPhoto||draggedPhoto===card)return;
      // Dropping ON a card means inserting before that card.
      card.parentNode.insertBefore(draggedPhoto,card);
      await savePhotoOrder();
    });
  });
}
async function savePhotoOrder(){const order=[...photoGrid.querySelectorAll('.photo')].map(x=>x.dataset.mediaId);const fd=new FormData();fd.append('_csrf',csrf);fd.append('property_id',propertyId);fd.append('media_type','photo');order.forEach(x=>fd.append('order[]',x));try{const r=await fetch('/immobilier/admin/property-media-reorder.php',{method:'POST',body:fd,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}});const j=await r.json();if(!j.success)throw new Error(j.message||'Could not save photo order.');}catch(e){alert(e.message);location.reload();}}

const videoGrid=document.getElementById('videoGrid');let draggedVideo=null;if(videoGrid){videoGrid.querySelectorAll('.video-card').forEach(card=>{const handle=card.querySelector('.video-drag-handle');if(handle)handle.addEventListener('dragstart',e=>{draggedVideo=card;card.classList.add('dragging');e.dataTransfer.effectAllowed='move';e.dataTransfer.setData('text/plain',card.dataset.mediaId);});card.addEventListener('dragover',e=>{if(!draggedVideo||draggedVideo===card)return;e.preventDefault();card.classList.add('drag-over');});card.addEventListener('dragleave',()=>card.classList.remove('drag-over'));card.addEventListener('drop',async e=>{e.preventDefault();card.classList.remove('drag-over');if(!draggedVideo||draggedVideo===card)return;card.before(draggedVideo);await saveVideoOrder();});card.addEventListener('dragend',()=>{card.classList.remove('dragging');draggedVideo=null;videoGrid.querySelectorAll('.video-card').forEach(x=>x.classList.remove('drag-over'));});});}
async function saveVideoOrder(){const order=[...videoGrid.querySelectorAll('.video-card')].map(x=>x.dataset.mediaId);const fd=new FormData();fd.append('_csrf',csrf);fd.append('property_id',propertyId);fd.append('media_type','video');order.forEach(x=>fd.append('order[]',x));try{const r=await fetch('/immobilier/admin/property-media-reorder.php',{method:'POST',body:fd,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}});const j=await r.json();if(!j.success)throw new Error(j.message||'Could not save video order.');}catch(e){alert(e.message);location.reload();}}

const dfilter=document.getElementById('documentCategoryFilter'),dgrid=document.getElementById('documentGrid');if(dfilter&&dgrid)dfilter.addEventListener('change',()=>dgrid.querySelectorAll('.document-row').forEach(r=>{r.style.display=(dfilter.value==='all'||r.dataset.category===dfilter.value)?'grid':'none';}));
const docGrid=document.getElementById('documentGrid');let draggedDoc=null;if(docGrid){docGrid.querySelectorAll('.document-row').forEach(row=>{row.addEventListener('dragstart',e=>{if(e.target.closest('form,a,button,input,select')){e.preventDefault();return;}draggedDoc=row;row.classList.add('dragging');e.dataTransfer.effectAllowed='move';});row.addEventListener('dragover',e=>{if(!draggedDoc||draggedDoc===row)return;e.preventDefault();row.classList.add('drag-over');});row.addEventListener('dragleave',()=>row.classList.remove('drag-over'));row.addEventListener('drop',async e=>{e.preventDefault();row.classList.remove('drag-over');if(!draggedDoc||draggedDoc===row)return;row.before(draggedDoc);await saveDocumentOrder();});row.addEventListener('dragend',()=>{row.classList.remove('dragging');draggedDoc=null;docGrid.querySelectorAll('.document-row').forEach(x=>x.classList.remove('drag-over'));});});}
async function saveDocumentOrder(){const order=[...docGrid.querySelectorAll('.document-row')].map(x=>x.dataset.documentId);const fd=new FormData();fd.append('_csrf',csrf);fd.append('property_id',propertyId);order.forEach(x=>fd.append('order[]',x));try{const r=await fetch('/immobilier/admin/property-document-reorder.php',{method:'POST',body:fd,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}});const j=await r.json();if(!j.success)throw new Error(j.message||'Could not save document order.');}catch(e){alert(e.message);location.reload();}}
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
window.ImmoI18n = Object.assign({
  published_badge: <?=json_encode(__t('properties.publication.published_badge','Published'))?>,
  draft_badge: <?=json_encode(__t('properties.publication.draft_badge','Draft'))?>,
  featured_badge: <?=json_encode(__t('properties.publication.featured_badge','Featured'))?>,
  not_featured_badge: <?=json_encode(__t('properties.publication.not_featured_badge','Not featured'))?>,
  not_published: <?=json_encode(__t('properties.publication.not_published','Not published yet'))?>,
  yes: <?=json_encode(__t('common.yes','Yes'))?>,
  no: <?=json_encode(__t('common.no','No'))?>,
  map_unavailable: <?=json_encode(__t('properties.js_map_unavailable','Map unavailable'))?>,
  pool_dimensions: <?=json_encode(__t('properties.pool_dimensions','Pool dimensions'))?>,
  add_pool_dimensions: <?=json_encode(__t('properties.add_pool_dimensions','Add pool dimensions'))?>,
  add_dimensions: <?=json_encode(__t('properties.add_dimensions','Add dimensions'))?>,
  enter_pool_size: <?=json_encode(__t('properties.pool_dimensions_required','Please enter pool length and width.'))?>,
  save_failed: <?=json_encode(__t('properties.js_save_failed','Save failed'))?>,
  retry: <?=json_encode(__t('properties.js_retry','Retry'))?>,
  unsaved: <?=json_encode(__t('properties.js_unsaved','Unsaved changes'))?>,
  saved_now: <?=json_encode(__t('properties.js_saved_now','Saved just now'))?>,
  saving: <?=json_encode(__t('properties.js_saving','Saving…'))?>,
  enter_postcode_city: <?=json_encode(__t('properties.js_enter_postcode_city','Enter a postcode or city'))?>,
  valid_postcode: <?=json_encode(__t('properties.js_valid_postcode','Enter a valid 5-digit postcode'))?>,
  loading_area: <?=json_encode(__t('properties.js_loading_area','Loading area…'))?>,
  postcode_area: <?=json_encode(__t('properties.js_postcode_area','Postcode area'))?>,
  approximate_area: <?=json_encode(__t('properties.js_approximate_area','Approximate area'))?>,
  ten_km_area: <?=json_encode(__t('properties.js_ten_km_area','10 km area around'))?>,
  area_unavailable: <?=json_encode(__t('properties.js_area_unavailable','Area unavailable'))?>,
  postcode_unavailable: <?=json_encode(__t('properties.js_postcode_unavailable','Postcode area unavailable'))?>,
  city_unavailable: <?=json_encode(__t('properties.js_city_unavailable','City unavailable'))?>
}, window.ImmoI18n || {});
(function(){
 const form=document.getElementById('propertyForm'); const status=document.getElementById('autosaveStatus'); if(!form||!status) return;
 let timer=null, saving=false, dirty=false;
 const syncRich=()=>document.querySelectorAll('.rich-editor').forEach(setupRichEditor);
 const setStatus=(text,cls='')=>{status.textContent=text;status.className='autosave-status '+cls;};
 const syncPublicationUI=(published,featured,publishedAt)=>{const pb=document.getElementById('headerPublishedBadge'),pt=document.getElementById('headerPublishedText'),fb=document.getElementById('headerFeaturedBadge'),ft=document.getElementById('headerFeaturedText'),pill=document.getElementById('publicationStatePill'),state=document.getElementById('publicationStateText'),fs=document.getElementById('publicationFeaturedState'); if(pb){pb.classList.toggle('published',!!published);}if(pt)pt.textContent=published?ImmoI18n.published_badge:ImmoI18n.draft_badge;if(fb){fb.classList.toggle('featured',!!featured);}if(ft)ft.textContent=featured?ImmoI18n.featured_badge:ImmoI18n.not_featured_badge;if(pill){pill.classList.toggle('is-published',!!published);pill.innerHTML='<span></span>'+(published?ImmoI18n.published_badge:ImmoI18n.draft_badge);}if(state)state.textContent=published?(publishedAt?ImmoI18n.published_badge+' '+new Date(publishedAt.replace(' ','T')).toLocaleString():ImmoI18n.published_badge):ImmoI18n.not_published;if(fs)fs.textContent=featured?ImmoI18n.yes:ImmoI18n.no;};
 const save=async()=>{if(<?= $id>0?'false':'true' ?>||saving||!dirty)return; saving=true; setStatus('Saving…','saving'); document.querySelectorAll('.rich-editor').forEach(root=>{const c=root.querySelector('.rich-content'),src=root.querySelector('.rich-source');if(c&&src)src.value=c.innerHTML;}); const fd=new FormData(form); fd.set('action','save'); try{const r=await fetch(window.location.href,{method:'POST',body:fd,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}}); const j=await r.json(); if(!r.ok||!j.success)throw new Error(j.message||'Save failed'); syncPublicationUI(!!j.is_published,!!j.is_featured,j.published_at); setStatus('Saved just now','saved'); dirty=false;}catch(e){setStatus('Save failed · Retry','error');}finally{saving=false;}};
 const schedule=()=>{dirty=true;setStatus('Unsaved changes');clearTimeout(timer);timer=setTimeout(save,1300);};
 form.querySelectorAll('input:not([type=hidden]),select,textarea').forEach(el=>{el.addEventListener('input',schedule);el.addEventListener('change',()=>{if(el.name==='is_published'||el.name==='is_featured'){syncPublicationUI(!!form.querySelector('[name=is_published]')?.checked,!!form.querySelector('[name=is_featured]')?.checked,null);}schedule();});});
 document.querySelectorAll('.rich-content').forEach(el=>el.addEventListener('input',schedule));
 window.addEventListener('beforeunload',e=>{if(dirty){e.preventDefault();e.returnValue='';}});
 document.querySelector('.property-actions .save')?.addEventListener('click',()=>{dirty=false;setStatus('Saving…','saving');});
})();
(function(){
 const mapEl=document.getElementById('propertyMap');
 const post=document.getElementById('locationPostcode'), city=document.getElementById('locationCity'), st=document.getElementById('mapStatus');
 if(!mapEl||typeof L==='undefined')return;
 const map=L.map(mapEl,{zoomControl:true,scrollWheelZoom:false}).setView([46.6,2.3],10);
 L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'&copy; OpenStreetMap contributors'}).addTo(map);
 let layer=null;
 const setStatus=t=>{if(st)st.textContent=t;};
 const clearLayer=()=>{if(layer){map.removeLayer(layer);layer=null;}};
 const postcodeContour=async(code)=>{
   const url='https://geo.api.gouv.fr/communes?codePostal='+encodeURIComponent(code)+'&fields=nom,code,contour&format=geojson&geometry=contour';
   const r=await fetch(url,{headers:{Accept:'application/json'}}); if(!r.ok)throw new Error('postcode lookup failed');
   const geo=await r.json(); if(!geo.features||!geo.features.length)throw new Error('postcode not found'); return geo;
 };
 const cityCenter=async(name)=>{
   const url='https://geo.api.gouv.fr/communes?nom='+encodeURIComponent(name)+'&fields=nom,centre&format=json&limit=1';
   const r=await fetch(url,{headers:{Accept:'application/json'}}); if(!r.ok)throw new Error('city lookup failed');
   const rows=await r.json(); if(!rows.length||!rows[0].centre||!Array.isArray(rows[0].centre.coordinates))throw new Error('city not found'); return rows[0];
 };
 const draw=async()=>{
   const code=(post?.value||'').trim().replace(/\s+/g,''); const town=(city?.value||'').trim(); clearLayer();
   if(!code&&!town){setStatus(ImmoI18n.enter_postcode_city);map.setView([46.6,2.3],6);return;}
   setStatus(ImmoI18n.loading_area);
   try{
     if(code){
       if(!/^\d{5}$/.test(code)){setStatus(ImmoI18n.valid_postcode);return;}
       const geo=await postcodeContour(code);
       layer=L.geoJSON(geo,{style:{color:'#315cf6',weight:2,fillColor:'#315cf6',fillOpacity:.16}}).addTo(map);
       map.fitBounds(layer.getBounds(),{padding:[24,24],maxZoom:10}); map.setZoom(10); setStatus(ImmoI18n.postcode_area); return;
     }
     const row=await cityCenter(town), [lon,lat]=row.centre.coordinates;
     layer=L.circle([lat,lon],{radius:10000,color:'#315cf6',fillColor:'#315cf6',fillOpacity:.10,weight:2}).addTo(map);
     map.fitBounds(layer.getBounds(),{padding:[24,24],maxZoom:10}); map.setZoom(10); setStatus(ImmoI18n.ten_km_area+' '+row.nom);
   }catch(e){clearLayer();setStatus(code?ImmoI18n.postcode_unavailable:ImmoI18n.city_unavailable);}
 };
 let timer=null; const schedule=()=>{clearTimeout(timer);timer=setTimeout(draw,500);};
 post?.addEventListener('input',schedule); city?.addEventListener('input',schedule); post?.addEventListener('change',draw); city?.addEventListener('change',draw);
 document.querySelector('[data-tab="location"]')?.addEventListener('click',()=>setTimeout(()=>map.invalidateSize(),120));
 setTimeout(()=>{map.invalidateSize();draw();},80);
})();
document.querySelectorAll('.feature-tile input[type=checkbox]').forEach(cb=>{
  const tile=cb.closest('.feature-tile'); if(!tile)return;
  cb.addEventListener('change',()=>tile.classList.toggle('selected',cb.checked));
  tile.classList.toggle('selected',cb.checked);
  tile.addEventListener('click',e=>{
    if(e.target.closest('input,button,a,select,textarea'))return;
    e.preventDefault(); cb.checked=!cb.checked; cb.dispatchEvent(new Event('change',{bubbles:true}));
  });
});
(function(){
 const p=document.getElementById('poolBox'); if(!p)return; const tile=p.closest('.feature-tile'); const len=document.getElementById('pool_length_m'),wid=document.getElementById('pool_width_m'),dep=document.getElementById('pool_depth_m'); const check=()=>{if(p.checked && (!len.value||!wid.value)){tile?.classList.add('invalid');}}; p.form?.addEventListener('submit',e=>{if(p.checked&&(!len.value||!wid.value)){e.preventDefault();document.querySelector('[data-tab=features]')?.click();alert(ImmoI18n.enter_pool_size);}});})();
(function(){
 const percent=document.getElementById('completionPercent'),bar=document.getElementById('completionBar'); if(!percent||!bar)return; const calc=()=>{let n=0,total=6;const val=n=>!!String(n||'').trim(); if(val(document.querySelector('[name=property_type_id]')?.value)&&val(document.querySelector('[name=title]')?.value))n++; const rich=[...document.querySelectorAll('.rich-content')]; if(rich.some(x=>x.textContent.trim().length>0))n++; if(document.querySelector('[name="features[]"]:checked'))n++; if(document.querySelectorAll('#photoGrid .photo').length)n++; if(document.querySelectorAll('#documentGrid .document-row').length)n++; if(document.querySelector('[name=is_published]')?.checked)n++; const pc=Math.round(n/total*100);percent.textContent=pc+'%';bar.style.width=pc+'%';}; calc();})();

(function(){
 const modal=document.getElementById('heatingModal'),tile=document.getElementById('heatingTile'),apply=document.getElementById('heatingApply'),uf=document.getElementById('underfloorHeating'),summary=document.getElementById('heatingSummary');
 if(!modal||!tile)return;
 const close=()=>{modal.hidden=true;document.body.classList.remove('modal-open');};
 tile.addEventListener('click',()=>{modal.hidden=false;document.body.classList.add('modal-open');});
 modal.querySelectorAll('[data-heating-close]').forEach(x=>x.addEventListener('click',close));
 document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!modal.hidden)close();});
 uf?.addEventListener('change',()=>{const t=document.getElementById('underfloorHeatingText');if(t)t.textContent=uf.checked?'Yes':'No';});
 apply?.addEventListener('click',()=>{const r=modal.querySelector('input[name="heating_feature_id"]:checked');const method=r?.closest('label')?.querySelector('strong')?.textContent?.trim()||'Select heating';if(summary)summary.textContent=method+(uf?.checked?' · Underfloor heating':'');close();});
})();
</script>
</div><!-- /.wrap -->
</main><!-- /.pr-main -->
</div><!-- /.pr-app-shell -->
<script id="immo-commission-two-decimals">
(function(){
  function normalizeCommission(el){
    if(!el) return;
    var v=parseFloat(String(el.value).replace(',','.'));
    if(!Number.isNaN(v)) el.value=v.toFixed(2);
  }
  document.addEventListener('blur',function(e){
    var n=(e.target.name||e.target.id||'').toLowerCase();
    if(n.indexOf('commission')!==-1 && (n.indexOf('percent')!==-1 || n.indexOf('percentage')!==-1)){
      normalizeCommission(e.target);
    }
  },true);
})();
</script>
<script>
(function(){
  function bindLabel(selectId,imageId,prefix){
    var select=document.getElementById(selectId), image=document.getElementById(imageId);
    if(!select||!image)return;
    select.addEventListener('change',function(){
      var value=(select.value||'').toLowerCase();
      if(!/^[a-g]$/.test(value)){image.hidden=true;image.removeAttribute('src');return;}
      image.src='/immobilier/media/dpe/'+prefix+'-'+value+'.png';
      image.hidden=false;
    });
  }
  bindLabel('energyLabel','energyLabelImage','dpe');
  bindLabel('co2Label','co2LabelImage','ges');
})();
</script>
</body></html>
