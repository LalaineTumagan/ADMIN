<?php

// Total Resident Count
$resCount = $conn->query("
    SELECT COUNT(*) AS total
    FROM residents
");

$totalResidents = ($resCount)
    ? $resCount->fetch_assoc()['total']
    : 0;

// Active Resident Count
$activeCount = $conn->query("
    SELECT COUNT(*) AS total
    FROM residents
    WHERE resident_status = 'Active'
");

$activeResidents = ($activeCount)
    ? $activeCount->fetch_assoc()['total']
    : 0;

// Revenue Calculation
$moneyQuery = $conn->query("
    SELECT SUM(total_bill) AS total
    FROM utility_bills
");

$totalMoney = (
    $moneyQuery &&
    $row = $moneyQuery->fetch_assoc()
)
? ($row['total'] ?? 0)
: 0;