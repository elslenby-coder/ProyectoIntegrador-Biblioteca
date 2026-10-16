
<?php

ini_set('session.use_strict_mode', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !isset($_SESSION['admin_id']) ||
    !is_numeric($_SESSION['admin_id'])
) {
    header('Location: login.php');
    exit;
}
