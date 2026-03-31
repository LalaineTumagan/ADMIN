// --- DEFINE THESE AT THE TOP (Global Scope) ---
let auditCurrentPage = 1; 
const auditRowsPerPage = 10; 
let filteredAuditData = [];

// 1. Updated Filter Logic to handle Year, Action, Admin, and Search
function filterAuditLog() {
    auditCurrentPage = 1; 
    
    const yearVal = document.getElementById("filterYear").value;
    const actionVal = document.getElementById("filterAction").value.toUpperCase();
    const adminVal = document.getElementById("filterAdmin").value.toLowerCase();
    const searchVal = document.getElementById("auditSearch").value.toLowerCase().trim();

    const filteredLogs = window.auditLogs.filter(log => {
        const logYear = new Date(log.timestamp).getFullYear().toString();
        const logAdmin = String(log.display_name || "").toLowerCase();
        const logAction = String(log.action_type || "").toUpperCase();
        const logDetails = String(log.details || "").toLowerCase();

        const matchesYear = yearVal === "" || logYear === yearVal;
        const matchesAction = actionVal === "" || logAction === actionVal;
        const matchesAdmin = adminVal === "" || logAdmin === adminVal;
        const matchesSearch = searchVal === "" || logDetails.includes(searchVal);

        return matchesYear && matchesAction && matchesAdmin && matchesSearch;
    });

    renderAuditTableWithData(filteredLogs);
}

// 2. Modified Render Function
function renderAuditTableWithData(dataList) {
    const tbody = document.getElementById("auditLogBody");
    if (!tbody) return;

    // PAGINATION MATH
    const totalPages = Math.ceil(dataList.length / auditRowsPerPage);
    const start = (auditCurrentPage - 1) * auditRowsPerPage; 
    const end = start + auditRowsPerPage;
    const paginatedLogs = dataList.slice(start, end);

    tbody.innerHTML = "";

    if (paginatedLogs.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 40px; color: #94a3b8;">No matching logs found.</td></tr>`;
        if (typeof renderAuditPagination === "function") renderAuditPagination(0, 0);
        return;
    }

    paginatedLogs.forEach(log => {
        const tr = document.createElement("tr");
        const rawAction = String(log.action_type || "").toUpperCase();
        
        // --- UPDATED COLOR CODING LOGIC ---
        let actionClass = "login"; // Default (Gray/Dark)

        if (['DELETED', 'DELETE', 'REMOVE', 'DELETE_ADMIN'].includes(rawAction)) {
            actionClass = "delete"; // RED
        } 
        else if (['CREATED', 'ADD', 'CREATE', 'INSERT', 'CREATE_ADMIN', 'ADDED'].includes(rawAction)) {
            actionClass = "create"; // GREEN
        } 
        else if (['UPDATED', 'EDIT', 'UPDATE', 'MODIFIED', 'UPDATE_ADMIN'].includes(rawAction)) {
            actionClass = "update"; // BLUE (Ensure your CSS .update class is blue)
        }

        tr.innerHTML = `
            <td style="color: #64748b;">#${log.log_id}</td>
            <td>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="admin-badge">ID: ${log.admin_id}</span>
                    <strong style="color: #f8fafc; font-size: 13px;">${log.display_name || 'System'}</strong>
                </div>
            </td>
            <td><span class="action-tag ${actionClass}">${rawAction}</span></td>
            <td class="details-cell" style="max-width: 400px; color: #cbd5e1; line-height: 1.5;">${log.details}</td>
            <td class="time-cell">${log.timestamp}</td>
        `;
        tbody.appendChild(tr);
    });

    if (typeof renderAuditPagination === "function") {
        renderAuditPagination(auditCurrentPage, totalPages);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    if (window.auditLogs) {
        filterAuditLog(); 
    }
});