<?php
/**
 * 1. SESSION & CACHE SECURITY
 */
session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Global Role Normalization (Fixes button visibility case-sensitivity)
$sessionRole = isset($_SESSION['authority_level']) ? strtolower(trim($_SESSION['authority_level'])) : '';
$currentSessionId = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 0;

// Security: Prevent browser back-button caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/**
 * 2. DATABASE INITIALIZATION
 */
include('db_connect.php');

/**
 * 3. ANALYTICS & DASHBOARD STATS
 */
// Total Resident Count
$resCount = $conn->query("SELECT COUNT(*) as total FROM residents");
$totalResidents = ($resCount) ? $resCount->fetch_assoc()['total'] : 0;

// Active Resident Count
$activeCount = $conn->query("SELECT COUNT(*) as total FROM residents WHERE resident_status = 'Active'");
$activeResidents = ($activeCount) ? $activeCount->fetch_assoc()['total'] : 0;

// Revenue Calculation (Utility Bills)
$moneyQuery = $conn->query("SELECT SUM(total_bill) as total FROM utility_bills");
$totalMoney = ($moneyQuery && $row = $moneyQuery->fetch_assoc()) ? ($row['total'] ?? 0) : 0;

/**
 * 4. DATA FETCHING (SUBDIVISIONS & RESIDENTS)
 */
// Subdivisions for dropdowns
$projects = $conn->query("SELECT * FROM subdivisions ORDER BY project_name ASC");

// Comprehensive Resident & Latest Utility Data (17 Fields)
$resQuery = $conn->query("
    SELECT 
        r.resident_id, r.subdivision_id, s.project_name as project, 
        r.phase, r.block_no, r.lot_no, r.tct_no,
        r.buyer_name, r.new_buyer_assumed, r.buyer_representative,
        r.contact_no, r.social_media, r.email_address, 
        r.account_number, r.account_address,
        r.resident_status, r.remarks, r.created_at,
        u.prev_reading, u.present_reading, u.total_bill, u.bill_status, u.remaining_balance, u.current_bill
    FROM residents r 
    LEFT JOIN subdivisions s ON r.subdivision_id = s.subdivision_id
    LEFT JOIN (
        SELECT * FROM utility_bills WHERE bill_id IN (
            SELECT MAX(bill_id) FROM utility_bills GROUP BY resident_id
        )
    ) u ON r.resident_id = u.resident_id
    ORDER BY r.resident_id DESC
");

$residentsArray = [];
if ($resQuery) {
    while($row = $resQuery->fetch_assoc()) { 
        $residentsArray[] = $row; 
    }
}

/**
 * 5. SYSTEM LOGS & ADMINISTRATIVE DATA
 */
// Audit Logs (Latest 100 actions)
$audit_logs = $conn->query("
    SELECT 
        l.log_id, l.admin_id, l.action_type, l.details, l.timestamp, 
        a.admin_name 
    FROM admin_logs l
    LEFT JOIN admins a ON l.admin_id = a.admin_id 
    ORDER BY l.timestamp DESC 
    LIMIT 100
");

$auditLogsArray = [];
if ($audit_logs && $audit_logs->num_rows > 0) {
    while($row = $audit_logs->fetch_assoc()) {
        $auditLogsArray[] = $row;
    }
    $audit_logs->data_seek(0); // Reset pointer for HTML rendering
}

// Administrative Users Management
$admins = $conn->query("SELECT admin_id, admin_name, authority_level, admin_status, auth_key FROM admins ORDER BY admin_id ASC");

/**
 * 6. SYSTEM UTILITIES
 */
function insert_audit_log($conn, $admin_name, $action_type, $module, $details) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action_type, details) VALUES ((SELECT admin_id FROM admins WHERE admin_name = ? LIMIT 1), ?, ?)");
    $stmt->bind_param("sss", $admin_name, $action_type, $details);
    return $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imperial House - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>

    <div id="leftMenu" class="left-menu">
        <div class="left-menu-header">
            <div class="left-menu-title">Navigation</div>
        </div>

        <ul class="left-menu-nav">
            <li class="left-menu-item active" data-page="dashboard">DASHBOARD</li>
            <li class="left-menu-item" data-page="residents">RESIDENTS</li>
            <li class="left-menu-item" data-page="admins">ADMIN MANAGEMENT</li>
            <li class="left-menu-item" data-page="reports">REPORT</li>

            <li class="left-menu-item logout-item" onclick="confirmLogout(event)">LOG OUT</li>
        </ul>
    </div>

    <header class="topbar"><div class="topbar-title">IMPERIAL HOUSE</div></header>

    <main class="main-content">
        <section id="section-dashboard" class="app-page active">
            <div class="stats-ribbon">
                <div class="stat-card">
                    <div class="stat-label">Global Residents</div>
                    <div class="stat-value"><?php echo number_format($totalResidents); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Active Connections</div>
                    <div class="stat-value"><?php echo number_format($activeResidents); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Receivables</div>
                    <div class="stat-value">₱ <?php echo number_format($totalMoney, 2); ?></div>
                </div>
            </div>

            <div class="location-selector-section">
                <h2 class="section-title">Project Selection</h2>

                <div class="selector-wrapper" style="display: flex; gap: 15px; margin-top: 15px;">

                    <!-- CUSTOM DROPDOWN -->
                    <div class="custom-dropdown" id="locationDropdown">

                        <div class="dropdown-selected" onclick="toggleLocationDropdown()">
                            <span id="locationSelectedText">-- Select Project --</span>
                            <span>▼</span>
                        </div>

                        <div class="dropdown-menu">

                            <!-- default option -->
                            <div class="dropdown-item"
                                onclick="selectLocation('', '-- Select Project --')">
                                -- Select Project --
                            </div>

                            <?php 
                            $projects->data_seek(0); 
                            while($p = $projects->fetch_assoc()): ?>

                                <div class="dropdown-item"
                                    onclick="selectLocation(
                                        '<?= $p['subdivision_id'] ?>',
                                        '<?= htmlspecialchars($p['project_name']) ?>'
                                    )">
                                    <?= htmlspecialchars($p['project_name']) ?>
                                </div>

                            <?php endwhile; ?>

                        </div>

                        <input type="hidden" id="locationSelect" name="subdivision_id">
                        <input type="hidden" id="projectName">

                    </div>

                    <button onclick="handleLocationChange()" class="btn-load">Load Project</button>

                </div>
            </div>

            <div class="map-wrapper" style="margin-top: 30px;">
                <div id="map-controls" style="display: none; justify-content: flex-end; margin-bottom: 10px;">
                    <button class="danger-btn" onclick="window.closeMapSection()" style="background:#ef4444; color:white; border:none; padding: 8px 16px; border-radius:8px; cursor:pointer;">✖ Close Map</button>
                </div>
                <div id="mapContainer" style="display:none; height: 550px; border-radius: 15px; z-index: 1;"></div>
            </div>

            <div id="project-analytics-box" style="margin-top: 30px; background: #1e293b; padding: 25px; border-radius: 15px; border: 1px solid #334155;">
                <h3 id="analytics-title" style="color: #d49006; margin-bottom: 20px; font-weight: 700; text-transform: uppercase;">Project Analytics Overview</h3>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 300px; height: 300px; background: #161e2e; padding: 15px; border-radius: 10px;"><canvas id="populationChart"></canvas></div>
                    <div style="flex: 1; min-width: 300px; height: 300px; background: #161e2e; padding: 15px; border-radius: 10px;"><canvas id="billingChart"></canvas></div>
                </div>
            </div>
        </section>

        <section id="section-residents" class="app-page">
            <div class="page-header" style="margin-bottom: 25px;"><h2>Residents Management</h2></div>
            <div class="residents-toolbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <button class="primary-btn" onclick="openResidentForm()">+ Add Resident</button>
                <input type="text" id="residentSearch" placeholder="Search by name, TCT, or account..." class="search-input">
            </div>
            <div class="residents-table-wrapper">
                <table class="residents-table">
                    <thead>
                        <tr>
                            <th>ID</th><th>Subdivision</th><th>Phase</th><th>Block</th><th>Lot</th>
                            <th>TCT No.</th><th>Buyer Name</th><th>Status</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="residentsTableBody"></tbody>
                </table>
            </div>
        </section>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-container" style="max-width: 650px;">
            <div class="modal-top-bar">
                <span id="modalTitle">Property Detail View</span>
                <button class="close-x" id="modalClose" onclick="window.closeMarkerModal()">✕</button>
            </div>
            <div id="markerModalContent" style="position: relative; max-height: 80vh; overflow-y: auto; background: #1e293b; color: white;">
                <div class="accent-bar" style="height: 4px; background: #d49006;"></div>
                <div class="details-section" style="padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 15px;">
                        <div>
                            <span style="color: #94a3b8; font-size: 10px; font-weight: bold; text-transform: uppercase; display: block;">Primary Buyer</span>
                            <h2 id="infoClient" style="margin: 0; color: #f8fafc; font-size: 20px;">-</h2>
                            <span id="infoStatus" class="status-tag" style="margin-top: 5px; display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">-</span>
                        </div>
                        <div style="text-align: right;">
                            <span style="color: #94a3b8; font-size: 10px; font-weight: bold; text-transform: uppercase; display: block;">Resident ID</span>
                            <span id="infoResId" style="font-weight: 700; color: #d49006;">-</span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">SUBDIVISION / PROJECT</label><span id="infoAddress" style="font-size: 13px;">-</span></div>
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">TCT NUMBER</label><span id="infoTct" style="font-size: 13px;">-</span></div>
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">PHASE / BLK / LOT</label><span id="infoProperty" style="font-size: 13px;">-</span></div>
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">CONTACT NO.</label><span id="infoContact" style="font-size: 13px;">-</span></div>
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">EMAIL ADDRESS</label><span id="infoEmail" style="font-size: 13px;">-</span></div>
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">SOCIAL MEDIA</label><span id="infoSocial" style="font-size: 13px;">-</span></div>
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">NEW BUYER / ASSUMED</label><span id="infoNewBuyer" style="font-size: 13px;">-</span></div>
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">REPRESENTATIVE</label><span id="infoRep" style="font-size: 13px;">-</span></div>
                        <div style="grid-column: span 2;"><label style="color: #94a3b8; font-size: 10px; display: block;">ACCOUNT ADDRESS</label><span id="infoAccAddress" style="font-size: 13px;">-</span></div>
                    </div>

                    <div style="background: #0f172a; padding: 15px; border-radius: 8px; border: 1px solid #334155; margin-bottom: 20px;">
                        <h3 style="font-size: 11px; color: #d49006; margin-top: 0; margin-bottom: 12px; text-transform: uppercase;">Latest Billing Summary</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div><label style="color: #64748b; font-size: 9px; display: block;">ACCOUNT NO.</label><span id="infoAccNo" style="font-size: 13px; font-weight: 600;">-</span></div>
                            <div><label style="color: #64748b; font-size: 9px; display: block;">BILL STATUS</label><span id="infoBillStatus" style="font-size: 13px; font-weight: bold;">-</span></div>
                            <div><label style="color: #64748b; font-size: 9px; display: block;">OUTSTANDING BAL.</label><span id="infoTotalBill" style="font-size: 16px; font-weight: bold; color: #fb7185;">₱ 0.00</span></div>
                            <div><label style="color: #64748b; font-size: 9px; display: block;">LAST UPDATE</label><span id="infoCreated" style="font-size: 13px;">-</span></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="color: #94a3b8; font-size: 10px; display: block;">REMARKS</label>
                        <p id="infoRemarks" style="font-size: 12px; color: #cbd5e1; font-style: italic; margin-top: 5px;">-</p>
                    </div>

                    <button id="infoEditBtn" class="primary-btn" style="width: 100%; padding: 12px; font-weight: bold;">Go to Management Profile</button>
                </div>
            </div>
        </div>
    </div>

    <div id="addResidentModal" class="modal-overlay">
        <div class="modal-container" style="max-width: 800px;">
            <div class="modal-top-bar"><span>New Resident Registration</span><button type="button" onclick="closeAddModal()">✕</button></div>
            <form id="addResidentForm">
                <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                    <div class="modal-section-title">Property Details</div>
                    <div class="form-grid"> 
                        <div>
                            <label>Subdivision Project</label>
                            <select id="addProject" name="subdivision_id" required>
                                <option value="" disabled selected>Select Project</option>
                                <?php $projects->data_seek(0); while($p = $projects->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($p['subdivision_id']); ?>"><?php echo $p['project_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div><label>TCT Number</label><input type="text" id="addTct" name="tct_no"></div>
                    </div>
                    <div style="display: flex; gap: 15px; margin-top: 10px; flex-wrap: wrap;">
                        <div style="flex: 1;"><label>Phase</label><input type="text" id="addPhase" name="phase"></div>
                        <div style="flex: 1;"><label>Block No.</label><input type="text" id="addBlock" name="block_no" style="text-transform: uppercase;" required></div>
                        <div style="flex: 1;"><label>Lot No.</label><input type="text" id="addLot" name="lot_no" style="text-transform: uppercase;" required></div>
                    </div>

                    <div class="modal-section-title">Ownership Information</div>
                    <div class="form-grid">
                        <div>
                            <label>Primary Buyer Name</label>
                            <input type="text" id="addName" name="buyer_name" 
                                   pattern="^[a-zA-Z\sñÑ.]+$" 
                                   title="Numbers are not allowed in the name." 
                                   required>
                        </div>
                        <div><label>Account Number</label><input type="text" id="addAccountNo" name="account_number"></div>
                    </div>
                    <div class="form-grid" style="margin-top: 10px;">
                        <div><label>New Buyer Assumed</label><input type="text" id="addNewBuyer" name="new_buyer_assumed" pattern="^[a-zA-Z\sñÑ.]+$" title="Numbers are not allowed."></div>
                        <div><label>Buyer Representative</label><input type="text" id="addRep" name="buyer_representative" pattern="^[a-zA-Z\sñÑ.]+$" title="Numbers are not allowed."></div>
                    </div>
                    <label style="margin-top:10px;">Account/Billing Address</label>
                    <input type="text" id="addAccountAddress" name="account_address">

                    <div class="modal-section-title">Contact & Communication</div>
                    <div class="form-grid">
                        <div>
                            <label>Contact No.</label>
                            <input type="text" id="addContact" name="contact_no" 
                                   oninput="this.value = this.value.replace(/[^0-9+]/g, '')" 
                                   placeholder="09123456789">
                        </div>
                        <div><label>Email Address</label><input type="email" id="addEmail" name="email_address"></div>
                    </div>
                    <label style="margin-top:10px;">Social Media (FB/Messenger)</label>
                    <input type="text" id="addSocial" name="social_media">

                    <div class="modal-section-title">System Status & Remarks</div>
                    <div class="form-grid">
                        <div>
                            <label>Resident Status</label>
                            <select id="addStatus" name="resident_status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Moved Out">Moved Out</option>
                            </select>
                        </div>
                        <div><label>Registration Date</label><input type="date" id="addCreatedAt" name="created_at" value="<?php echo date('Y-m-d'); ?>"></div>
                    </div>
                    <label style="margin-top:10px;">Internal Remarks</label>
                    <textarea id="addRemarks" name="remarks" placeholder="Add any specific notes..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-delete" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="primary-btn">Register Resident</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editResidentModal" class="modal-overlay">
        <div class="modal-container" style="max-width: 800px;">
            <div class="modal-top-bar"><span>Edit Resident Information</span><button type="button" onclick="closeEditModal()">✕</button></div>
            <form id="editResidentForm">
                <input type="hidden" id="editResidentId" name="resident_id">
                <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <div class="modal-section-title">Property Details</div>
                    <div class="form-grid">
                        <div>
                            <label>Subdivision Project</label>
                            <select id="editProject" name="subdivision_id" required>
                                <option value="" disabled selected>Select Project</option>
                                <?php $projects->data_seek(0); while($p = $projects->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($p['subdivision_id']); ?>"><?php echo $p['project_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div><label>TCT Number</label><input type="text" id="editTct" name="tct_no"></div>
                        <div><label>Phase</label><input type="text" id="editPhase" name="phase"></div>
                        <div><label>Block No.</label><input type="text" id="editBlock" name="block_no" style="text-transform: uppercase;" required></div>
                        <div><label>Lot No.</label><input type="text" id="editLot" name="lot_no" style="text-transform: uppercase;" required></div>
                        <div><label>Created Date</label><input type="date" id="editCreatedAt" name="created_at"></div>
                    </div>

                    <div class="modal-section-title">Ownership & Assumption</div>
                    <div class="form-grid">
                        <div>
                            <label>Primary Buyer Name</label>
                            <input type="text" id="editName" name="buyer_name" 
                                   pattern="^[a-zA-Z\sñÑ.]+$" 
                                   title="Numbers are not allowed in the name." 
                                   required>
                        </div>
                        <div>
                            <label>New Buyer / Assumed By</label>
                            <input type="text" id="editNewBuyer" name="new_buyer_assumed" 
                                   pattern="^[a-zA-Z\sñÑ.]+$" 
                                   title="Numbers are not allowed.">
                        </div>
                        <div>
                            <label>Buyer Representative</label>
                            <input type="text" id="editRep" name="buyer_representative" 
                                   pattern="^[a-zA-Z\sñÑ.]+$" 
                                   title="Numbers are not allowed.">
                        </div>
                        <div><label>Account Number</label><input type="text" id="editAccountNo" name="account_number"></div>
                    </div>

                    <div class="modal-section-title">Contact Information</div>
                    <div class="form-grid">
                        <div>
                            <label>Contact No.</label>
                            <input type="text" id="editContact" name="contact_no" 
                                   oninput="this.value = this.value.replace(/[^0-9+]/g, '')" 
                                   placeholder="09123456789">
                        </div>
                        <div><label>Email Address</label><input type="email" id="editEmail" name="email_address"></div>
                        <div><label>Social Media (Link/Handle)</label><input type="text" id="editSocial" name="social_media"></div>
                        <div><label>Account Address</label><input type="text" id="editAccountAddress" name="account_address"></div>
                    </div>
                    
                    <div class="modal-section-title">Resident Status & Remarks</div>
                    <div class="form-grid">
                        <div>
                            <label>Resident Status</label>
                            <select id="editStatus" name="resident_status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Moved Out">Moved Out</option>
                            </select>
                        </div>
                        <div><label>Remarks</label><textarea id="editRemarks" name="remarks"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer" style="justify-content: space-between;">
                    <button type="button" class="danger-btn" onclick="openDeleteConfirmation()" style="background: #991b1b; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;">Delete Resident</button>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="btn-delete" onclick="closeEditModal()" style="background: #475569;">Cancel</button>
                        <button type="submit" class="primary-btn">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteResidentModal" class="modal-overlay" style="z-index: 2000;">
        <div class="modal-container" style="max-width: 400px; text-align: center;">
            <div class="modal-top-bar" style="background: #991b1b;"><span>Confirm Deletion</span><button onclick="closeDeleteModal()">✕</button></div>
            <div class="modal-body" style="padding: 30px;">
                <p style="color: #f87171; font-weight: bold; margin-bottom: 20px;">This action cannot be undone. Enter Admin PIN to proceed.</p>
                <input type="password" id="adminPinInput" placeholder="Enter 4-Digit PIN" 
                       style="text-align: center; font-size: 24px; letter-spacing: 10px; padding: 10px; width: 100%; border-radius: 8px; background: #0f172a; color: white; border: 1px solid #ef4444;">
            </div>
            <div class="modal-footer" style="justify-content: center; gap: 10px;">
                <button type="button" class="btn-delete" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="danger-btn" onclick="processDeleteResident()" style="background: #ef4444;">Confirm Delete</button>
            </div>
        </div>
    </div>

        <section id="section-reports" class="app-page">
    <div class="page-header" style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h2 style="color: #d49006; margin-bottom: 5px;">System Audit Log</h2>
            <p style="color: #64748b; font-size: 13px; margin: 0;">Track all administrative changes and system activities.</p>
        </div>
        
        <button onclick="exportAuditLog()" 
                style="padding: 10px 18px; background: #d49006; color: #0f172a; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 13px; transition: 0.2s;">
            Export CSV
        </button>
    </div>

    <div class="audit-toolbar" style="display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; align-items: flex-end; background: #1e293b; padding: 15px; border-radius: 10px; border: 1px solid #334155;">
        
        <div class="filter-group">
            <label style="display:block; font-size:10px; color:#d49006; font-weight:800; margin-bottom:5px; text-transform:uppercase; letter-spacing:1px;">Search Details</label>
            <input type="text" id="auditSearch" onkeyup="filterAuditLog()" placeholder="Search content..." 
                   style="padding: 8px 12px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; color: #f8fafc; font-size: 13px; width: 200px; outline: none;">
        </div>

        <div class="filter-group">
            <label style="display:block; font-size:10px; color:#d49006; font-weight:800; margin-bottom:5px; text-transform:uppercase; letter-spacing:1px;">Admin</label>
            <select id="filterAdmin" onchange="filterAuditLog()" 
                    style="padding: 8px 12px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; color: #f8fafc; font-size: 13px; width: 140px; outline: none;">
                <option value="">All Admins</option>
                <?php 
                // Dynamically populate admin names for the filter dropdown
                if(isset($admins)):
                    $admins->data_seek(0);
                    while($adm = $admins->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($adm['admin_name']); ?>">
                            <?php echo htmlspecialchars($adm['admin_name']); ?>
                        </option>
                <?php endwhile; endif; ?>
            </select>
        </div>

        <div class="filter-group">
            <label style="display:block; font-size:10px; color:#d49006; font-weight:800; margin-bottom:5px; text-transform:uppercase; letter-spacing:1px;">Action Type</label>
            <select id="filterAction" onchange="filterAuditLog()" 
                    style="padding: 8px 12px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; color: #f8fafc; font-size: 13px; width: 130px; outline: none;">
                <option value="">All Actions</option>
                <option value="CREATE">CREATE</option>
                <option value="UPDATE">UPDATE</option>
                <option value="DELETE">DELETE</option>
                <option value="LOGIN">LOGIN</option>
            </select>
        </div>

        <div class="filter-group">
            <label style="display:block; font-size:10px; color:#d49006; font-weight:800; margin-bottom:5px; text-transform:uppercase; letter-spacing:1px;">Year</label>
            <select id="filterYear" onchange="filterAuditLog()" 
                    style="padding: 8px 12px; background: #0f172a; border: 1px solid #334155; border-radius: 6px; color: #f8fafc; font-size: 13px; width: 100px; outline: none;">
                <option value="">All Years</option>
                <?php for($y = date("Y"); $y >= 2024; $y--): ?>
                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <button onclick="filterAuditLog()" class="btn-load" style="height: 35px; padding: 0 20px; font-size: 11px;">Reset View</button>
    </div>
    <div class="audit-table-wrapper">
        <table class="audit-log-table" id="auditLogTable">
            <thead>
                <tr>
                    <th style="width: 80px;">Log ID</th>
                    <th>Admin Details</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody id="auditLogBody">
                <?php if (!$audit_logs || $audit_logs->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 80px; color: #64748b;">
                            No activity logs found in the database.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

    <section id="section-admins" class="app-page">
    <div class="page-header">
        <div class="header-info">
            <h2>Admin Management</h2>
            <p>Master admins can manage all accounts. Staff can only edit their own profile.</p>
        </div>
        
        <?php 
        // Normalize role for comparison
        $sessionRole = isset($_SESSION['authority_level']) ? strtolower(trim($_SESSION['authority_level'])) : '';
        $iAmMaster = ($sessionRole === 'master');

        if($iAmMaster): 
        ?>
        <button type="button" class="btn-add-admin" onclick="openAddAdminModal()">
            <i class="fas fa-plus"></i> Register New Admin
        </button>
        <?php endif; ?>
    </div>

    <div class="admin-list-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Access Level</th>
                    <th>Status</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="adminTableBody">
                <?php if(isset($admins) && $admins->num_rows > 0): ?>
                    <?php 
                    $admins->data_seek(0); 
                    while($row = $admins->fetch_assoc()): 
                        $rowId = (int)$row['admin_id'];
                        $sessId = (int)$_SESSION['admin_id'];
                        
                        $isMe = ($rowId === $sessId);
                        $rowLevelClean = strtolower(trim($row['authority_level']));
                        
                        // UI Classes based on your DB values
                        $levelClass = ($rowLevelClean === 'master') ? 'level-master' : 'level-staff';
                        
                        // FIXED STATUS LOGIC: Checking for 'active' instead of 'master'
                        $status = $row['admin_status'] ?? 'Deactivated'; 
                        $statusClass = (strtolower(trim($status)) === 'active') ? 'status-active' : 'status-inactive';

                        // Safe JSON for Edit Function
                        $adminJson = json_encode([
                            'admin_id' => $row['admin_id'],
                            'admin_name' => $row['admin_name'],
                            'auth_key' => $row['auth_key'],
                            'authority_level' => $row['authority_level'], 
                            'admin_status' => $status
                        ]);
                    ?>
                    <tr>
                        <td class="admin-id-badge">#<?php echo $row['admin_id']; ?></td>
                        <td class="admin-name-cell">
                            <strong><?php echo htmlspecialchars($row['admin_name']); ?></strong>
                            <?php if($isMe): ?>
                                <span style="color: #3b82f6; font-size: 10px; font-weight: bold; text-transform: uppercase; margin-left: 5px;">(You)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="level-tag <?php echo $levelClass; ?>">
                                <?php echo htmlspecialchars($row['authority_level']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($status); ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 10px; justify-content: center;">
                                <?php if ($iAmMaster || $isMe): ?>
                                    <button type="button" class="btn-edit-admin" onclick='editAdmin(<?php echo htmlspecialchars($adminJson, ENT_QUOTES, 'UTF-8'); ?>)'>
                                        Edit
                                    </button>
                                    
                                    <?php if ($iAmMaster && !$isMe): ?>
                                        <button type="button" class="btn-delete-admin" 
                                                onclick="confirmDeleteAdmin(<?php echo $row['admin_id']; ?>, '<?php echo addslashes($row['admin_name']); ?>')">
                                            Delete
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: #64748b; font-size: 11px;">Restricted</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; padding: 30px;">No administrators found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<div id="adminAccountModal" class="modal-overlay">
    <div class="admin-modal-content">
        <div class="admin-modal-header">
            <h3 id="adminModalTitle">Register New Admin</h3>
        </div>
        
        <form id="adminAccountForm" onsubmit="event.preventDefault(); saveAdminAccount();">
            <input type="hidden" id="modalAdminId">
            
            <div class="admin-form-group">
                <label>Username</label>
                <input type="text" id="modalAdminName" class="admin-form-input" required>
            </div>

            <div class="admin-form-group">
                <label>Account Status</label>
                <select id="modalAdminStatus" class="admin-form-input">
                    <option value="active">Active</option>
                    <option value="deactivated">Deactivated</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label>Authority Level</label>
                <select id="modalAuthLevel" class="admin-form-input" <?php echo (!$iAmMaster) ? 'disabled' : ''; ?>>
                    <option value="Staff">Staff</option>
                    <option value="Master">Master</option>
                </select>
            </div>
            
            <div class="admin-form-group">
                <label>Master PIN / Auth Key</label>
                <div style="display: flex; gap: 5px;">
                    <input type="password" id="modalAdminKey" class="admin-form-input" maxlength="6" required>
                    <button type="button" onclick="toggleField('modalAdminKey', this)" style="background:none; border:1px solid #334155; color:#94a3b8; border-radius:4px; padding:0 10px; cursor:pointer;">SHOW</button>
                </div>
            </div>

            <div class="admin-form-group">
                <label>Login Password</label>
                <input type="password" id="modalAdminPassword" class="admin-form-input" placeholder="New password or leave blank">
            </div>

            <div class="admin-modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAdminModal()">Cancel</button>
                <button type="submit" class="btn-save-admin">Save Account</button>
            </div>
        </form>
    </div>
</div>

<div id="adminSecurityModal" class="modal-overlay">
    <div class="admin-modal-content" style="max-width: 400px;">
        <div class="admin-modal-header">
            <h3 style="color: #ef4444;">Confirm Admin Deletion</h3>
        </div>
        <div style="padding: 20px;">
            <p>You are about to permanently delete admin: <b id="targetAdminNameText"></b></p>
            <p style="font-size: 13px; color: #64748b; margin-top: 10px;">To proceed, please enter <b>YOUR</b> Master Authorization Key:</p>
            
            <input type="hidden" id="deleteTargetId">
            
            <div class="admin-form-group" style="margin-top: 15px;">
                <input type="password" id="masterVerifyKey" class="admin-form-input" placeholder="Enter Master Key" maxlength="6">
            </div>
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="btn-cancel" onclick="closeAdminSecurityModal()">Cancel</button>
            <button type="button" class="btn-save-admin" style="background: #ef4444;" onclick="executeVerifiedDelete()">Delete Permanent</button>
        </div>
    </div>
</div>



<div id="logoutModal" class="modal-overlay" style="display:none; position:fixed; inset:0; background:rgba(15, 23, 42, 0.9); backdrop-filter:blur(5px); z-index:10001; justify-content:center; align-items:center;">
    <div style="background:#1e293b; border:1px solid #334155; padding:30px; border-radius:15px; width:350px; text-align:center; box-shadow: 0 20px 50px rgba(0,0,0,0.5);">
        <div style="font-size: 40px; margin-bottom: 15px;">🚪</div>
        <h3 style="color:#f8fafc; margin-bottom:10px; font-weight:800; text-transform:uppercase; letter-spacing:1px;">Confirm Logout</h3>
        <p style="color:#94a3b8; font-size:14px; margin-bottom:25px;">Are you sure you want to end your session?</p>
        
        <div style="display:flex; gap:10px;">
            <button onclick="closeLogoutModal()" style="flex:1; padding:12px; background:#334155; color:#f8fafc; border:none; border-radius:8px; cursor:pointer; font-weight:700; transition:0.2s;">CANCEL</button>
            <button onclick="processLogout()" style="flex:1; padding:12px; background:#d49006; color:#0f172a; border:none; border-radius:8px; cursor:pointer; font-weight:800; transition:0.2s;">LOG OUT</button>
        </div>
    </div>
</div>


    <script>
    // Residents Data (Fail-safe: defaults to empty array if null)
    window.residents = <?php echo json_encode($residentsArray ?? []); ?>;
    
    // Audit Logs Data (Using the processed array from your PHP update)
    window.auditLogs = <?php echo json_encode($auditLogsArray ?? []); ?>;
    
    // Debugging Console - Helps verify data load on page start
    console.log("System Ready: " + 
        (window.residents ? window.residents.length : 0) + " residents and " + 
        (window.auditLogs ? window.auditLogs.length : 0) + " logs loaded."
    );

    // Dropdown Event
    function toggleLocationDropdown() {
        document.getElementById("locationDropdown").classList.toggle("open");
    }

    function selectLocation(value, label) {
        document.getElementById("locationSelect").value = value;
        document.getElementById("locationSelectedText").innerText = label;

        document.getElementById("locationDropdown").classList.remove("open");
    }

    // close dropdown when clicking outside
    document.addEventListener("click", function(e) {
        const dropdown = document.getElementById("locationDropdown");

        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove("open");
        }
    });
</script>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="get_maps_data.php"></script> 

<script src="../javascript/marker.js"></script>
<script src="../javascript/ui_utils.js"></script>
<script src="../javascript/mapModal.js"></script>
<script src="../javascript/residentsManagement.js"></script>
<script src="../javascript/adminManagement.js"></script>
<script src="../javascript/auditReports.js"></script>
<script src="../javascript/projectAnalytics.js"></script>
<script src="../javascript/menu.js"></script> 
<script src="../javascript/map.js"></script>
<script src="../javascript/logOut.js"></script>


</body>
</html>