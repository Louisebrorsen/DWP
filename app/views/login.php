<?php
require_once __DIR__ . '/../../includes/security.php';
require_once __DIR__ . '/../../includes/connection.php';
require_once __DIR__ . '/../../includes/helpers.php';

$login_error = '';
$login_debug = '';
$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = ($host === 'localhost' || str_starts_with($host, '127.'));

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['form'] ?? '') === 'login') {
    // CSRF (vil exit/419 ved fejl)
    verify_csrf();

    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $login_error = 'Udfyld både email og adgangskode.';
    } else {
        // Hent PDO handle
        $dbh = null;
        if (function_exists('db')) { $dbh = db(); }
        else { global $pdo; $dbh = $pdo ?? null; }

        if (!$dbh) {
            $login_error = 'Databaseforbindelse mangler.';
        } else {
            try {
                $stmt = $dbh->prepare('SELECT userID AS id, firstName, lastName, email, password AS password_hash
                                         FROM user
                                         WHERE LOWER(email) = :email
                                         LIMIT 1');
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                $ok = false;
                if ($user) {
                    // Brug kolonnen `password` som hash (den indeholder allerede bcrypt: $2y$...)
                    $hash = (string)($user['password_hash'] ?? '');
                    if ($hash !== '' && password_verify($password, $hash)) {
                        $ok = true;
                        // Rehash hvis algoritmen er forældet (opdaterer samme kolonne `password`)
                        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                            $newHash = password_hash($password, PASSWORD_DEFAULT);
                            $up = $dbh->prepare('UPDATE user SET password = :h WHERE userID = :id');
                            $up->execute([':h' => $newHash, ':id' => (int)$user['id']]);
                        }
                    }
                }

                if ($ok) {
                    // Sæt session
                    $_SESSION['member_id']    = (int)$user['id'];
                    $_SESSION['member_name']  = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
                    $_SESSION['member_email'] = $user['email'];
                    $_SESSION['flash'] = 'Velkommen tilbage!';

                    // Redirect til profil
                    $dest = function_exists('url') ? url('?page=profile') : '?page=profile';
                    header('Location: ' . $dest);
                    exit;
                } else {
                    $login_error = 'Forkert email eller adgangskode.';
                }
            } catch (Throwable $e) {
                $login_error = 'Kunne ikke logge ind lige nu.';
                if ($isLocal) { $login_debug = $e->getMessage(); }
            }
        }
    }
}
?>

<main class="container" style="padding:40px 20px; max-width:480px; margin:auto; text-align:left;">
  <h1>Login</h1>
  <p>Indtast din email og adgangskode for at logge ind.</p>

  <?php if (!empty($login_error)): ?>
    <div style="margin:12px 0; padding:12px; border-radius:10px; background:#402020; color:#ffd6d6;">
      <?= e($login_error) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($login_debug)): ?>
    <div style="margin:8px 0; padding:10px; border-radius:10px; background:#312a; color:#ffefef; border:1px dashed #ff9a9a;">
      <strong>Debug:</strong> <?= e($login_debug) ?>
    </div>
  <?php endif; ?>

  <form method="post" style="display:grid; gap:12px;">
    <input type="hidden" name="form" value="login">
    <?= csrf_input() ?>

    <label>
      Email<br>
      <input type="email" name="email" placeholder="email" required style="width:100%; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:rgba(0,0,0,.1); color:inherit;">
    </label>

    <label>
      Adgangskode<br>
      <input type="password" name="password" placeholder="adgangskode" required style="width:100%; padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,.2); background:rgba(0,0,0,.1); color:inherit;">
    </label>

    <button type="submit" class="btn btn--primary">Log ind</button>
  </form>

  <p style="margin-top:20px; font-size:14px; color:var(--muted);">
    Har du ikke en konto? <a href="<?= url('?page=register') ?>" class="btn btn--ghost" style="margin-left:6px;">Opret bruger</a>
  </p>
</main>