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
        $contact = $_POST['contact_no'] ?? '';

        // --- NEW: VALIDATION RESTRICTIONS ---
        if (preg_match('~[0-9]~', $name)) {
            echo json_encode(["success" => false, "message" => "Error: Client name cannot contain numbers."]);
            exit;
        }
        if (preg_match('/[a-zA-Z]/', $contact)) {
            echo json_encode(["success" => false, "message" => "Error: Contact number must contain digits only."]);
            exit;
        }

        // --- DUPLICATE CHECK LOGIC ---
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
    }
}

// --- 2. HANDLE FETCHING (PAGINATION LOGIC) ---
/**
 * 20,000 Residents require pagination to prevent browser freezing.
 */
$limit = 50; // Show 50 per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total count for the frontend to know how many pages exist
$total_result = $conn->query("SELECT COUNT(*) as total FROM residents");
$total_count = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_count / $limit);

$sql = "SELECT 
            r.*, 
            s.project_name, 
            u.electric_bill, 
            u.water_bill 
        FROM residents r
        LEFT JOIN subdivisions s ON r.subdivision_id = s.subdivision_id
        LEFT JOIN utility_bills u ON r.resident_id = u.resident_id
        ORDER BY r.resident_id DESC
        LIMIT $limit OFFSET $offset"; // Added Limit and Offset

$result = $conn->query($sql);
$residents = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $residents[] = $row;
    }
    echo json_encode([
        "success" => true, 
        "count" => count($residents),
        "total_count" => (int)$total_count,
        "current_page" => $page,
        "total_pages" => $total_pages,
        "residents" => $residents
    ]);
} else {
    echo json_encode([
        "success" => false, 
        "message" => "Database Query Error: " . $conn->error
    ]);
}
?>