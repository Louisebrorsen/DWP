<?php
// Session & CSRF
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function csrf_input(): string {
  return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function verify_csrf(): void {
  if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $ok = isset($_POST['csrf'], $_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf']);
    if (!$ok) { http_response_code(419); exit('CSRF validation failed'); }
  }
}