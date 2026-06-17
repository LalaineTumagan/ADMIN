<?php

// Subdivisions
$projects = $conn->query("
    SELECT *
    FROM subdivisions
    ORDER BY project_name ASC
");

// Residents + Latest Utility Bill
$resQuery = $conn->query("
    SELECT 
        r.resident_id,
        r.subdivision_id,
        s.project_name AS project,
        r.phase,
        r.block_no,
        r.lot_no,
        r.tct_no,
        r.buyer_name,
        r.new_buyer_assumed,
        r.buyer_representative,
        r.contact_no,
        r.social_media,
        r.email_address,
        r.account_number,
        r.account_address,
        r.resident_status,
        r.remarks,
        r.created_at,

        u.prev_reading,
        u.present_reading,
        u.total_bill,
        u.bill_status,
        u.remaining_balance,
        u.current_bill

    FROM residents r

    LEFT JOIN subdivisions s
        ON r.subdivision_id = s.subdivision_id

    LEFT JOIN (
        SELECT *
        FROM utility_bills
        WHERE bill_id IN (
            SELECT MAX(bill_id)
            FROM utility_bills
            GROUP BY resident_id
        )
    ) u
        ON r.resident_id = u.resident_id

    ORDER BY r.resident_id DESC
");

$residentsArray = [];

if ($resQuery) {
    while ($row = $resQuery->fetch_assoc()) {
        $residentsArray[] = $row;
    }
}