<?php require 'config.php'; need(['admin','receptionist']);
$s=db()->prepare('SELECT o.*,c.full_name,c.phone,d.type,d.brand,d.model,d.serial_no,m.full_name mname,r.full_name rname FROM orders o JOIN clients c ON c.id=o.client_id JOIN devices d ON d.id=o.device_id JOIN users r ON r.id=o.received_by LEFT JOIN users m ON m.id=o.master_id WHERE o.id=?');$s->execute([(int)($_GET['id']??0)]);$o=$s->fetch();if(!$o)exit('Ёфт нашуд');
$url=(isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['SCRIPT_NAME']),'/').'/index.php?r='.$o['receipt_no'];
head('Квитансия');?>
<div class="noprint actions"><button class="btn" id="pdf">Боргирии PDF</button><button class="btn ghost" onclick="print()">Чоп</button><a class="btn ghost" href="order.php?id=<?=$o['id']?>">Ба фармоиш</a></div>
<div id="rc" class="receipt"><h2>ТехСервис</h2><p class="muted">Квитансияи қабули дастгоҳ</p><div class="rno"><?=e($o['receipt_no'])?></div>
<table><tr><td>Сана</td><td><?=e(date('d.m.Y H:i',strtotime($o['created_at'])))?></td></tr><tr><td>Мизоҷ</td><td><?=e($o['full_name'])?>, <?=e($o['phone'])?></td></tr><tr><td>Дастгоҳ</td><td><?=$o['type']==='laptop'?'Ноутбук':'Телефон'?> <?=e($o['brand'].' '.$o['model'])?></td></tr><tr><td>Серия / IMEI</td><td><?=e($o['serial_no']?:'—')?></td></tr><tr><td>Носозӣ</td><td><?=e($o['fault_type'])?>: <?=e($o['fault_desc'])?></td></tr><tr><td>Усто</td><td><?=e($o['mname']?:'—')?></td></tr><tr><td>Кафолат</td><td><?=$o['is_warranty']?'Ҳолати кафолатӣ — ройгон':(int)$o['warranty_days'].' рӯз пас аз супоридан'?></td></tr><tr><td>Қабулкунанда</td><td><?=e($o['rname'])?></td></tr></table>
<p class="small">Ҳолатро бе воридшавӣ дар <b><?=e($url)?></b> бубинед.</p><div class="sign"><span>Имзои мизоҷ</span><span>Имзои қабулкунанда</span></div></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>document.getElementById('pdf').onclick=()=>html2pdf().set({margin:8,filename:'<?=e($o['receipt_no'])?>.pdf',html2canvas:{scale:2},jsPDF:{format:'a5'}}).from(document.getElementById('rc')).save();</script>
<?php foot();
