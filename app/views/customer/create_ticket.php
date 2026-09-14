<?php
$data = $formData ?? [];
$selectedCat = intval($_GET['category'] ?? $data['category_id'] ?? 0);
?>

<!-- Leaflet CSS for embedded map in location picker -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="form-wrapper">
    <div class="card card-form">
        <div class="card-header form-card-header">
            <div>
                <h2><i class="fa-solid fa-plus-circle"></i> Register Hardware Repair Complaint</h2>
                <p>Fill in your appliance details and defect summary to receive service support.</p>
            </div>
        </div>

        <div class="card-body">
            <form action="index.php?url=ticket/create" method="POST" enctype="multipart/form-data" id="complaintForm">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <!-- Hidden location fields populated by JS -->
                <input type="hidden" name="location_lat" id="location_lat">
                <input type="hidden" name="location_lng" id="location_lng">
                <input type="hidden" name="location_address" id="location_address">

                <!-- ======== SECTION 1: Appliance Info ======== -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-icon"><i class="fa-solid fa-layer-group"></i></div>
                        <div>
                            <h4>Appliance & Device Information</h4>
                            <p>Tell us about the device that needs repair.</p>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="category_id">Hardware Category <span class="required-star">*</span></label>
                            <select name="category_id" id="category_id" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $selectedCat == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group col-6">
                            <label for="device_name">Device / Appliance Name <span class="required-star">*</span></label>
                            <input type="text" id="device_name" name="device_name" value="<?= htmlspecialchars($data['device_name'] ?? '') ?>" placeholder="e.g. 55 Inch 4K QLED TV or Side-by-Side Fridge" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-4">
                            <label for="brand">Brand / Manufacturer <span class="required-star">*</span></label>
                            <input type="text" id="brand" name="brand" value="<?= htmlspecialchars($data['brand'] ?? '') ?>" placeholder="e.g. Samsung, LG, Daikin, Sony" required>
                            <div class="quick-brand-chips">
                                <span class="brand-chip" onclick="selectBrand('Samsung')">Samsung</span>
                                <span class="brand-chip" onclick="selectBrand('LG')">LG</span>
                                <span class="brand-chip" onclick="selectBrand('Sony')">Sony</span>
                                <span class="brand-chip" onclick="selectBrand('Apple')">Apple</span>
                                <span class="brand-chip" onclick="selectBrand('Dell')">Dell</span>
                                <span class="brand-chip" onclick="selectBrand('HP')">HP</span>
                                <span class="brand-chip" onclick="selectBrand('Daikin')">Daikin</span>
                                <span class="brand-chip" onclick="selectBrand('Whirlpool')">Whirlpool</span>
                            </div>
                        </div>

                        <div class="form-group col-4">
                            <label for="model_number">Model Number</label>
                            <input type="text" id="model_number" name="model_number" value="<?= htmlspecialchars($data['model_number'] ?? '') ?>" placeholder="e.g. QN55Q60A">
                        </div>

                        <div class="form-group col-4">
                            <label for="serial_number">Serial Number</label>
                            <input type="text" id="serial_number" name="serial_number" value="<?= htmlspecialchars($data['serial_number'] ?? '') ?>" placeholder="e.g. SN-998822">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="warranty_status">Warranty Coverage <span class="required-star">*</span></label>
                            <select name="warranty_status" id="warranty_status" required>
                                <option value="out_of_warranty" <?= ($data['warranty_status'] ?? '') === 'out_of_warranty' ? 'selected' : '' ?>>Out of Warranty</option>
                                <option value="in_warranty" <?= ($data['warranty_status'] ?? '') === 'in_warranty' ? 'selected' : '' ?>>In Warranty (Proof Required)</option>
                                <option value="unknown" <?= ($data['warranty_status'] ?? '') === 'unknown' ? 'selected' : '' ?>>Not Sure / Unchecked</option>
                            </select>
                        </div>

                        <div class="form-group col-6">
                            <label for="preferred_date">Preferred Service Date</label>
                            <input type="date" id="preferred_date" name="preferred_date" value="<?= htmlspecialchars($data['preferred_date'] ?? date('Y-m-d', strtotime('+1 day'))) ?>">
                        </div>
                    </div>
                </div>

                <!-- ======== SECTION 2: Fault Details ======== -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-icon orange"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div>
                            <h4>Fault & Complaint Details</h4>
                            <p>Describe the issue so our technicians can prepare for your repair.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="issue_title">Summary of Problem <span class="required-star">*</span></label>
                        <input type="text" id="issue_title" name="issue_title" value="<?= htmlspecialchars($data['issue_title'] ?? '') ?>" placeholder="e.g. TV screen turns black after 5 minutes, Freezer not cooling" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Detailed Fault Description <span class="required-star">*</span></label>
                        <textarea id="description" name="description" rows="5" placeholder="Describe what happens, any error codes, unusual noises, or when the issue occurs..." required><?= htmlspecialchars($data['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="priority">Urgency Priority <span class="required-star">*</span></label>
                            <select name="priority" id="priority" required>
                                <option value="medium" <?= ($data['priority'] ?? 'medium') === 'medium' ? 'selected' : '' ?>>Medium (Standard Turnaround)</option>
                                <option value="low" <?= ($data['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low (Routine Check)</option>
                                <option value="high" <?= ($data['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High (Fast Inspection)</option>
                                <option value="urgent" <?= ($data['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent / Emergency</option>
                            </select>
                        </div>

                        <div class="form-group col-6">
                            <label for="attachment"><i class="fa-solid fa-paperclip"></i> Defect Photo / Invoice (JPG, PNG, PDF ≤ 5MB)</label>
                            <div class="file-input-wrapper dropzone-area" id="fileDropzone">
                                <input type="file" id="attachment" name="attachment" accept=".jpg,.jpeg,.png,.webp,.gif,.pdf">
                                <div class="file-input-display" id="fileDisplay">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span>Drag & drop photo here or <strong>browse</strong></span>
                                </div>
                            </div>
                            <div class="file-preview-box" id="filePreviewBox" style="display:none;">
                                <div class="preview-thumb" id="previewThumb"></div>
                                <div class="preview-info">
                                    <span class="preview-name" id="previewName"></span>
                                    <span class="preview-size" id="previewSize"></span>
                                </div>
                                <button type="button" class="preview-remove-btn" id="previewRemoveBtn" title="Remove file">&times;</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ======== SECTION 3: Location Plugin ======== -->
                <div class="form-section">
                    <div class="form-section-header">
                        <div class="form-section-icon green"><i class="fa-solid fa-location-dot"></i></div>
                        <div>
                            <h4>Service Location <span class="badge-optional">Optional</span></h4>
                            <p>Share your current location so the technician can reach you faster.</p>
                        </div>
                    </div>

                    <div class="location-plugin-card">
                        <div class="location-plugin-actions">
                            <button type="button" class="btn btn-location" id="btnDetectLocation">
                                <i class="fa-solid fa-crosshairs"></i> Detect My Current Location
                            </button>
                            <div class="location-status" id="locationStatus">
                                <span class="status-dot inactive"></span>
                                <span id="locationStatusText">Location not set</span>
                            </div>
                        </div>

                        <div class="location-address-row" id="locationAddressRow" style="display:none;">
                            <div class="form-group">
                                <label for="location_address_display">Detected Address (editable)</label>
                                <div class="input-with-btn">
                                    <input type="text" id="location_address_display" placeholder="Address will appear here..." class="location-address-input">
                                    <button type="button" class="btn btn-sm btn-outline" id="btnClearLocation" title="Clear location">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Leaflet Map Preview -->
                        <div id="locationMapContainer" class="location-map-container" style="display:none;">
                            <div id="locationMap"></div>
                            <div class="map-coords-badge">
                                <i class="fa-solid fa-map-pin"></i>
                                <span id="mapCoordsLabel">—</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ======== Form Actions ======== -->
                <div class="form-submit-bar">
                    <a href="index.php?url=ticket/dashboard" class="btn btn-outline btn-lg">
                        <i class="fa-solid fa-xmark"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-paper-plane"></i> Submit Hardware Complaint
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    let locationMap = null;
    let locationMarker = null;

    const btnDetect = document.getElementById('btnDetectLocation');
    const btnClear = document.getElementById('btnClearLocation');
    const statusDot = document.querySelector('.status-dot');
    const statusText = document.getElementById('locationStatusText');
    const addressRow = document.getElementById('locationAddressRow');
    const mapContainer = document.getElementById('locationMapContainer');
    const addressDisplay = document.getElementById('location_address_display');
    const coordsLabel = document.getElementById('mapCoordsLabel');
    const hiddenLat = document.getElementById('location_lat');
    const hiddenLng = document.getElementById('location_lng');
    const hiddenAddress = document.getElementById('location_address');

    function setLocationStatus(type, text) {
        statusDot.className = 'status-dot ' + type;
        statusText.textContent = text;
    }

    function initMap(lat, lng) {
        mapContainer.style.display = 'block';
        addressRow.style.display = 'block';
        if (!locationMap) {
            locationMap = L.map('locationMap', { zoomControl: true }).setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(locationMap);
            locationMarker = L.marker([lat, lng], { draggable: true }).addTo(locationMap);
            locationMarker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                updateCoords(pos.lat, pos.lng);
                reverseGeocode(pos.lat, pos.lng);
            });
        } else {
            locationMap.setView([lat, lng], 15);
            locationMarker.setLatLng([lat, lng]);
            locationMap.invalidateSize();
        }
    }

    function updateCoords(lat, lng) {
        hiddenLat.value = lat.toFixed(7);
        hiddenLng.value = lng.toFixed(7);
        coordsLabel.textContent = lat.toFixed(5) + ', ' + lng.toFixed(5);
    }

    function reverseGeocode(lat, lng) {
        setLocationStatus('loading', 'Looking up address...');
        fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
            .then(r => r.json())
            .then(data => {
                const address = data.display_name || `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                addressDisplay.value = address;
                hiddenAddress.value = address;
                setLocationStatus('active', 'Location set successfully');
            })
            .catch(() => {
                const address = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                addressDisplay.value = address;
                hiddenAddress.value = address;
                setLocationStatus('active', 'Location set (address lookup failed)');
            });
    }

    btnDetect.addEventListener('click', function () {
        if (!navigator.geolocation) {
            setLocationStatus('error', 'Geolocation not supported by your browser');
            return;
        }
        btnDetect.disabled = true;
        btnDetect.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Detecting...';
        setLocationStatus('loading', 'Requesting GPS signal...');

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                btnDetect.disabled = false;
                btnDetect.innerHTML = '<i class="fa-solid fa-crosshairs"></i> Update Location';
                updateCoords(lat, lng);
                initMap(lat, lng);
                reverseGeocode(lat, lng);
            },
            function (err) {
                btnDetect.disabled = false;
                btnDetect.innerHTML = '<i class="fa-solid fa-crosshairs"></i> Detect My Current Location';
                const msgs = {
                    1: 'Location access denied. Please enable in browser settings.',
                    2: 'Unable to determine location. Check GPS/network.',
                    3: 'Location request timed out. Please try again.'
                };
                setLocationStatus('error', msgs[err.code] || 'Location detection failed.');
            },
            { timeout: 12000, enableHighAccuracy: true }
        );
    });

    btnClear.addEventListener('click', function () {
        hiddenLat.value = '';
        hiddenLng.value = '';
        hiddenAddress.value = '';
        addressDisplay.value = '';
        mapContainer.style.display = 'none';
        addressRow.style.display = 'none';
        setLocationStatus('inactive', 'Location not set');
        btnDetect.innerHTML = '<i class="fa-solid fa-crosshairs"></i> Detect My Current Location';
        if (locationMap) {
            locationMap.remove();
            locationMap = null;
            locationMarker = null;
        }
    });

    // Sync editable address input back to hidden field
    addressDisplay.addEventListener('input', function () {
        hiddenAddress.value = this.value;
    });

    // File input custom display
    const fileInput = document.getElementById('attachment');
    const fileDisplay = document.getElementById('fileDisplay');
    if (fileInput && fileDisplay) {
        fileInput.addEventListener('change', function () {
            if (this.files.length > 0) {
                const name = this.files[0].name;
                const size = (this.files[0].size / 1024).toFixed(1);
                fileDisplay.innerHTML = `<i class="fa-solid fa-file-circle-check" style="color:#059669"></i><span><strong>${name}</strong> (${size} KB)</span>`;
            }
        });
    }
})();
</script>
