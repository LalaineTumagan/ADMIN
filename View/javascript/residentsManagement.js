/**
 * Imperial House - Residents Management Module (Enhanced with Duplicate Check)
 */
document.addEventListener("DOMContentLoaded", () => {
    if (!window.residents) window.residents = [];

    const searchInput = document.getElementById("residentSearch");
    const addForm = document.getElementById("addResidentForm");
    const editForm = document.getElementById("editResidentForm");

    // --- NEW: Helper to capitalize Block/Lot (handles 1a -> 1A) ---
    const formatBlockLot = (e) => {
        e.target.value = e.target.value.toUpperCase();
    };

    // Attach to all block/lot inputs
    ['addBlock', 'addLot', 'editBlock', 'editLot'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', formatBlockLot);
    });

    // ==========================================
    // 1. Render Table Logic (WITH PAGINATION)
    // ==========================================
    let currentPage = 1;
    const rowsPerPage = 30;

    function renderResidentsTable(filter = "") {
        const tbody = document.getElementById("residentsTableBody");
        if (!tbody) return;

        const searchTerm = filter.toLowerCase().trim();

        const filteredResidents = window.residents
            .map((res, index) => ({ ...res, originalIndex: index }))
            .filter(res => {
                const name = String(res.buyer_name || "").toLowerCase();
                const tct = String(res.tct_no || "").toLowerCase();
                const acc = String(res.account_number || "").toLowerCase();
                const projectID = String(res.subdivision_id || "").toLowerCase();
                const projectName = String(res.project || "").toLowerCase();
                
                return name.includes(searchTerm) || tct.includes(searchTerm) || 
                    acc.includes(searchTerm) || projectID.includes(searchTerm) ||
                    projectName.includes(searchTerm);
            });

        const totalPages = Math.ceil(filteredResidents.length / rowsPerPage);
        if (currentPage > totalPages) currentPage = totalPages || 1;

        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginatedItems = filteredResidents.slice(start, end);

        tbody.innerHTML = "";

        if (paginatedItems.length === 0) {
            tbody.innerHTML = `<tr><td colspan="18" style="text-align:center; padding: 40px; color: #94a3b8;">No residents found.</td></tr>`;
            renderPaginationControls(0, 0); 
            return;
        }

        paginatedItems.forEach(res => {
            const tr = document.createElement("tr");
            const statusVal = res.resident_status || "Active";
            const statusClass = statusVal.toLowerCase().replace(/\s+/g, "-");
            const displayProject = res.project || res.subdivision_id || "-";

            tr.innerHTML = `
                <td>${res.resident_id}</td>
                <td>${displayProject}</td>
                <td>${res.phase || "-"}</td>
                <td>${res.block_no || "-"}</td>
                <td>${res.lot_no || "-"}</td>
                <td>${res.tct_no || "-"}</td>
                <td style="font-weight: 700; color: #f8fafc;">${res.buyer_name || "N/A"}</td>
                <td><span class="status-tag ${statusClass}">${statusVal}</span></td>
                <td>
                    <div style="display: flex; gap: 5px;">
                        <button class="btn-edit" onclick="editResident(${res.originalIndex})">Edit</button>
                        <button class="btn-delete" onclick="deleteResident(${res.originalIndex})" style="background:#991b1b;">Del</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        renderPaginationControls(currentPage, totalPages);
    }

    function renderPaginationControls(current, total) {
        let controls = document.getElementById("paginationControls");
        if (!controls) {
            controls = document.createElement("div");
            controls.id = "paginationControls";
            controls.style = "display: flex; justify-content: center; align-items: center; gap: 20px; margin-top: 20px; padding: 15px;";
            const tableParent = document.querySelector(".table-container");
            if(tableParent) tableParent.after(controls);
        }
        if (total <= 1) { controls.innerHTML = ""; return; }

        controls.innerHTML = `
            <button class="btn-edit" ${current === 1 ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''} 
                onclick="window.changePage(${current - 1})">Previous</button>
            <span style="color: #94a3b8;">Page <b>${current}</b> of ${total}</span>
            <button class="btn-edit" ${current === total ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''} 
                onclick="window.changePage(${current + 1})">Next</button>
        `;
    }

    window.changePage = function(newPage) {
        currentPage = newPage;
        const currentSearch = searchInput ? searchInput.value : "";
        renderResidentsTable(currentSearch);
        const tableContainer = document.querySelector(".table-container");
        if(tableContainer) tableContainer.scrollTop = 0;
    };

    // ==========================================
    // 2. Add Resident Logic (UPDATED FOR DUPLICATE CHECK)
    // ==========================================
    if (addForm) {
        addForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const submitBtn = addForm.querySelector('button[type="submit"]');
            if (submitBtn.disabled) return;
            
            submitBtn.disabled = true;
            submitBtn.innerText = "Saving...";

            const formData = new FormData(addForm);
            formData.append('action', 'ADD_RESIDENT');

            fetch('process_resident.php', {
                method: 'POST',
                body: formData // Using FormData is better for the check logic
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    location.reload(); 
                } else {
                    // This alerts the user if "Block/Lot is already owned"
                    alert("⚠️ " + data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerText = "Save Resident";
                }
            })
            .catch(err => {
                console.error(err);
                submitBtn.disabled = false;
                submitBtn.innerText = "Save Resident";
            });
        });
    }

    // ==========================================
    // 3. Edit Resident Logic (UPDATED)
    // ==========================================
    window.editResident = function (index) {
        const res = window.residents[index];
        if (!res) return;

        try {
            document.getElementById("editResidentId").value = res.resident_id || "";
            document.getElementById("editProject").value = res.subdivision_id || "";
            document.getElementById("editTct").value = res.tct_no || "";
            document.getElementById("editPhase").value = res.phase || "";
            document.getElementById("editBlock").value = res.block_no || "";
            document.getElementById("editLot").value = res.lot_no || "";
            document.getElementById("editCreatedAt").value = res.created_at || "";
            document.getElementById("editName").value = res.buyer_name || "";
            document.getElementById("editNewBuyer").value = res.new_buyer_assumed || "";
            document.getElementById("editRep").value = res.buyer_representative || "";
            document.getElementById("editAccountNo").value = res.account_number || "";
            document.getElementById("editContact").value = res.contact_no || "";
            document.getElementById("editEmail").value = res.email_address || "";
            document.getElementById("editSocial").value = res.social_media || "";
            document.getElementById("editAccountAddress").value = res.account_address || "";
            document.getElementById("editStatus").value = res.resident_status || "Active";
            document.getElementById("editRemarks").value = res.remarks || "";

            document.getElementById("editResidentModal").classList.add("show");
        } catch (err) {
            console.error("Mapping Error:", err);
        }
    };

    if (editForm) {
        editForm.addEventListener("submit", function(e) {
            e.preventDefault();
            const formData = new FormData(editForm);
            formData.append('action', 'UPDATE_RESIDENT');
            formData.append('resident_id', document.getElementById("editResidentId").value);

            fetch('process_resident.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) location.reload();
                else alert("⚠️ " + data.message);
            });
        });
    }

    // ==========================================
    // 4. Delete Logic
    // ==========================================
    let deleteResidentId = null; 
    
    window.deleteResident = function (index) {
        const res = window.residents[index];
        if (!res) return;
        deleteResidentId = res.resident_id; 

        const pinInput = document.getElementById("adminPinInput");
        if (pinInput) {
            pinInput.value = "";
            setTimeout(() => pinInput.focus(), 200); 
        }
        document.getElementById("deleteResidentModal").classList.add("show");
    };

    window.processDeleteResident = function () {
        const pinInput = document.getElementById("adminPinInput");
        if (!deleteResidentId || !pinInput || !pinInput.value) {
            alert("Please enter the Admin PIN");
            return;
        }

        const confirmBtn = document.querySelector("#deleteResidentModal button[onclick='processDeleteResident()']");
        if (confirmBtn) confirmBtn.disabled = true;

        fetch('process_resident.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                action: 'delete', 
                id: deleteResidentId,
                admin_pin: pinInput.value 
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                pinInput.value = ""; 
                window.closeDeleteModal();
                showToast("Resident successfully deleted", "success");
                setTimeout(() => { location.reload(); }, 1000); 
            } else {
                alert(data.message || "Invalid Admin PIN");
                pinInput.value = ""; 
                pinInput.focus();
                if (confirmBtn) confirmBtn.disabled = false;
            }
        });
    };

    // ==========================================
    // 5. Global Modal Control Utilities
    // ==========================================
    window.openResidentForm = () => {
        document.getElementById("addResidentModal").classList.add("show");
    };

    window.closeAddModal = () => {
        const modal = document.getElementById("addResidentModal");
        modal.classList.remove("show");
        if(addForm) addForm.reset();
    };

    window.closeEditModal = () => {
        document.getElementById("editResidentModal").classList.remove("show");
    };
    
    window.closeDeleteModal = () => {
        document.getElementById("deleteResidentModal").classList.remove("show");
        document.getElementById("adminPinInput").value = "";
    };

    function showToast(message, type = "success") {
        const toast = document.createElement("div");
        toast.className = `toast show`;
        toast.style.cssText = `position:fixed; bottom:20px; right:20px; padding:12px 24px; border-radius:8px; color:white; z-index:10000; font-weight:600; background:${type === 'success' ? '#10b981' : '#ef4444'}`;
        toast.innerText = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = "0";
            toast.style.transition = "opacity 0.3s ease";
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // ==========================================
    // 6. Map-to-Modal Bridge
    // ==========================================
    window.openLotModal = function(projectID, block, lot) {
        const searchID = String(projectID).trim();
        const searchBlock = String(block).trim();
        const searchLot = String(lot).trim();

        const residentIndex = window.residents.findIndex(r => {
            const dbProjectID = String(r.subdivision_id || "").trim();
            const dbBlock = String(r.block_no || "").trim();
            const dbLot = String(r.lot_no || "").trim();
            return dbProjectID === searchID && dbBlock === searchBlock && dbLot === searchLot;
        });

        if (residentIndex !== -1) {
            const res = window.residents[residentIndex];
            document.getElementById("infoResId").innerText = res.resident_id || "---";
            document.getElementById("infoAddress").innerText = res.project || res.subdivision_id || "N/A";
            document.getElementById("infoProperty").innerText = `Phase ${res.phase || '1'} | Blk ${res.block_no} Lot ${res.lot_no}`;
            document.getElementById("infoTct").innerText = res.tct_no || "---";
            document.getElementById("infoClient").innerText = res.buyer_name || "Unassigned";
            document.getElementById("infoNewBuyer").innerText = res.new_buyer_assumed || "None";
            document.getElementById("infoRep").innerText = res.buyer_representative || "---";
            document.getElementById("infoContact").innerText = res.contact_no || "---";
            document.getElementById("infoEmail").innerText = res.email_address || "---";
            document.getElementById("infoSocial").innerText = res.social_media || "---";
            document.getElementById("infoAccAddress").innerText = res.account_address || "---";
            document.getElementById("infoAccNo").innerText = res.account_number || "---";
            document.getElementById("infoTotalBill").innerText = res.total_bill ? `₱ ${Number(res.total_bill).toLocaleString()}` : "₱ 0.00";
            document.getElementById("infoRemarks").innerText = res.remarks || "No additional remarks.";
            document.getElementById("infoCreated").innerText = res.created_at || "-";

            const statusEl = document.getElementById("infoStatus");
            const status = (res.resident_status || "Active");
            statusEl.innerText = status;
            statusEl.style.backgroundColor = status === 'Active' ? '#065f46' : '#991b1b';
            
            const editBtn = document.getElementById("infoEditBtn");
            if (editBtn) {
                editBtn.onclick = function() {
                    if(typeof window.closeMarkerModal === "function") window.closeMarkerModal();
                    window.editResident(residentIndex); 
                };
            }
            const overlay = document.getElementById("modalOverlay");
            if(overlay) overlay.classList.add("show");

        } else {
            if(addForm) addForm.reset();
            const addProj = document.getElementById("addProject");
            if(addProj) addProj.value = projectID;
            document.getElementById("addBlock").value = block;
            document.getElementById("addLot").value = lot;
            window.openResidentForm();
        }
    };

    document.addEventListener('keydown', (e) => {
        if (e.key === "Escape") {
            closeAddModal();
            closeEditModal();
            closeDeleteModal();
        }
    });

    if (searchInput) {
        searchInput.addEventListener("input", e => {
            currentPage = 1;
            renderResidentsTable(e.target.value);
        });
    }

    renderResidentsTable();
});