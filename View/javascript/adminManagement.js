/**
 * adminManagement.js
 * Handles CRUD for System Administrators
 */

// Function to open the modal for adding a NEW admin
function openAddAdminModal() {
    const form = document.getElementById('adminAccountForm');
    if(form) form.reset();
    
    document.getElementById('modalAdminId').value = "";
    document.getElementById('adminModalTitle').innerText = "Register New Admin";
    
    // Corrected: Default to 'active' (matches the logic in your new Status dropdown)
    const statusSelect = document.getElementById('modalAdminStatus');
    if(statusSelect) statusSelect.value = "active";

    // Ensure the key field is empty and type is password
    const keyField = document.getElementById('modalAdminKey');
    if(keyField) {
        keyField.value = "";
        keyField.type = "password";
    }

    // Reset password field placeholder for new users
    const passField = document.getElementById('modalAdminPassword');
    if(passField) passField.placeholder = "Enter login password";

    document.getElementById('adminAccountModal').style.display = 'flex';
}

function editAdmin(adminData) {
    // 1. Change the Title
    document.getElementById('adminModalTitle').innerText = "Edit Admin: " + adminData.admin_name;

    // 2. FILL THE HIDDEN ID
    document.getElementById('modalAdminId').value = adminData.admin_id;

    // 3. FILL THE INPUTS
    document.getElementById('modalAdminName').value = adminData.admin_name;
    document.getElementById('modalAdminKey').value = adminData.auth_key; 
    
    // Set placeholder to show it's optional for edits
    const passField = document.getElementById('modalAdminPassword');
    if(passField) {
        passField.value = "";
        passField.placeholder = "Leave blank to keep current password";
    }

    // 4. SET THE DROPDOWN (Authority Level) - Matches 'Master' or 'Staff'
    const authLevelSelect = document.getElementById('modalAuthLevel');
    if(authLevelSelect) {
        authLevelSelect.value = adminData.authority_level;
    }

    // 5. SET THE STATUS DROPDOWN - Matches 'active' or 'deactivated'
    const statusSelect = document.getElementById('modalAdminStatus');
    if(statusSelect) {
        // Ensure we handle lowercase comparison from DB values
        const currentStatus = adminData.admin_status ? adminData.admin_status.toLowerCase() : 'active';
        statusSelect.value = currentStatus;
    }

    // 6. SHOW MODAL
    document.getElementById('adminAccountModal').style.display = 'flex';
}

function closeAdminModal() {
    document.getElementById('adminAccountModal').style.display = 'none';
}

// Save or Update Admin
function saveAdminAccount() {
    const id = document.getElementById('modalAdminId').value;
    const name = document.getElementById('modalAdminName').value;
    const newKeyToSave = document.getElementById('modalAdminKey').value; 
    const pass = document.getElementById('modalAdminPassword').value;
    const authLevel = document.getElementById('modalAuthLevel')?.value || 'Staff';
    const status = document.getElementById('modalAdminStatus')?.value || 'active';

    if (!name || !newKeyToSave) {
        alert("Please fill in Name and Auth Key.");
        return;
    }

    const formData = new FormData();
    formData.append('action', id ? 'update_admin' : 'add_admin');
    formData.append('admin_id', id);
    formData.append('admin_name', name);
    formData.append('auth_key_to_save', newKeyToSave); 
    formData.append('password', pass);
    formData.append('authority_level', authLevel);
    formData.append('admin_status', status);

    // Security: Pass the key as the verification 'auth_key'
    formData.append('auth_key', newKeyToSave); 

    fetch('admin_api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(id ? "Account updated successfully!" : "New admin registered!");
            location.reload(); 
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => {
        console.error("Fetch Error:", err);
        alert("System error. Check the network tab in inspect element.");
    });
}

/**
 * DELETION LOGIC WITH SECURITY VERIFICATION
 */

function confirmDeleteAdmin(id, name) {
    const modal = document.getElementById('adminSecurityModal');
    const targetText = document.getElementById('targetAdminNameText');
    const targetIdInput = document.getElementById('deleteTargetId');
    const verifyInput = document.getElementById('masterVerifyKey');

    if(modal && targetText && targetIdInput) {
        targetIdInput.value = id;
        targetText.innerText = name;
        verifyInput.value = ''; 
        modal.style.display = 'flex';
    }
}

function closeAdminSecurityModal() {
    document.getElementById('adminSecurityModal').style.display = 'none';
}

function executeVerifiedDelete() {
    const authKey = document.getElementById('masterVerifyKey').value;
    const adminId = document.getElementById('deleteTargetId').value;

    if (!authKey) {
        alert("Your Master Authorization Key is required to delete an account!");
        return;
    }

    // Final browser confirmation
    if (!confirm("Are you absolutely sure you want to delete this admin? This action is permanent.")) return;

    const formData = new FormData();
    formData.append('action', 'delete_admin');
    formData.append('admin_id', adminId);
    formData.append('auth_key', authKey); 

    fetch('admin_api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Admin deleted successfully.");
            location.reload(); 
        } else {
            alert("Verification Failed: " + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("An error occurred. Check the console.");
    });
}

/**
 * UI HELPERS
 */

function toggleField(fieldId, btn) {
    const field = document.getElementById(fieldId);
    if (!field) return;

    if (field.type === "password") {
        field.type = "text";
        btn.innerText = "HIDE";
        btn.style.color = "#ef4444";
    } else {
        field.type = "password";
        btn.innerText = "SHOW";
        btn.style.color = "#64748b";
    }
}

// Example: Adding an Admin
function handleAddAdmin(event) {
    event.preventDefault();
    
    // 1. Show the loader
    showSystemLoader("Registering New Admin...");

    const formData = new FormData(event.target);
    formData.append('action', 'add_admin');

    fetch('admin_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Success! The page will likely reload, so the loader stays visible
            window.location.reload(); 
        } else {
            hideSystemLoader(); // Hide if there's a validation error
            alert(data.message || "Error adding admin");
        }
    })
    .catch(err => {
        hideSystemLoader();
        console.error(err);
    });
}