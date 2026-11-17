<?php
session_start();
const DB_HOST='localhost', DB_NAME='repair_centers', DB_USER='root', DB_PASS='';
const STATUSES=['received'=>'Қабул шуд','diagnosis'=>'Ташхис','repair'=>'Дар таъмир','ready'=>'Тайёр','delivered'=>'Супорида шуд'];
const FAULTS=['Экран','Батарея','Зарядка / пайвасткунак','Плата / ҷузъҳои электронӣ','Нармафзор','Об дода шудааст','Клавиатура / тугмаҳо','Гармшавӣ / кулер','Дигар'];
function db(){static $p;if(!$p)$p=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);return $p;}
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function me(){return $_SESSION['u']??null;}
function need(array $roles){if(!me()||!in_array(me()['role'],$roles,true)){header('Location: login.php');exit;}}
function csrf(){if(empty($_SESSION['t']))$_SESSION['t']=bin2hex(random_bytes(16));return '<input type="hidden" name="t" value="'.$_SESSION['t'].'">';}
function check(){if($_SERVER['REQUEST_METHOD']==='POST'&&!hash_equals($_SESSION['t']??'',$_POST['t']??'')){http_response_code(400);exit('Хатои амният. Саҳифаро нав кунед.');}}
function money($n){return number_format((float)$n,2,'.',' ').' сом.';}
function flash($m=null){if($m!==null){$_SESSION['f']=$m;return;}$x=$_SESSION['f']??'';unset($_SESSION['f']);return $x;}
function logst($oid,$st,$note=null){db()->prepare('INSERT INTO order_status_log(order_id,status,note,user_id) VALUES(?,?,?,?)')->execute([$oid,$st,$note,me()['id']??null]);}
function recalc($oid){$p=db()->prepare('SELECT COALESCE(SUM(qty*unit_price),0) FROM order_parts WHERE order_id=?');$p->execute([$oid]);$pc=$p->fetchColumn();
 db()->prepare('UPDATE orders SET parts_cost=?, total=IF(is_warranty=1,0,work_cost+?) WHERE id=?')->execute([$pc,$pc,$oid]);}
function head($t){$u=me();?><!doctype html><html lang="tg"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($t)?> — ТехСервис</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css"></head><body>
<header class="top"><a class="logo" href="index.php"><svg viewBox="0 0 32 32" width="28" height="28"><path d="M4 16h8l3-8 4 16 3-8h6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>ТехСервис</a>
<nav><?php if($u):?><a href="panel.php">Фармоишҳо</a><?php if($u['role']!=='master'):?><a href="order_new.php">Қабули нав</a><a href="parts.php">Қисмҳо</a><?php endif;?><span class="who"><?=e($u['full_name'])?></span><a class="btn ghost" href="logout.php">Баромад</a>
<?php else:?><a href="index.php#track">Пайгирӣ</a><a class="btn ghost" href="login.php">Ворид барои кормандон</a><?php endif;?></nav></header><main><?php if($m=flash()):?><div class="toast"><?=e($m)?></div><?php endif;
}
function foot(){?></main><footer>© <?=date('Y')?> ТехСервис · Маркази таъмири телефон ва ноутбук</footer><script src="assets/app.js"></script></body></html><?php }
