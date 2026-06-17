<section id="section-dashboard" class="app-page active">
            <div class="stats-ribbon">
                <div class="stat-card">
                    <div class="stat-label">Global Residents</div>
                    <div class="stat-value"><?php echo number_format($totalResidents); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Active Connections</div>
                    <div class="stat-value"><?php echo number_format($activeResidents); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Receivables</div>
                    <div class="stat-value">₱ <?php echo number_format($totalMoney, 2); ?></div>
                </div>
            </div>

            <div class="location-selector-section">
                <h2 class="section-title">Project Selection</h2>

                <div class="selector-wrapper" style="display: flex; gap: 15px; margin-top: 15px;">

                    <!-- CUSTOM DROPDOWN -->
                    <div class="custom-dropdown" id="locationDropdown">

                        <div class="dropdown-selected" onclick="toggleLocationDropdown()">
                            <span id="locationSelectedText">-- Select Project --</span>
                            <span>▼</span>
                        </div>

                        <div class="dropdown-menu">

                            <!-- default option -->
                            <div class="dropdown-item"
                                onclick="selectLocation('', '-- Select Project --')">
                                -- Select Project --
                            </div>

                            <?php 
                            $projects->data_seek(0); 
                            while($p = $projects->fetch_assoc()): ?>

                                <div class="dropdown-item"
                                    onclick="selectLocation(
                                        '<?= $p['subdivision_id'] ?>',
                                        '<?= htmlspecialchars($p['project_name']) ?>'
                                    )">
                                    <?= htmlspecialchars($p['project_name']) ?>
                                </div>

                            <?php endwhile; ?>

                        </div>

                        <input type="hidden" id="locationSelect" name="subdivision_id">
                        <input type="hidden" id="projectName">

                    </div>

                    <button onclick="handleLocationChange()" class="btn-load">Load Project</button>

                </div>
            </div>

            <div class="map-wrapper" style="margin-top: 30px;">
                <div id="map-controls" style="display: none; justify-content: flex-end; margin-bottom: 10px;">
                    <button class="danger-btn" onclick="window.closeMapSection()" style="background:#ef4444; color:white; border:none; padding: 8px 16px; border-radius:8px; cursor:pointer;">✖ Close Map</button>
                </div>
                <div id="mapContainer" style="display:none; height: 550px; border-radius: 15px; z-index: 1;"></div>
            </div>

            <div id="project-analytics-box" style="margin-top: 30px; background: #1e293b; padding: 25px; border-radius: 15px; border: 1px solid #334155;">
                <h3 id="analytics-title" style="color: #d49006; margin-bottom: 20px; font-weight: 700; text-transform: uppercase;">Project Analytics Overview</h3>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 300px; height: 300px; background: #161e2e; padding: 15px; border-radius: 10px;"><canvas id="populationChart"></canvas></div>
                    <div style="flex: 1; min-width: 300px; height: 300px; background: #161e2e; padding: 15px; border-radius: 10px;"><canvas id="billingChart"></canvas></div>
                </div>
            </div>
        </section>