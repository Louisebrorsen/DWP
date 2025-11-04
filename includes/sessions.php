<?php


require_once __DIR__ . '/validate.php';      // indeholder validate::isText/isEmail/isPassword
require_once __DIR__ . '/connection.php';    // giver db() → PDO
// optional: CSRF hvis du bruger det
// require_once __DIR__ . '/security.php';

// --- Validation compatibility shim ---
// If your project uses a class named `Validate` (capital V) or only functions,
// provide a `validate` class with the expected static methods.
if (!class_exists('validate')) {
  if (class_exists('Validate')) {
    // Alias: allow validate:: to call Validate::
    class validate extends Validate {}
  } else {
    // Minimal fallback implementation
    class validate {
      public static function isText($str, int $min = 1, int $max = 255): bool {
        $len = mb_strlen(trim((string)$str));
        return $len >= $min && $len <= $max;
      }
      public static function isEmail($email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
      }
      public static function isPassword($pw, int $min = 6, int $max = 100): bool {
        $len = mb_strlen((string)$pw);
        return $len >= $min && $len <= $max;
      }
    }
  }
}
// --- End shim ---

// Session start flyttes til controller (fx index.php) FØR output
// så vi undgår "headers already sent". Her antager vi, at controlleren
// starter sessionen, når det er nødvendigt (fx på register/login).
$logged_in = (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['logged_in'])) ? $_SESSION['logged_in'] : false;

// Init state (så vi undgår undefined index, og kan re-udfylde formular)
$member  = [
  'firstName' => '',
  'lastName'  => '',
  'email'     => '',
  'password'  => '',
];
$confirm = '';
$errors  = [
  'firstName' => '',
  'lastName'  => '',
  'email'     => '',
  'password'  => '',
  'confirm'   => '',
];
$result  = null;   // bruges til success/fejl besked fra DB

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
  // Hvis du bruger CSRF: verify_csrf();

  // 1) Hent POST sikkert og trim
  $member['firstName'] = trim($_POST['firstName'] ?? '');
  $member['lastName']  = trim($_POST['lastName']  ?? '');
  $member['email']     = trim($_POST['email']     ?? '');
  $member['password']  = (string)($_POST['password'] ?? '');
  $confirm             = (string)($_POST['confirm']  ?? '');
  $member['DOB']       = $_POST['DOB']   ?? null;  // forventer 'YYYY-MM-DD' eller tom
  $member['gender']    = $_POST['gender']?? null;  // 'M'/'F'/'U' (1 tegn)

  // 2) Valider (brug din validate-class)
  $errors['firstName'] = validate::isText($member['firstName'], 2, 50) ? '' : 'Fornavn skal være mellem 2 og 50 tegn.';
  $errors['lastName']  = validate::isText($member['lastName'],  2, 50) ? '' : 'Efternavn skal være mellem 2 og 50 tegn.';
  $errors['email']     = validate::isEmail($member['email'])            ? '' : 'Ugyldig email.';
  $errors['password']  = validate::isPassword($member['password'], 6, 100) ? '' : 'Adgangskode skal være mellem 6 og 100 tegn.';
  $errors['confirm']   = ($confirm === $member['password']) ? '' : 'Adgangskoderne stemmer ikke overens.';

  // 3) Er der fejl?
  $invalid = implode('', array_filter($errors)); // tom streng = ingen fejl

  // 4) Gem i DB hvis alt er OK
  if ($invalid === '') {
    $result = sessions_create_member($member);
    
    // Hvis OK → log ind (valgfrit) og ryd følsomme felter
    if (!empty($result['ok'])) {
      $_SESSION['logged_in'] = true;
      $_SESSION['member_email'] = $member['email'];
      // Ryd password fra memory
      $member['password'] = $confirm = '';
    } else {
      // Hvis fx duplicate email, læg fejl i email-feltet
      if (!empty($result['error_code']) && (int)$result['error_code'] === 1062) {
        $errors['email'] = 'Denne email er allerede registreret.';
      }
    }
  }
}

/**
 * Opretter medlem i DB
 * Forventet users-schema (tilpas til dit):
 *  users(id INT AI PK, first_name VARCHAR(50), last_name VARCHAR(50), email VARCHAR(190) UNIQUE,
 *        password_hash VARCHAR(255), created_at DATETIME)
 */
function sessions_create_member(array $member): array {
  try {
    $pdo = db();

    $hash = password_hash($member['password'], PASSWORD_DEFAULT);

    // Map optional fields
    $dob = !empty($member['DOB']) ? $member['DOB'] : null;       // allow NULL
    $gender = !empty($member['gender']) ? $member['gender'] : 'U'; // default Unknown

    $sql = "INSERT INTO user (firstName, lastName, DOB, email, password, gender)
            VALUES (:fn, :ln, :dob, :em, :ph, :gender)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ':fn'     => $member['firstName'],
      ':ln'     => $member['lastName'],
      ':dob'    => $dob,      // null or 'YYYY-MM-DD'
      ':em'     => $member['email'],
      ':ph'     => $hash,
      ':gender' => $gender,   // 'M' | 'F' | 'U' (1 char)
    ]);

    return ['ok' => true, 'id' => (int)$pdo->lastInsertId()];
  } catch (PDOException $e) {
    // Returnér detaljer til visning/log (du kan logge $e->getMessage())
    return [
      'ok' => false,
      'error' => $e->getMessage(),
      'error_code' => (int)$e->errorInfo[1] ?? 0 // fx 1062 for duplicate key
    ];
  }
}