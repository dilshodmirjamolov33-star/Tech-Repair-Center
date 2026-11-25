<?php
require '../../../config.php';
header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); header('Allow: GET'); echo json_encode(['ok'=>false,'error'=>'method_not_allowed']); exit; }
$code = strtoupper(trim($_GET['code'] ?? '')); if ($code === '' || strlen($code)>40) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'invalid_code']); exit; }
$q=$pdo->prepare('SELECT o.code,o.status,o.fault_type,o.is_warranty,o.warranty_until,o.created_at,d.type,d.brand,d.model FROM orders o JOIN devices d ON d.id=o.device_id WHERE o.code=?'); $q->execute([$code]); $o=$q->fetch();
if (!$o) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'not_found']); exit; }
$q=$pdo->prepare('SELECT status,note,created_at FROM order_status_log WHERE order_id=(SELECT id FROM orders WHERE code=?) ORDER BY id'); $q->execute([$code]);
$log=$q->fetchAll(); echo json_encode(['ok'=>true,'data'=>['order'=>$o,'history'=>$log,'statuses'=>STATUSES]],JSON_UNESCAPED_UNICODE);
