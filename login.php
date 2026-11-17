<?php require 'config.php'; check(); $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $s=db()->prepare('SELECT * FROM users WHERE username=? AND is_active=1');$s->execute([trim($_POST['username']??'')]);$u=$s->fetch();
 if($u&&password_verify($_POST['password']??'',$u['password_hash'])){session_regenerate_id(true);$_SESSION['u']=['id'=>$u['id'],'full_name'=>$u['full_name'],'role'=>$u['role']];header('Location: panel.php');exit;}
 $err='Логин ё парол нодуруст аст.';}
head('Ворид');?>
<section class="narrow card"><h1>Ворид</h1><?php if($err):?><p class="err"><?=e($err)?></p><?php endif;?>
<form method="post"><?=csrf()?><label>Логин<input name="username" required autofocus></label><label>Парол<input type="password" name="password" required></label><button class="btn">Ворид шудан</button></form></section>
<?php foot();
