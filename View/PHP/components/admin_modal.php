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
