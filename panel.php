<?php require 'config.php'; need(['admin','receptionist','master']); $u=me();
$f=$_GET['s']??''; $w=[];$a=[];
if($u['role']==='master'){$w[]='o.master_id=?';$a[]=$u['id'];}
if(isset(STATUSES[$f])){$w[]='o.status=?';$a[]=$f;}
$q=trim($_GET['q']??'');if($q!==''){$w[]='(o.receipt_no LIKE ? OR c.full_name LIKE ? OR c.phone LIKE ?)';array_push($a,"%$q%","%$q%","%$q%");}
$s=db()->prepare('SELECT o.*,c.full_name,c.phone,d.brand,d.model,m.full_name mname FROM orders o JOIN clients c ON c.id=o.client_id JOIN devices d ON d.id=o.device_id LEFT JOIN users m ON m.id=o.master_id'.($w?' WHERE '.implode(' AND ',$w):'').' ORDER BY o.id DESC LIMIT 200');$s->execute($a);$rows=$s->fetchAll();
$cnt=db()->query('SELECT status,COUNT(*) c FROM orders GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);
head('Фармоишҳо');?>
<h1>Фармоишҳо</h1>
<div class="chips"><a class="<?=$f===''?'on':''?>" href="panel.php">Ҳама</a><?php foreach(STATUSES as $k=>$v):?><a class="<?=$f===$k?'on':''?>" href="?s=<?=$k?>"><?=e($v)?> <em><?=(int)($cnt[$k]??0)?></em></a><?php endforeach;?></div>
<form class="search" method="get"><input name="q" value="<?=e($q)?>" placeholder="Рақам, ном ё телефон"><button class="btn ghost">Ҷустуҷӯ</button></form>
<div class="tablewrap"><table><thead><tr><th>Квитансия</th><th>Мизоҷ</th><th>Дастгоҳ</th><th>Усто</th><th>Ҳолат</th><th>Ҷамъ</th><th></th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><b><?=e($r['receipt_no'])?></b><?=$r['is_warranty']?' <span class="tag">кафолат</span>':''?></td><td><?=e($r['full_name'])?><br><small><?=e($r['phone'])?></small></td><td><?=e($r['brand'].' '.$r['model'])?></td><td><?=e($r['mname']?:'—')?></td><td><span class="pill s-<?=e($r['status'])?>"><?=e(STATUSES[$r['status']])?></span></td><td><?=money($r['total'])?></td><td><a class="btn ghost sm" href="order.php?id=<?=$r['id']?>">Кушодан</a></td></tr>
<?php endforeach; if(!$rows):?><tr><td colspan="7" class="muted">Фармоише нест.</td></tr><?php endif;?></tbody></table></div>
<?php foot();
