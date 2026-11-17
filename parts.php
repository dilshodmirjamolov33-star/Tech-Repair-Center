<?php require 'config.php'; need(['admin','receptionist']); check();
if($_SERVER['REQUEST_METHOD']==='POST'){$a=$_POST['a']??'';
 if($a==='new')db()->prepare('INSERT INTO parts(name,sku,price,stock,min_stock) VALUES(?,?,?,?,?)')->execute([trim($_POST['name']),trim($_POST['sku'])?:null,(float)$_POST['price'],(int)$_POST['stock'],(int)$_POST['min_stock']]);
 if($a==='add')db()->prepare('UPDATE parts SET stock=stock+? WHERE id=?')->execute([max(0,(int)$_POST['qty']),(int)$_POST['id']]);
 flash('Анбор нав шуд.');header('Location: parts.php');exit;}
$rows=db()->query('SELECT * FROM parts ORDER BY (stock<=min_stock) DESC,name')->fetchAll();
head('Қисмҳои эҳтиётӣ');?><h1>Қисмҳои эҳтиётӣ</h1>
<div class="tablewrap"><table><thead><tr><th>Ном</th><th>Код</th><th>Нарх</th><th>Бақия</th><th>Воридот</th></tr></thead><tbody>
<?php foreach($rows as $r):$low=$r['stock']<=$r['min_stock'];?><tr><td><?=e($r['name'])?></td><td><?=e($r['sku'])?></td><td><?=money($r['price'])?></td><td><span class="pill <?=$low?'s-low':'s-ready'?>"><?=$r['stock']?><?=$low?' · кам':''?></span></td>
<td><form method="post" class="inline"><?=csrf()?><input type="hidden" name="a" value="add"><input type="hidden" name="id" value="<?=$r['id']?>"><input type="number" name="qty" value="5" min="1" style="width:80px"><button class="btn ghost sm">Илова</button></form></td></tr><?php endforeach;?></tbody></table></div>
<form method="post" class="card grid"><?=csrf()?><input type="hidden" name="a" value="new"><h3 class="full">Қисми нав</h3>
<label>Ном<input name="name" required></label><label>Код<input name="sku"></label><label>Нарх<input type="number" step="0.01" name="price" required></label><label>Бақия<input type="number" name="stock" value="0"></label><label>Ҳадди ақал<input type="number" name="min_stock" value="3"></label><button class="btn full">Сабт</button></form>
<?php foot();
