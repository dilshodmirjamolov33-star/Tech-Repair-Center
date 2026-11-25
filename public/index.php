<?php require '../config.php'; head('Асосӣ'); ?>
<section class="hero">
  <span class="pill reveal">Маркази таъмири телефон ва ноутбук</span>
  <h1 class="reveal">Таъмири <span class="grad">босуръат</span> ва шаффоф</h1>
  <p class="reveal">Рақами квитансияро ворид кунед ва ҳолати таъмирро дар лаҳза бинед — бе бақайдгирӣ.</p>
  <form action="track.php" method="get" class="track reveal">
    <input name="code" placeholder="Масалан: RC-27-00001" required>
    <button class="btn">Санҷидан →</button>
  </form>
</section>
<section class="grid3">
  <div class="card tilt reveal"><div class="ico">📥</div><h3>Қабули зуд</h3><p>Қабулкунанда дастгоҳро қабул карда, квитансияи PDF медиҳад.</p></div>
  <div class="card tilt reveal"><div class="ico">🛠️</div><h3>Пайгирии ҳолат</h3><p>Қабул → ташхис → таъмир → тайёр → супорида шуд.</p></div>
  <div class="card tilt reveal"><div class="ico">🛡️</div><h3>Кафолат</h3><p>Бо ҳамон носозӣ дар муддати кафолат — таъмир ройгон.</p></div>
</section>
<?php foot();
