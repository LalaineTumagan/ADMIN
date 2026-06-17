<?php

$admins = $conn->query("
    SELECT
        admin_id,
        admin_name,
        authority_level,
        admin_status,
        auth_key
    FROM admins
    ORDER BY admin_id ASC
");

// Permission Checker
$iAmMaster = (
    $sessionRole === 'master'
);