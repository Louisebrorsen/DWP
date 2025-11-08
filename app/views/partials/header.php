<!DOCTYPE html>
<html lang="da">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($SITE['name']) ?> – Biograf</title>
  <meta name="description" content="<?= e($SITE['description']) ?>" />
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
  <header>
    <div class="container nav">
      <a href="<?= url('?page=home') ?>" class="brand" aria-label="Forside">
        <span class="brand__logo" aria-hidden="true"></span>
        <span><?= e($SITE['name']) ?></span>
      </a>

      <nav aria-label="Hovednavigation" class="menu">
        <a href="<?= url('?page=home') ?>" <?= nav_active('home') ?>>I biografen</a>
        <a href="#today">Dagens tider</a>
        <a href="#coming">Kommende</a>
        <a href="#about">Om os</a>
        <a href="#contact">Kontakt</a>
        <?php if (!empty($_SESSION['member_email'])): ?>
          <a href="<?= url('?page=logout') ?>">Log ud</a>
        <?php else: ?>
          <a href="<?= url('?page=login') ?>">Login</a>
        <?php endif; ?>
      </nav>

      <details class="navdrop">
        <summary aria-label="Åbn menu"><span class="hamb"><span></span></span></summary>
        <div class="drawer" role="menu">
          <a role="menuitem" href="<?= url('?page=home') ?>">I biografen</a>
          <a role="menuitem" href="#today">Dagens tider</a>
          <a role="menuitem" href="#coming">Kommende</a>
          <a role="menuitem" href="#about">Om os</a>
          <a role="menuitem" href="#contact">Kontakt</a>
          <?php if (!empty($_SESSION['member_email'])): ?>
            <a role="menuitem" href="<?= url('?page=logout') ?>">Log ud</a>
          <?php else: ?>
            <a role="menuitem" href="<?= url('?page=login') ?>">Login</a>
          <?php endif; ?>
        </div>
      </details>
    </div>
  </header>