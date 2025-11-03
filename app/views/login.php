

<main class="container" style="padding:40px 20px; max-width:480px; margin:auto; text-align:left;">
  <h1>Login</h1>
  <p>Indtast din email og adgangskode for at logge ind.</p>

  <?php if (!empty($login_error)): ?>
    <div style="margin:12px 0; padding:12px; border-radius:10px; background:#402020; color:#ffd6d6;">
      <?= e($login_error) ?>
    </div>
  <?php endif; ?>

  <form method="post" style="display:grid; gap:12px;">
    <input type="hidden" name="form" value="login">
    <?= csrf_input() ?>

    <label>
      Email<br>
      <input type="email" name="email" required style="width:100%; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:rgba(0,0,0,.1); color:inherit;">
    </label>

    <label>
      Adgangskode<br>
      <input type="password" name="password" required style="width:100%; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:rgba(0,0,0,.1); color:inherit;">
    </label>

    <button type="submit" class="btn btn--primary">Log ind</button>
  </form>

  <p style="margin-top:20px; font-size:14px; color:var(--muted);">
    Har du ikke en konto? <a href="<?= url('?page=register') ?>" class="btn btn--ghost" style="margin-left:6px;">Opret bruger</a>
  </p>
</main>