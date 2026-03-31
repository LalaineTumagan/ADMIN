/**
 * ui_utils.js - Imperial House Global UI Utilities
 * Reusable components for Resident and Admin Management
 */

/**
 * 1. Global System Loader
 * Shows a Midnight & Gold loading overlay
 */
function showSystemLoader(message = "Processing...") {
    let overlay = document.getElementById('global-system-loader');
    
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'global-system-loader';
        
        Object.assign(overlay.style, {
            position: 'fixed',
            inset: '0',
            background: 'rgba(15, 23, 42, 0.95)',
            display: 'none',
            flexDirection: 'column',
            justifyContent: 'center',
            alignItems: 'center',
            zIndex: '30000', // Higher than any modals
            backdropFilter: 'blur(10px)',
            transition: 'opacity 0.3s ease',
            fontFamily: 'sans-serif'
        });

        overlay.innerHTML = `
            <style>
                @keyframes spinGold { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
                .loader-spinner {
                    width: 50px; height: 50px;
                    border: 5px solid #1e293b;
                    border-bottom-color: #d49006;
                    border-radius: 50%;
                    animation: spinGold 1s linear infinite;
                    margin-bottom: 20px;
                }
            </style>
            <div class="loader-spinner"></div>
            <div id="loader-message" style="color: #d49006; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; font-size: 13px; text-align: center;">
                ${message}
            </div>
        `;
        document.body.appendChild(overlay);
    }
    
    document.getElementById('loader-message').innerText = message;
    overlay.style.display = 'flex';
    overlay.style.opacity = '1';
}

/**
 * Hides the loader with a smooth fade-out
 */
function hideSystemLoader() {
    const overlay = document.getElementById('global-system-loader');
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => {
            overlay.style.display = 'none';
        }, 300);
    }
}

/**
 * 2. Global Toast Notification
 * Use: showToast("Resident Added Successfully!");
 */
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    
    Object.assign(toast.style, {
        position: 'fixed',
        top: '30px',
        right: '30px',
        background: type === 'success' ? '#1e293b' : '#450a0a',
        color: type === 'success' ? '#d49006' : '#f87171',
        padding: '15px 30px',
        borderRadius: '10px',
        borderLeft: `5px solid ${type === 'success' ? '#d49006' : '#ef4444'}`,
        boxShadow: '0 15px 30px rgba(0,0,0,0.4)',
        zIndex: '40000',
        fontWeight: '800',
        fontSize: '12px',
        letterSpacing: '1.5px',
        fontFamily: 'sans-serif',
        textTransform: 'uppercase',
        transition: 'all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275)',
        transform: 'translateX(120%)'
    });

    toast.innerText = message;
    document.body.appendChild(toast);

    // Slide in after a tiny delay
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);

    // Slide out and remove after 3 seconds
    setTimeout(() => {
        toast.style.transform = 'translateX(150%)';
        setTimeout(() => toast.remove(), 500);
    }, 3000);
} ww