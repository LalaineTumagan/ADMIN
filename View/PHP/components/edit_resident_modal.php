<div id="editResidentModal" class="modal-overlay">
        <div class="modal-container" style="max-width: 800px;">
            <div class="modal-top-bar"><span>Edit Resident Information</span><button type="button" onclick="closeEditModal()">✕</button></div>
            <form id="editResidentForm">
                <input type="hidden" id="editResidentId" name="resident_id">
                <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <div class="modal-section-title">Property Details</div>
                    <div class="form-grid">
                        <div>
                            <label>Subdivision Project</label>
                            <select id="editProject" name="subdivision_id" required>
                                <option value="" disabled selected>Select Project</option>
                                <?php $projects->data_seek(0); while($p = $projects->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($p['subdivision_id']); ?>"><?php echo $p['project_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div><label>TCT Number</label><input type="text" id="editTct" name="tct_no"></div>
                        <div><label>Phase</label><input type="text" id="editPhase" name="phase"></div>
                        <div><label>Block No.</label><input type="text" id="editBlock" name="block_no" style="text-transform: uppercase;" required></div>
                        <div><label>Lot No.</label><input type="text" id="editLot" name="lot_no" style="text-transform: uppercase;" required></div>
                        <div><label>Created Date</label><input type="date" id="editCreatedAt" name="created_at"></div>
                    </div>

                    <div class="modal-section-title">Ownership & Assumption</div>
                    <div class="form-grid">
                        <div>
                            <label>Primary Buyer Name</label>
                            <input type="text" id="editName" name="buyer_name" 
                                   pattern="^[a-zA-Z\sñÑ.]+$" 
                                   title="Numbers are not allowed in the name." 
                                   required>
                        </div>
                        <div>
                            <label>New Buyer / Assumed By</label>
                            <input type="text" id="editNewBuyer" name="new_buyer_assumed" 
                                   pattern="^[a-zA-Z\sñÑ.]+$" 
                                   title="Numbers are not allowed.">
                        </div>
                        <div>
                            <label>Buyer Representative</label>
                            <input type="text" id="editRep" name="buyer_representative" 
                                   pattern="^[a-zA-Z\sñÑ.]+$" 
                                   title="Numbers are not allowed.">
                        </div>
                        <div><label>Account Number</label><input type="text" id="editAccountNo" name="account_number"></div>
                    </div>

                    <div class="modal-section-title">Contact Information</div>
                    <div class="form-grid">
                        <div>
                            <label>Contact No.</label>
                            <input type="text" id="editContact" name="contact_no" 
                                   oninput="this.value = this.value.replace(/[^0-9+]/g, '')" 
                                   placeholder="09123456789">
                        </div>
                        <div><label>Email Address</label><input type="email" id="editEmail" name="email_address"></div>
                        <div><label>Social Media (Link/Handle)</label><input type="text" id="editSocial" name="social_media"></div>
                        <div><label>Account Address</label><input type="text" id="editAccountAddress" name="account_address"></div>
                    </div>
                    
                    <div class="modal-section-title">Resident Status & Remarks</div>
                    <div class="form-grid">
                        <div>
                            <label>Resident Status</label>
                            <select id="editStatus" name="resident_status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="Moved Out">Moved Out</option>
                            </select>
                        </div>
                        <div><label>Remarks</label><textarea id="editRemarks" name="remarks"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer" style="justify-content: space-between;">
                    <button type="button" class="danger-btn" onclick="openDeleteConfirmation()" style="background: #991b1b; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;">Delete Resident</button>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="btn-delete" onclick="closeEditModal()" style="background: #475569;">Cancel</button>
                        <button type="submit" class="primary-btn">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>