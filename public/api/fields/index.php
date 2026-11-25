<?php
require '../../../config.php'; header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');
if (!user()) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'unauthorized']); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); header('Allow: GET'); echo json_encode(['ok'=>false,'error'=>'method_not_allowed']); exit; }
$page=max(1,(int)($_GET['page']??1)); $per=min(25,max(1,(int)($_GET['per_page']??15))); $off=($page-1)*$per; $q=trim($_GET['q']??'');
$where=''; $params=[]; if ($q!=='') { $where='WHERE o.code LIKE ? OR d.brand LIKE ? OR d.model LIKE ?'; $params=['%'.$q.'%','%'.$q.'%','%'.$q.'%']; }
$c=$pdo->prepare('SELECT COUNT(*) FROM orders o JOIN devices d ON d.id=o.device_id '.$where); $c->execute($params); $total=(int)$c->fetchColumn();
$sql='SELECT o.id,o.code,o.status,o.created_at,d.brand,d.model FROM orders o JOIN devices d ON d.id=o.device_id '.$where.' ORDER BY o.id DESC LIMIT '.$per.' OFFSET '.$off; $s=$pdo->prepare($sql); $s->execute($params); echo json_encode(['ok'=>true,'data'=>$s->fetchAll(),'meta'=>['page'=>$page,'per_page'=>$per,'total'=>$total]],JSON_UNESCAPED_UNICODE);
