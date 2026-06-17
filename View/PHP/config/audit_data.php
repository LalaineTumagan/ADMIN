<?php

$audit_logs = $conn->query("
    SELECT
        l.log_id,
        l.admin_id,
        l.action_type,
        l.details,
        l.timestamp,
        a.admin_name

    FROM admin_logs l

    LEFT JOIN admins a
        ON l.admin_id = a.admin_id

    ORDER BY l.timestamp DESC
    LIMIT 100
");

$auditLogsArray = [];

if ($audit_logs && $audit_logs->num_rows > 0) {

    while ($row = $audit_logs->fetch_assoc()) {
        $auditLogsArray[] = $row;
    }

    $audit_logs->data_seek(0);
}