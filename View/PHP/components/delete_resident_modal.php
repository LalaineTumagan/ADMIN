<div id="deleteResidentModal" class="modal-overlay" style="z-index: 2000;">
        <div class="modal-container" style="max-width: 400px; text-align: center;">
            <div class="modal-top-bar" style="background: #991b1b;"><span>Confirm Deletion</span><button onclick="closeDeleteModal()">✕</button></div>
            <div class="modal-body" style="padding: 30px;">
                <p style="color: #f87171; font-weight: bold; margin-bottom: 20px;">This action cannot be undone. Enter Admin PIN to proceed.</p>
                <input type="password" id="adminPinInput" placeholder="Enter 4-Digit PIN" 
                       style="text-align: center; font-size: 24px; letter-spacing: 10px; padding: 10px; width: 100%; border-radius: 8px; background: #0f172a; color: white; border: 1px solid #ef4444;">
            </div>
            <div class="modal-footer" style="justify-content: center; gap: 10px;">
                <button type="button" class="btn-delete" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="danger-btn" onclick="processDeleteResident()" style="background: #ef4444;">Confirm Delete</button>
            </div>
        </div>
    </div>