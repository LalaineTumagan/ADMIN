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