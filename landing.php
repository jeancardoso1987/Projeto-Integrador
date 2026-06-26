<?php
require_once __DIR__ . '/includes/auth.php';

if (estaLogado()) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MVP Timer — Nunca mais perca um MVP no Ragnarok Online</title>
    <link rel="icon" type="image/svg+xml" href="img/logo.svg">
    <link rel="stylesheet" href="css/fonts.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/landing.css">
</head>
<body class="landing-page">

<svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs>
    <symbol id="i-sword" viewBox="0 0 24 24"><polyline points="14.5 17.5 3 6 3 3 6 3 17.5 14.5"/><line x1="13" y1="19" x2="19" y2="13"/><line x1="16" y1="16" x2="20" y2="20"/><line x1="19" y1="21" x2="21" y2="19"/></symbol>
    <symbol id="i-target" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></symbol>
    <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></symbol>
    <symbol id="i-bell" viewBox="0 0 24 24"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></symbol>
    <symbol id="i-server" viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></symbol>
</defs></svg>

<nav class="lp-nav">
    <a href="landing.php" class="lp-brand">
        <img src="img/logo.svg" alt="MVP Timer">
        <span class="wm">MVP <span class="t2">TIMER</span></span>
    </a>
    <div class="lp-nav-links">
        <a href="registro.php">Criar conta</a>
        <a href="login.php" class="lp-entrar">Entrar</a>
    </div>
</nav>

<header class="lp-hero">
    <div class="lp-hero-inner">
        <img src="img/logo.svg" class="lp-emblem" alt="">
        <h1>Nunca mais <span class="accent">perca</span> um MVP.</h1>
        <p class="sub">
            Registre a morte do chefe e deixe o resto com a gente. O MVP Timer calcula o respawn,
            mostra a contagem regressiva e te avisa antes da hora — em todos os seus servidores.
        </p>
        <div class="lp-cta">
            <a href="registro.php" class="lp-btn lp-btn-primary">
                <svg><use href="#i-clock"/></svg> Começar agora
            </a>
            <a href="login.php" class="lp-btn lp-btn-ghost">Entrar</a>
        </div>
    </div>
</header>

<div class="lp-preview">
    <div class="lp-mvp">
        <span class="thumb"><svg><use href="#i-sword"/></svg></span>
        <div><strong>Valquíria Randgris</strong><small>odin_tem03 · LATAM</small></div>
        <span class="timer">2:41:08</span>
    </div>
    <div class="lp-mvp">
        <span class="thumb"><svg><use href="#i-target"/></svg></span>
        <div><strong>Tao Gunka</strong><small>beach_dun · HERO</small></div>
        <span class="timer spawn">SPAWN!</span>
    </div>
</div>

<section class="lp-features">
    <div class="lp-feature">
        <div class="ico-wrap"><svg><use href="#i-clock"/></svg></div>
        <h3>Tempo real</h3>
        <p>Contagem regressiva precisa do respawn de cada MVP, calculada automaticamente.</p>
    </div>
    <div class="lp-feature">
        <div class="ico-wrap"><svg><use href="#i-bell"/></svg></div>
        <h3>Alertas</h3>
        <p>Aviso sonoro e notificação no navegador alguns minutos antes do spawn.</p>
    </div>
    <div class="lp-feature">
        <div class="ico-wrap"><svg><use href="#i-server"/></svg></div>
        <h3>Multi-servidor</h3>
        <p>Acompanhe LATAM, HERO, NIDHHOG e quantos servidores você jogar.</p>
    </div>
</section>

<footer class="lp-footer">
    MVP Timer · Ragnarok Online &nbsp;—&nbsp; Projeto Integrador
</footer>

</body>
</html>
