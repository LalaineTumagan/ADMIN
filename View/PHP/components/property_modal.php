<div class="modal-overlay" id="modalOverlay">
        <div class="modal-container" style="max-width: 650px;">
            <div class="modal-top-bar">
                <span id="modalTitle">Property Detail View</span>
                <button class="close-x" id="modalClose" onclick="window.closeMarkerModal()">✕</button>
            </div>
            <div id="markerModalContent" style="position: relative; max-height: 80vh; overflow-y: auto; background: #1e293b; color: white;">
                <div class="accent-bar" style="height: 4px; background: #d49006;"></div>
                <div class="details-section" style="padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 1px solid #334155; padding-bottom: 15px;">
                        <div>
                            <span style="color: #94a3b8; font-size: 10px; font-weight: bold; text-transform: uppercase; display: block;">Primary Buyer</span>
                            <h2 id="infoClient" style="margin: 0; color: #f8fafc; font-size: 20px;">-</h2>
                            <span id="infoStatus" class="status-tag" style="margin-top: 5px; display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">-</span>
                        </div>
                        <div style="text-align: right;">
                            <span style="color: #94a3b8; font-size: 10px; font-weight: bold; text-transform: uppercase; display: block;">Resident ID</span>
                            <span id="infoResId" style="font-weight: 700; color: #d49006;">-</span>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">SUBDIVISION / PROJECT</label><span id="infoAddress" style="font-size: 13px;">-</span></div>
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">TCT NUMBER</label><span id="infoTct" style="font-size: 13px;">-</span></div>
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">PHASE / BLK / LOT</label><span id="infoProperty" style="font-size: 13px;">-</span></div>
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">CONTACT NO.</label><span id="infoContact" style="font-size: 13px;">-</span></div>
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">EMAIL ADDRESS</label><span id="infoEmail" style="font-size: 13px;">-</span></div>
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">SOCIAL MEDIA</label><span id="infoSocial" style="font-size: 13px;">-</span></div>
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">NEW BUYER / ASSUMED</label><span id="infoNewBuyer" style="font-size: 13px;">-</span></div>
                        <div><label style="color: #94a3b8; font-size: 10px; display: block;">REPRESENTATIVE</label><span id="infoRep" style="font-size: 13px;">-</span></div>
                        <div style="grid-column: span 2;"><label style="color: #94a3b8; font-size: 10px; display: block;">ACCOUNT ADDRESS</label><span id="infoAccAddress" style="font-size: 13px;">-</span></div>
                    </div>

                    <div style="background: #0f172a; padding: 15px; border-radius: 8px; border: 1px solid #334155; margin-bottom: 20px;">
                        <h3 style="font-size: 11px; color: #d49006; margin-top: 0; margin-bottom: 12px; text-transform: uppercase;">Latest Billing Summary</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <div><label style="color: #64748b; font-size: 9px; display: block;">ACCOUNT NO.</label><span id="infoAccNo" style="font-size: 13px; font-weight: 600;">-</span></div>
                            <div><label style="color: #64748b; font-size: 9px; display: block;">BILL STATUS</label><span id="infoBillStatus" style="font-size: 13px; font-weight: bold;">-</span></div>
                            <div><label style="color: #64748b; font-size: 9px; display: block;">OUTSTANDING BAL.</label><span id="infoTotalBill" style="font-size: 16px; font-weight: bold; color: #fb7185;">₱ 0.00</span></div>
                            <div><label style="color: #64748b; font-size: 9px; display: block;">LAST UPDATE</label><span id="infoCreated" style="font-size: 13px;">-</span></div>
                        </div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="color: #94a3b8; font-size: 10px; display: block;">REMARKS</label>
                        <p id="infoRemarks" style="font-size: 12px; color: #cbd5e1; font-style: italic; margin-top: 5px;">-</p>
                    </div>

                    <button id="infoEditBtn" class="primary-btn" style="width: 100%; padding: 12px; font-weight: bold;">Go to Management Profile</button>
                </div>
            </div>
        </div>
</div>