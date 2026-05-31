<?php // admin/logout.php
require_once __DIR__ . '/../includes/security.php';
start_session();
$_SESSION = [];
session_destroy();
header('Location: /admin/');
exit;
