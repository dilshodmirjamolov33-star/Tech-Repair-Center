<?php require 'config.php'; need(['admin','receptionist','master']); check(); $u=me(); $id=(int)($_GET['id']??0);
$s=db()->prepare('SELECT o.*,c.full_name,c.phone,d.type,d.brand,d.model,d.serial_no,m.full_name mname FROM orders o JOIN clients c ON c.id=o.client_id JOIN devices d ON d.id=o.device_id LEFT JOIN users m ON m.id=o.master_id WHERE o.id=?');$s->execute([$id]);$o=$s->fetch();
if(!$o||($u['role']==='master'&&$o['master_id']!=$u['id'])){http_response_code(404);exit('Ёфт нашуд');}
$staff=$u['role']!=='master';
if($_SERVER['REQUEST_METHOD']==='POST'){ $a=$_POST['a']??''; $pdo=db();
 if($a==='assign'&&$staff){$pdo->prepare('UPDATE orders SET master_id=? WHERE id=?')->execute([(int)$_POST['master_id']?:null,$id]);flash('Усто таъин шуд.');}
 if($a==='status'){$st=$_POST['status']??'';
  if(isset(STATUSES[$st])&&($st!=='delivered'||$staff)){
   if($st==='delivered'){$pdo->prepare('UPDATE orders SET status=?,delivered_at=NOW(),warranty_until=DATE_ADD(CURDATE(),INTERVAL warranty_days DAY) WHERE id=?')->execute([$st,$id]);}
   else $pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$st,$id]);
   logst($id,$st,trim($_POST['note']??'')?:null);flash('Ҳолат иваз шуд.');}}
 if($a==='cost'){$pdo->prepare('UPDATE orders SET work_cost=? WHERE id=?')->execute([max(0,(float)$_POST['work_cost']),$id]);recalc($id);flash('Арзиши кор нигоҳ дошта шуд.');}
 if($a==='part'){$pid=(int)$_POST['part_id'];$q=max(1,(int)$_POST['qty']);$pdo->beginTransaction();
  $p=$pdo->prepare('SELECT * FROM parts WHERE id=? FOR UPDATE');$p->execute([$pid]);$pt=$p->fetch();
  if(!$pt||$pt['stock']<$q){$pdo->rollBack();flash('Дар анбор кифоя нест.');}
  else{$pdo->prepare('UPDATE parts SET stock=stock-? WHERE id=?')->execute([$q,$pid]);
   $pdo->prepare('INSERT INTO order_parts(order_id,part_id,qty,unit_price,added_by) VALUES(?,?,?,?,?)')->execute([$id,$pid,$q,$pt['price'],$u['id']]);recalc($id);$pdo->commit();flash('Қисми эҳтиётӣ илова шуд.');}}
 header("Location: order.php?id=$id");exit;}
$parts=db()->query('SELECT * FROM parts ORDER BY name')->fetchAll();
$op=db()->prepare('SELECT op.*,p.name FROM order_parts op JOIN parts p ON p.id=op.part_id WHERE order_id=?');$op->execute([$id]);$used=$op->fetchAll();
$lg=db()->prepare('SELECT * FROM order_status_log WHERE order_id=? ORDER BY id DESC');$lg->execute([$id]);$log=$lg->fetchAll();
$masters=db()->query("SELECT id,full_name FROM users WHERE role='master' AND is_active=1")->fetchAll();
head($o['receipt_no']);?>
<div class="rhead"><div><h1><?=e($o['receipt_no'])?></h1><p class="muted"><?=e($o['brand'].' '.$o['model'])?> · <?=e($o['full_name'])?> · <?=e($o['phone'])?></p></div><span class="pill s-<?=e($o['status'])?>"><?=e(STATUSES[$o['status']])?></span></div>
<?php if($o['is_warranty']):?><p class="ok">Ҳолати кафолатӣ: ҳамон носозӣ дар муддати кафолат баргаштааст, таъмир ройгон аст.</p><?php endif;?>
<div class="cols"><div class="card"><h3>Носозӣ</h3><p><b><?=e($o['fault_type'])?></b></p><p><?=nl2br(e($o['fault_desc']))?></p><p class="muted">Серия: <?=e($o['serial_no']?:'—')?> · Усто: <?=e($o['mname']?:'таъин нашудааст')?></p>
<?php if($staff):?><form method="post" class="inline"><?=csrf()?><input type="hidden" name="a" value="assign"><select name="master_id"><option value="0">—</option><?php foreach($masters as $m):?><option value="<?=$m['id']?>" <?=$m['id']==$o['master_id']?'selected':''?>><?=e($m['full_name'])?></option><?php endforeach;?></select><button class="btn ghost sm">Таъин</button></form><?php endif;?>
<a class="btn ghost sm" href="receipt.php?id=<?=$id?>">Квитансия (PDF)</a></div>
<div class="card"><h3>Ҳолат</h3><form method="post"><?=csrf()?><input type="hidden" name="a" value="status"><select name="status"><?php foreach(STATUSES as $k=>$v): if($k==='delivered'&&!$staff)continue;?><option value="<?=$k?>" <?=$k===$o['status']?'selected':''?>><?=e($v)?></option><?php endforeach;?></select><input name="note" placeholder="Эзоҳ (ихтиёрӣ)"><button class="btn sm">Иваз кардан</button></form>
<ul class="log"><?php foreach($log as $l):?><li><b><?=e(STATUSES[$l['status']])?></b> <small><?=e(date('d.m H:i',strtotime($l['created_at'])))?></small><?=$l['note']?'<br><small>'.e($l['note']).'</small>':''?></li><?php endforeach;?></ul></div></div>
<div class="card"><h3>Қисмҳои эҳтиётӣ ва арзиш</h3>
<table><thead><tr><th>Қисм</th><th>Шумора</th><th>Нарх</th><th>Ҷамъ</th></tr></thead><tbody><?php foreach($used as $x):?><tr><td><?=e($x['name'])?></td><td><?=$x['qty']?></td><td><?=money($x['unit_price'])?></td><td><?=money($x['qty']*$x['unit_price'])?></td></tr><?php endforeach;if(!$used):?><tr><td colspan="4" class="muted">Ҳоло қисм истифода нашудааст.</td></tr><?php endif;?></tbody></table>
<form method="post" class="inline"><?=csrf()?><input type="hidden" name="a" value="part"><select name="part_id"><?php foreach($parts as $p):?><option value="<?=$p['id']?>"><?=e($p['name'])?> (бақия: <?=$p['stock']?>)</option><?php endforeach;?></select><input type="number" name="qty" value="1" min="1" style="width:80px"><button class="btn ghost sm">Илова</button></form>
<form method="post" class="inline"><?=csrf()?><input type="hidden" name="a" value="cost"><input type="number" step="0.01" name="work_cost" value="<?=e($o['work_cost'])?>"><button class="btn ghost sm">Арзиши кор</button></form>
<dl class="sum"><div><dt>Кор</dt><dd><?=money($o['work_cost'])?></dd></div><div><dt>Қисмҳо</dt><dd><?=money($o['parts_cost'])?></dd></div><div class="tot"><dt>Ҷамъ</dt><dd><?=$o['is_warranty']?'0.00 сом. (кафолат)':money($o['total'])?></dd></div></dl></div>
<?php foot();
