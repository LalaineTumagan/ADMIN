<?php

include('config/auth.php');
include('db_connect.php');

include('config/dashboard_stats.php');
include('config/resident_data.php');
include('config/audit_data.php');
include('config/admin_data.php');

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

    <header class="topbar">
        <div class="topbar-title">IMPERIAL HOUSE</div>
    </header>

    <main class="main-content">

        <?php include 'pages/dashboard.php'; ?>
        <?php include 'pages/residents.php'; ?>
        <?php include 'pages/reports.php'; ?>
        <?php include 'pages/admins.php'; ?>

    </main>

    <!-- MODALS -->
    <?php include 'components/property_modal.php'; ?>
    <?php include 'components/resident_modal.php'; ?>
    <?php include 'components/edit_resident_modal.php'; ?>
    <?php include 'components/delete_resident_modal.php'; ?>
    <?php include 'components/admin_modal.php'; ?>
    <?php include 'components/logout_modal.php'; ?>

    <script>
        window.residents = <?php echo json_encode($residentsArray ?? []); ?>;
        window.auditLogs = <?php echo json_encode($auditLogsArray ?? []); ?>;

        console.log(
            "System Ready: " +
            (window.residents ? window.residents.length : 0) +
            " residents and " +
            (window.auditLogs ? window.auditLogs.length : 0) +
            " logs loaded."
        );

        function toggleLocationDropdown() {
            document.getElementById("locationDropdown")
                .classList.toggle("open");
        }

        function selectLocation(value, label) {
            document.getElementById("locationSelect").value = value;
            document.getElementById("locationSelectedText").innerText = label;

            document.getElementById("locationDropdown")
                .classList.remove("open");
        }

        document.addEventListener("click", function(e) {
            const dropdown =
                document.getElementById("locationDropdown");

            if (dropdown && !dropdown.contains(e.target)) {
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