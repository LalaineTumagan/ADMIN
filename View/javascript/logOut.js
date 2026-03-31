/**
 * logOut.js - Resident Management System
 */

function confirmLogout() {
    // 1. Prevent the menu from switching to a blank "logout" page
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    // 2. Show our custom modal instead of the browser alert
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeLogoutModal() {
    const modal = document.getElementById('logoutModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function processLogout() {
    closeLogoutModal();
    showLogoutLoading("Ending Session...");
    
    setTimeout(() => {
        window.location.href = 'logout.php';
    }, 800);
}

// Re-using your existing loader function
function showLogoutLoading(message) {
    let overlay = document.getElementById('system-loader-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'system-loader-overlay';
        Object.assign(overlay.style, {
            position: 'fixed',
            inset: '0',
            background: 'rgba(15, 23, 42, 0.95)',
            display: 'flex',
            flexDirection: 'column',
            justifyContent: 'center',
            alignItems: 'center',
            zIndex: '10002',
            backdropFilter: 'blur(10px)'
        });

        overlay.innerHTML = `
            <style>
                @keyframes spinGold { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
                .gold-spinner {
                    width: 50px; height: 50px;
                    border: 5px solid #334155;
                    border-bottom-color: #d49006;
                    border-radius: 50%;
                    animation: spinGold 1s linear infinite;
                    margin-bottom: 20px;
                }
            </style>
            <div class="gold-spinner"></div>
            <div style="color: #d49006; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; font-size: 13px;">${message}</div>
        `;
        document.body.appendChild(overlay);
    }
    overlay.style.display = 'flex';
}