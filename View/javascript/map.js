/* ==========================================================
    MAP.JS - DYNAMIC MULTI-PROJECT LOADER
    Supports ID (Database) and Name (Map Configuration)
========================================================== */
document.addEventListener("DOMContentLoaded", () => {
    const mapContainer = document.getElementById('mapContainer');
    const locationSelect = document.getElementById('locationSelect'); 
    let map;
    let currentLayer;
    let markersLayer;

    // --- FUNCTION TO CLOSE/HIDE MAP ---
    window.closeMapSection = function() {
        if (mapContainer) mapContainer.style.display = 'none';
        
        const mapControls = document.getElementById('map-controls');
        if (mapControls) mapControls.style.display = 'none';

        console.log("Map closed.");
    };

    // --- MAIN RENDER LOGIC ---
    window.handleLocationChange = function() {
        if (!locationSelect || !locationSelect.value) {
            alert("Please select a subdivision first.");
            return;
        }
        
        // 1. Get the Numeric ID (e.g., "14") for Database/Resident lookups
        const projectID = locationSelect.value.trim(); 

        // 2. Get the Name Key (e.g., "VHS PH 2") from the data-name attribute
        // This is the bridge between the ID and the MAPS/PROJECT_MARKERS objects
        const selectedOption = locationSelect.options[locationSelect.selectedIndex];
        const projectKey = selectedOption.getAttribute('data-name'); 

        console.log(`System: Loading ID ${projectID} | Map Key: ${projectKey}`);

        // 3. Look up map data using the NAME key
        const projectData = (typeof MAPS !== 'undefined') ? MAPS[projectKey] : null;

        if (!projectData) {
            console.error("Project Key not found in MAPS:", projectKey);
            if (typeof MAPS !== 'undefined') {
                console.log("Available keys in MAPS:", Object.keys(MAPS));
            }
            alert(`Map configuration not found for "${projectKey}". Please check your database paths.`);
            return;
        }

        // --- SHOW UI ELEMENTS ---
        if (mapContainer) mapContainer.style.display = 'block';

        const mapControls = document.getElementById('map-controls');
        if (mapControls) mapControls.style.display = 'flex';

        const analyticsBox = document.getElementById('project-analytics-box');
        if (analyticsBox) analyticsBox.style.display = 'block';

        // --- INITIALIZE LEAFLET ---
        if (!map && mapContainer) {
            map = L.map('mapContainer', { 
                crs: L.CRS.Simple, 
                minZoom: -1, 
                maxZoom: 2,
                zoomControl: true 
            });
            
            map.on('click', function(e) {
                console.log("Coords for marker.js:", [Math.round(e.latlng.lat), Math.round(e.latlng.lng)]);
            });
        }

        // Clear previous layers to prevent overlap
        if (currentLayer && map) map.removeLayer(currentLayer);
        if (markersLayer && map) map.removeLayer(markersLayer);

        // --- ADD IMAGE OVERLAY ---
        // Uses the size and image path provided by get_maps_data.php
        const bounds = [[0, 0], [projectData.size[1], projectData.size[0]]];
        currentLayer = L.imageOverlay(projectData.image, bounds).addTo(map);
        map.fitBounds(bounds);

        // Update Charts (Using the Name key for visual labeling)
        if (typeof window.updateProjectCharts === "function") {
            window.updateProjectCharts(projectKey);
        }

        // --- RENDER MARKERS ---
        markersLayer = L.layerGroup().addTo(map);
        
        // Load marker coordinates using the Name key
        const projectMarkers = (typeof PROJECT_MARKERS !== 'undefined') ? PROJECT_MARKERS[projectKey] || [] : [];
        console.log(`Rendering ${projectMarkers.length} markers for ${projectKey}`);

        projectMarkers.forEach(markerData => {
            const lotNum = String(markerData.lot).trim(); 
            const blockNum = String(markerData.block).trim();
            
            // Resident Lookup: Uses the Numeric ID (projectID) to match the database
            const resident = (typeof window.getResidentByLotBlock === "function")
                ? window.getResidentByLotBlock(lotNum, blockNum, projectID) 
                : null;

            // Pin Color Logic based on resident status
            let pinClass = "vacant-lot"; 
            let statusText = "Vacant";

            if (resident) {
                const status = String(resident.resident_status || "").toLowerCase().trim();
                if (status === "active") {
                    pinClass = "active-resident"; 
                    statusText = "Occupied (Active)";
                } else {
                    pinClass = "inactive-resident"; 
                    statusText = `Occupied (${resident.resident_status})`;
                }
            }

            // Create Icon
            const icon = L.divIcon({
                className: `custom-pin ${pinClass}`,
                html: `<div class="pin"></div>`, 
                iconSize: [20, 20],
                iconAnchor: [10, 10] 
            });

            // Add Marker to Map
            const marker = L.marker(markerData.pos, { icon });
            
            const buyerName = resident ? `<br><b>${resident.buyer_name}</b>` : "";
            marker.bindTooltip(`Block ${blockNum} Lot ${lotNum} ${buyerName} <br> ${statusText}`);
            
            marker.on('click', (e) => {
                L.DomEvent.stopPropagation(e); 
                if (typeof window.openLotModal === "function") {
                    // Pass the Database ID so the modal pulls correct records
                    window.openLotModal(projectID, blockNum, lotNum);
                }
            });
            
            markersLayer.addLayer(marker);
        });

        // Ensure map renders correctly and scroll into view
        map.invalidateSize();
        if (mapContainer) {
            mapContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };
});