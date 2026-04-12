<?php
include('db_connect.php');
header('Content-Type: application/json');

// --- 1. HANDLE SAVING (POST REQUESTS) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'ADD_RESIDENT' || $action === 'UPDATE_RESIDENT') {
        $sub_id  = $_POST['subdivision_id'];
        $blk     = trim($_POST['block_no']);
        $lot     = trim($_POST['lot_no']);
        $res_id  = $_POST['resident_id'] ?? 0;
        $name    = $_POST['resident_name'] ?? 'Unknown';

        // --- DUPLICATE CHECK LOGIC ---
        // We look for any OTHER resident with the same Sub/Block/Lot
        $check_sql = "SELECT resident_name FROM residents 
                      WHERE subdivision_id = ? 
                      AND block_no = ? 
                      AND lot_no = ? 
                      AND resident_id != ?";
        
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("issi", $sub_id, $blk, $lot, $res_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_row = $check_result->fetch_assoc()) {
            echo json_encode([
                "success" => false, 
                "message" => "DUPLICATE: Block $blk, Lot $lot is already registered to " . $check_row['resident_name']
            ]);
            exit;
        }
        
        // If no duplicate, proceed with your existing INSERT/UPDATE logic here...
        // (Insert your SQL execution code for saving here)
    }
}

// --- 2. HANDLE FETCHING (GET REQUESTS / DEFAULT) ---
/**
 * We join:
 * 1. residents (r)
 * 2. subdivisions (s) - to get the 'project_name'
 * 3. utility_bills (u) - to get 'electric_bill' and 'water_bill'
 */
$sql = "SELECT 
            r.*, 
            s.project_name, 
            u.electric_bill, 
            u.water_bill 
        FROM residents r
        LEFT JOIN subdivisions s ON r.subdivision_id = s.subdivision_id
        LEFT JOIN utility_bills u ON r.resident_id = u.resident_id
        ORDER BY r.resident_id DESC";

$result = $conn->query($sql);
$residents = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $residents[] = $row;
    }
    echo json_encode([
        "success" => true, 
        "count" => count($residents),
        "residents" => $residents
    ]);
} else {
    echo json_encode([
        "success" => false, 
        "message" => "Database Query Error: " . $conn->error
    ]);
}
?>