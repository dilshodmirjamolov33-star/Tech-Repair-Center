<?php require 'config.php';
$r=strtoupper(trim($_GET['r']??'')); $o=null; $log=[];
if($r!==''){$s=db()->prepare('SELECT o.*,d.type,d.brand,d.model FROM orders o JOIN devices d ON d.id=o.device_id WHERE o.receipt_no=?');$s->execute([$r]);$o=$s->fetch();
 if($o){$l=db()->prepare('SELECT status,created_at FROM order_status_log WHERE order_id=? ORDER BY id');$l->execute([$o['id']]);$log=$l->fetchAll();}}
head('Пайгирии таъмир');$keys=array_keys(STATUSES);?>
<section class="hero">
 <div class="hero-text"><h1>Дастгоҳи шумо дар кадом марҳала аст?</h1>
 <p>Рақами квитансияро ворид кунед. Ворид шудан лозим нест.</p></div>
 <form id="track" class="ticket" method="get" action="index.php#result">
  <label for="r">Рақами квитансия</label>
  <input id="r" name="r" value="<?=e($r)?>" placeholder="RC2600001" autocomplete="off" required>
  <button class="btn">Нишон додан</button></form>
</section>
<?php if($r!==''):?><section id="result" class="result card">
<?php if(!$o):?><p class="err">Квитансияи «<?=e($r)?>» ёфт нашуд. Рақамро аз руи варақа санҷед.</p>
<?php else: $i=array_search($o['status'],$keys);?>
 <div class="rhead"><div><h2><?=e($o['brand'].' '.$o['model'])?></h2><p class="muted">Квитансия <?=e($o['receipt_no'])?> · қабул: <?=e(date('d.m.Y',strtotime($o['created_at'])))?></p></div>
 <span class="pill s-<?=e($o['status'])?>"><?=e(STATUSES[$o['status']])?></span></div>
 <ol class="trace" style="--n:<?=count($keys)?>;--i:<?=$i?>"><?php foreach($keys as $k=>$st):?><li class="<?=$k<$i?'done':($k==$i?'now':'')?>"><b></b><span><?=e(STATUSES[$st])?></span></li><?php endforeach;?></ol>
 <dl class="facts"><div><dt>Носозӣ</dt><dd><?=e($o['fault_type'])?></dd></div>
 <?php if(in_array($o['status'],['ready','delivered'])):?><div><dt>Арзиши таъмир</dt><dd><?=$o['is_warranty']?'Ройгон (кафолат)':money($o['total'])?></dd></div><?php endif;?>
 <?php if($o['warranty_until']):?><div><dt>Кафолат то</dt><dd><?=e(date('d.m.Y',strtotime($o['warranty_until'])))?></dd></div><?php endif;?></dl>
 <?php if($o['status']==='ready'):?><p class="ok">Дастгоҳ тайёр аст. Бо квитансия биёед ва гиред.</p><?php endif;?>
<?php endif;?></section><?php endif;?>
<section class="how"><div><h3>Кафолат</h3><p>Агар дастгоҳ дар муддати кафолат бо ҳамон носозӣ баргардад, таъмир ройгон аст.</p></div>
<div><h3>Ҳолат дар вақти воқеӣ</h3><p>Усто ҳолатро иваз мекунад — шумо дарҳол мебинед.</p></div></section>
<?php foot();
