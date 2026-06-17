<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$sessionRole = strtolower(trim($_SESSION['authority_level'] ?? ''));
$currentSessionId = (int)($_SESSION['admin_id'] ?? 0);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");