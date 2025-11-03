<main class="user-main container" style="padding:40px 20px;">
  <header class="user-head">
    <div class="user-avatar" aria-hidden="true">
      <?php
        $nm = $_SESSION['member_name'] ?? '';
        $initials = '';
        if ($nm) {
          $parts = preg_split('/\s+/', trim($nm));
          $initials = strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr(end($parts) ?: '', 0, 1));
        }
        echo $initials ?: 'U';
      ?>
    </div>
    <div>
      <h1 style="margin:0 0 6px;">Hej <?= e($_SESSION['member_name'] ?? 'CineMagic gæst') ?></h1>
      <p class="muted" style="margin:0;"><?= e($_SESSION['member_email'] ?? 'Log ind for at se dine oplysninger') ?></p>
    </div>
    <div class="user-head__actions">
      <a class="btn btn--ghost" href="<?= url('?page=login') ?>">Log ind</a>
      <a class="btn btn--primary" href="<?= url('?page=register') ?>">Opret bruger</a>
    </div>
  </header>

  <section class="user-grid">
    <!-- Profilkort -->
    <article class="user-card">
      <h2>Konto</h2>
      <div class="user-fields">
        <div>
          <div class="label">Navn</div>
          <div class="value"><?= e($_SESSION['member_name'] ?? '—') ?></div>
        </div>
        <div>
          <div class="label">Email</div>
          <div class="value"><?= e($_SESSION['member_email'] ?? '—') ?></div>
        </div>
      </div>
      <div class="user-actions">
        <a class="btn btn--ghost" href="#">Redigér profil</a>
        <a class="btn btn--ghost" href="#">Skift adgangskode</a>
      </div>
    </article>

    <!-- Hurtige tal / status  -->
    <article class="user-card">
      <h2>Dine billetter</h2>
      <div class="stats">
        <div class="stat"><div class="stat__num">0</div><div class="stat__label">Aktive</div></div>
        <div class="stat"><div class="stat__num">0</div><div class="stat__label">Kommende</div></div>
        <div class="stat"><div class="stat__num">0</div><div class="stat__label">Tidligere</div></div>
      </div>
      <p class="muted small">Her kan du senere se og administrere dine bookinger.</p>
    </article>

    <!-- Seneste bookinger (placeholder) -->
    <article class="user-card user-card--span2">
      <h2>Seneste bookinger</h2>
      <div class="list" role="list" style="margin-top:10px;">
        <div class="row" role="listitem">
          <div>
            <div class="title">Ingen bookinger endnu</div>
            <div class="meta">Når du køber billetter, dukker de op her.</div>
          </div>
          <div class="row__format">—</div>
          <div class="row__time">—</div>
        </div>
      </div>
      <div class="user-actions">
        <a class="btn btn--primary" href="<?= url('?page=home#today') ?>">Find forestillinger</a>
      </div>
    </article>

    <!-- Præferencer (dummy form klar til backend) -->
    <article class="user-card user-card--span2">
      <h2>Præferencer</h2>
      <form method="post" action="#" class="pref-form">
        <?= csrf_input() ?>
        <div class="pref-grid">
          <label>
            Foretrukket sprog<br>
            <select name="pref_lang">
              <option value="auto">Auto</option>
              <option>Dansk</option>
              <option>Engelsk</option>
            </select>
          </label>
          <label>
            Notifikationer<br>
            <select name="pref_notif">
              <option value="email">Email</option>
              <option value="none">Ingen</option>
            </select>
          </label>
        </div>
        <button class="btn btn--ghost" type="submit">Gem præferencer</button>
      </form>
    </article>
  </section>
</main>
