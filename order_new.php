<?php require 'config.php'; need(['admin','receptionist']); check();
$masters=db()->query("SELECT id,full_name FROM users WHERE role='master' AND is_active=1")->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
 $p=fn($k)=>trim($_POST[$k]??''); $pdo=db(); $pdo->beginTransaction();
 $phone=preg_replace('/[^\d+]/','',$p('phone'));
 $c=$pdo->prepare('SELECT id FROM clients WHERE phone=?');$c->execute([$phone]);$cid=$c->fetchColumn();
 if(!$cid){$pdo->prepare('INSERT INTO clients(full_name,phone) VALUES(?,?)')->execute([$p('full_name'),$phone]);$cid=$pdo->lastInsertId();}
 $did=null;$serial=$p('serial_no');
 if($serial!==''){$d=$pdo->prepare('SELECT id FROM devices WHERE serial_no=? AND client_id=?');$d->execute([$serial,$cid]);$did=$d->fetchColumn();}
 if(!$did){$pdo->prepare('INSERT INTO devices(client_id,type,brand,model,serial_no) VALUES(?,?,?,?,?)')->execute([$cid,$p('type')==='laptop'?'laptop':'phone',$p('brand'),$p('model'),$serial?:null]);$did=$pdo->lastInsertId();}
 // санҷиши кафолат: ҳамон дастгоҳ + ҳамон носозӣ + кафолат ба охир нарасидааст
 $g=$pdo->prepare("SELECT id FROM orders WHERE device_id=? AND fault_type=? AND status='delivered' AND warranty_until>=CURDATE() ORDER BY id DESC LIMIT 1");$g->execute([$did,$p('fault_type')]);$rel=$g->fetchColumn();
 $mid=(int)($_POST['master_id']??0)?:null;
 $pdo->prepare('INSERT INTO orders(receipt_no,client_id,device_id,fault_type,fault_desc,received_by,master_id,warranty_days,is_warranty,related_order_id) VALUES(?,?,?,?,?,?,?,?,?,?)')
  ->execute([uniqid('T'),$cid,$did,$p('fault_type'),$p('fault_desc'),me()['id'],$mid,max(0,(int)$p('warranty_days')),$rel?1:0,$rel?:null]);
 $oid=$pdo->lastInsertId(); $no='RC'.date('y').str_pad($oid,5,'0',STR_PAD_LEFT);
 $pdo->prepare('UPDATE orders SET receipt_no=? WHERE id=?')->execute([$no,$oid]);
 logst($oid,'received',$rel?'Бозгашт дар муддати кафолат':'Дастгоҳ қабул шуд');
 $pdo->commit(); flash($rel?'Кафолат: таъмир ройгон аст.':'Дастгоҳ қабул шуд.'); header("Location: receipt.php?id=$oid"); exit;}
head('Қабули дастгоҳ');?>
<h1>Қабули дастгоҳ</h1>
<form method="post" class="card grid"><?=csrf()?>
<label>Номи мизоҷ<input name="full_name" required></label><label>Телефон<input name="phone" required placeholder="+992 ..."></label>
<label>Навъ<select name="type"><option value="phone">Телефон</option><option value="laptop">Ноутбук</option></select></label>
<label>Бренд<input name="brand" required></label><label>Модел<input name="model" required></label><label>IMEI / рақами серия<input name="serial_no"></label>
<label>Намуди носозӣ<select name="fault_type"><?php foreach(FAULTS as $x):?><option><?=e($x)?></option><?php endforeach;?></select></label>
<label>Усто<select name="master_id"><option value="0">Баъдтар таъин мешавад</option><?php foreach($masters as $m):?><option value="<?=$m['id']?>"><?=e($m['full_name'])?></option><?php endforeach;?></select></label>
<label>Кафолат (рӯз)<input type="number" name="warranty_days" value="90" min="0"></label>
<label class="full">Тавсифи носозӣ<textarea name="fault_desc" rows="3" required></textarea></label>
<button class="btn full">Қабул ва квитансия</button></form>
<?php foot();
