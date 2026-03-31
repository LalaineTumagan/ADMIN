<?php
// 1. PRODUCTION ERROR HANDLING & BUFFERING
error_reporting(E_ALL);
ini_set('display_errors', 0); // Hide from browser to keep JSON clean
ob_start();

include('db_connect.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * record_audit: Logs administrative actions into the database.
 */
function record_audit($conn, $admin_name, $action_type, $details) {
    $admin_id = $_SESSION['admin_id'] ?? 1; 

    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action_type, details) VALUES (?, ?, ?)");
    
    if (!$stmt) {
        error_log("Audit Prepare Failed: " . $conn->error);
        return false;
    }

    $stmt->bind_param("iss", $admin_id, $action_type, $details);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

// Get the input data once at the top
$data = json_decode(file_get_contents("php://input"), true);
$output = ["success" => false, "message" => "Server processed nothing"];

if ($data) {
    $action = $data['action'] ?? '';
    $current_admin = $_SESSION['admin_name'] ?? 'System';

    // ACTION: EDIT
    if ($action === 'edit' && isset($data['id'])) {
        $sql = "UPDATE residents SET subdivision_id=?, tct_no=?, phase=?, block_no=?, lot_no=?, buyer_name=?, account_number=?, contact_no=?, email_address=?, resident_status=?, remarks=? WHERE resident_id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("issssssssssi", 
                $data['subdivision_id'], $data['tct_no'], $data['phase'], $data['block_no'], $data['lot_no'], 
                $data['buyer_name'], $data['account_number'], $data['contact_no'], 
                $data['email_address'], $data['resident_status'], $data['remarks'], $data['id']
            );
            if ($stmt->execute()) {
                $output = ["success" => true];
                record_audit($conn, $current_admin, 'UPDATED', "Modified: " . $data['buyer_name']);
            } else {
                $output["message"] = $stmt->error;
            }
            $stmt->close();
        }
    }

    // ACTION: ADD
    else if ($action === 'add') {
        $sql = "INSERT INTO residents (subdivision_id, tct_no, phase, block_no, lot_no, buyer_name, new_buyer_assumed, buyer_representative, contact_no, email_address, social_media, account_number, account_address, resident_status, remarks, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("isssssssssssssss", 
                $data['subdivision_id'], $data['tct_no'], $data['phase'], $data['block_no'], $data['lot_no'], 
                $data['buyer_name'], $data['new_buyer_assumed'], $data['buyer_representative'], 
                $data['contact_no'], $data['email_address'], $data['social_media'], 
                $data['account_number'], $data['account_address'], $data['resident_status'], 
                $data['remarks'], $data['created_at']
            );
            if ($stmt->execute()) {
                $output = ["success" => true];
                record_audit($conn, $current_admin, 'CREATED', "Added: " . $data['buyer_name']);
            } else {
                $output["message"] = $stmt->error;
            }
            $stmt->close();
        }
    }

    // ACTION: DELETE
    else if ($action === 'delete') {
        $currentAdminId = $_SESSION['admin_id'] ?? 0;
        $providedPin = $data['admin_pin'] ?? '';
        $residentId = $data['id'] ?? 0;

        // 1. Fetch the name using 'buyer_name' (matches your ADD/EDIT columns)
        $getName = $conn->prepare("SELECT buyer_name FROM residents WHERE resident_id = ?");
        $getName->bind_param("i", $residentId);
        $getName->execute();
        $nameRes = $getName->get_result();
        $resident = $nameRes->fetch_assoc();
        $getName->close();

        if (!$resident) {
            $output = ["success" => false, "message" => "Resident not found (ID: $residentId)"];
        } else {
            $residentName = $resident['buyer_name'];

            // 2. Security Check
            $auth = $conn->prepare("SELECT admin_name FROM admins WHERE admin_id = ? AND auth_key = ?");
            $auth->bind_param("is", $currentAdminId, $providedPin);
            $auth->execute();
            $authRes = $auth->get_result();
            
            if ($row = $authRes->fetch_assoc()) {
                // 3. Perform Delete
                $del = $conn->prepare("DELETE FROM residents WHERE resident_id = ?");
                $del->bind_param("i", $residentId);
                
                if ($del->execute()) {
                    $auditDetail = "Deleted Resident: " . $residentName . " (ID: " . $residentId . ")";
                    record_audit($conn, $row['admin_name'], 'DELETED', $auditDetail);
                    $output = ["success" => true];
                } else {
                    $output = ["success" => false, "message" => "Database delete failed."];
                }
                $del->close();
            } else {
                $output = ["success" => false, "message" => "Invalid PIN. Use your own Master Key."];
            }
            $auth->close();
        }
    }
}

// FINAL CLEANUP
while (ob_get_level()) { ob_end_clean(); }
header('Content-Type: application/json');
echo json_encode($output);
$conn->close();
exit;   