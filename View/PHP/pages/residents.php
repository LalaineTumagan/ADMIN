<section id="section-residents" class="app-page">
            <div class="page-header" style="margin-bottom: 25px;"><h2>Residents Management</h2></div>
            <div class="residents-toolbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <button class="primary-btn" onclick="openResidentForm()">+ Add Resident</button>
                <input type="text" id="residentSearch" placeholder="Search by name, TCT, or account..." class="search-input">
            </div>
            <div class="residents-table-wrapper">
                <table class="residents-table">
                    <thead>
                        <tr>
                            <th>ID</th><th>Subdivision</th><th>Phase</th><th>Block</th><th>Lot</th>
                            <th>TCT No.</th><th>Buyer Name</th><th>Status</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="residentsTableBody"></tbody>
                </table>
            </div>
        </section>