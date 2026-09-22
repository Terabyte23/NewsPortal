<?php
require_once __DIR__ . '/../db.php';

$currentUser = get_logged_in_user($conn);

if (!is_editor_or_admin($currentUser)) {
    header("Location: ../login.php");
    exit;
}

// Helper to strictly verify CSRF for state-changing admin actions
function admin_verify_csrf() {
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        die("Turvakontroll ebaõnnestus (vigane või aegunud CSRF luba). Palun värskendage lehte ja proovige uuesti.");
    }
}
