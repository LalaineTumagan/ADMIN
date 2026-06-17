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