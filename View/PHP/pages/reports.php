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