<div id="addResidentModal" class="modal-overlay">
        <div class="modal-container" style="max-width: 800px;">
            <div class="modal-top-bar"><span>New Resident Registration</span><button type="button" onclick="closeAddModal()">✕</button></div>
            <form id="addResidentForm">
                <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
                    <div class="modal-section-title">Property Details</div>
                    <div class="form-grid"> 
                        <div>
                            <label>Subdivision Project</label>
                            <select id="addProject" name="subdivision_id" required>
                                <option value="" disabled selected>Select Project</option>
                                <?php $projects->data_seek(0); while($p = $projects->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($p['subdivision_id']); ?>"><?php echo $p['project_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div><label>TCT Number</label><input type="text" id="addTct" name="tct_no"></div>
                    </div>
                    <div style="display: flex; gap: 15px; margin-top: 10px; flex-wrap: wrap;">
                        <div style="flex: 1;"><label>Phase</label><input type="text" id="addPhase" name="phase"></div>
                        <div style="flex: 1;"><label>Block No.</label><input type="text" id="addBlock" name="block_no" style="text-transform: uppercase;" required></div>
                        <div style="flex: 1;"><label>Lot No.</label><input type="text" id="addLot" name="lot_no" style="text-transform: uppercase;" required></div>
                    </div>

                    <div class="modal-section-title">Ownership Information</div>
                    <div class="form-grid">
                        <div>
                            <label>Primary Buyer Name</label>
                            <input type="text" id="addName" name="buyer_name" 
                                   pattern="^[a-zA-Z\sñÑ.]+$" 
                                   title="Numbers are not allowed in the name." 
                                   required>
                        </div>
                        <div><label>Account Number</label><input type="text" id="addAccountNo" name="account_number"></div>
                    </div>
                    <div class="form-grid" style="margin-top: 10px;">
                        <div><label>New Buyer Assumed</label><input type="text" id="addNewBuyer" name="new_buyer_assumed" pattern="^[a-zA-Z\sñÑ.]+$" title="Numbers are not allowed."></div>
                        <div><label>Buyer Representative</label><input type="text" id="addRep" name="buyer_representative" pattern="^[a-zA-Z\sñÑ.]+$" title="Numbers are not allowed."></div>
                    </div>
                    <label style="margin-top:10px;">Account/Billing Address</label>
                    <input type="text" id="addAccountAddress" name="account_address">

                    <div class="modal-section-title">Contact & Communication</div>
                    <div class="form-grid">
                        <div>
                            <label>Contact No.</label>
                            <input type="text" id="addContact" name="contact_no" 
                                   oninput="this.value = this.value.replace(/[^0-9+]/g, '')" 
                                   placeholder="09123456789">
                        </div>
                        <div><label>Email Address</label><input type="email" id="addEmail" name="email_address"></div>
                    </div>
                    <label style="margin-top:10px;">Social Media (FB/Messenger)</label>
                    <input type="text" id="addSocial" name="social_media">

                    <div class="modal-section-title">System Status & Remarks</div>
                    <div class="form-grid">
                        <div>
                            <label>Resident Status</label>
                            <select id="addStatus" name="resident_status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Moved Out">Moved Out</option>
                            </select>
                        </div>
                        <div><label>Registration Date</label><input type="date" id="addCreatedAt" name="created_at" value="<?php echo date('Y-m-d'); ?>"></div>
                    </div>
                    <label style="margin-top:10px;">Internal Remarks</label>
                    <textarea id="addRemarks" name="remarks" placeholder="Add any specific notes..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-delete" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="primary-btn">Register Resident</button>
                </div>
            </form>
        </div>
    </div>