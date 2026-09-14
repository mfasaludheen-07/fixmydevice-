<div class="dashboard-wrapper">
    <div class="dashboard-header">
        <div class="dashboard-title">
            <h1><i class="fa-solid fa-gauge-high" style="color:var(--primary)"></i> Admin Control Center</h1>
            <p>Master dashboard for managing hardware complaints, technician workload, categories, and system metrics.</p>
        </div>
        <div class="dashboard-actions">
            <a href="index.php?url=admin/export_csv" class="btn btn-outline" style="margin-right:8px;">
                <i class="fa-solid fa-file-csv"></i> Export Tickets CSV
            </a>
            <button class="btn btn-primary" onclick="document.getElementById('newCatModal').classList.toggle('active')">
                <i class="fa-solid fa-folder-plus"></i> Add Category
            </button>
        </div>
    </div>

    <!-- Admin Top System Metrics -->
    <div class="dashboard-stats-grid">
        <div class="dash-stat-card border-blue">
            <div class="stat-num"><?= $stats['total'] ?></div>
            <div class="stat-label"><i class="fa-solid fa-clipboard-list"></i> Total Complaints</div>
        </div>
        <div class="dash-stat-card border-amber">
            <div class="stat-num"><?= $stats['pending'] ?></div>
            <div class="stat-label"><i class="fa-solid fa-hourglass-start"></i> Pending Assignment</div>
        </div>
        <div class="dash-stat-card border-purple">
            <div class="stat-num"><?= $stats['in_progress'] ?></div>
            <div class="stat-label"><i class="fa-solid fa-microchip"></i> Active Repairs</div>
        </div>
        <div class="dash-stat-card border-green">
            <div class="stat-num"><?= $stats['ready'] + $stats['resolved'] ?></div>
            <div class="stat-label"><i class="fa-solid fa-circle-check"></i> Ready / Resolved</div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card" style="margin-bottom:24px;">
        <div class="card-body">
            <form action="index.php" method="GET" style="display:flex;gap:16px;align-items:flex-end;flex-wrap:wrap;">
                <input type="hidden" name="url" value="admin/dashboard">
                <div class="form-group" style="flex:1;min-width:180px;margin-bottom:0;">
                    <label class="form-label">Filter by Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Statuses --</option>
                        <option value="pending" <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="assigned" <?= $filterStatus === 'assigned' ? 'selected' : '' ?>>Assigned</option>
                        <option value="in_diagnosis" <?= $filterStatus === 'in_diagnosis' ? 'selected' : '' ?>>In Diagnosis</option>
                        <option value="repair_in_progress" <?= $filterStatus === 'repair_in_progress' ? 'selected' : '' ?>>Repair In Progress</option>
                        <option value="ready" <?= $filterStatus === 'ready' ? 'selected' : '' ?>>Ready for Pickup</option>
                        <option value="resolved" <?= $filterStatus === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                    </select>
                </div>
                <div class="form-group" style="flex:1;min-width:180px;margin-bottom:0;">
                    <label class="form-label">Filter by Category</label>
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">-- All Categories --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $filterCategory == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex:0 0 auto;padding-top:24px;">
                    <a href="index.php?url=admin/dashboard" class="btn btn-outline">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Master Tickets Management Table -->
    <div class="card" style="margin-bottom:32px;">
        <div class="card-header dashboard-table-header">
            <h3><i class="fa-solid fa-list"></i> Master Ticket Registry</h3>
            <div class="table-search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="adminTicketSearch" class="table-search-input" placeholder="Search any ticket, device, customer..." onkeyup="filterTable('adminTicketSearch', 'adminTicketTable')">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover" id="adminTicketTable">
                    <thead>
                        <tr>
                            <th>Ticket Code</th>
                            <th>Category & Device</th>
                            <th>Customer</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Assign Technician</th>
                            <th>Est. Quote (₹)</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td>
                                    <a href="index.php?url=ticket/view/<?= $t['ticket_code'] ?>" class="ticket-code-badge">
                                        <i class="fa-solid fa-hashtag"></i> <?= htmlspecialchars($t['ticket_code']) ?>
                                    </a>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($t['brand'] . ' ' . $t['device_name']) ?></strong>
                                    <br><small style="color:var(--text-muted)"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($t['category_name']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($t['customer_name']) ?></td>
                                <td>
                                    <?php if (!empty($t['location_lat']) && !empty($t['location_lng'])): ?>
                                        <button type="button" class="btn btn-sm btn-location-view"
                                            data-ticket="<?= htmlspecialchars($t['ticket_code']) ?>"
                                            data-lat="<?= floatval($t['location_lat']) ?>"
                                            data-lng="<?= floatval($t['location_lng']) ?>"
                                            data-address="<?= htmlspecialchars($t['location_address'] ?? '') ?>"
                                            onclick="openLocationModalFromBtn(this)"
                                            title="View customer location">
                                            <i class="fa-solid fa-location-dot"></i> View Map
                                        </button>
                                    <?php else: ?>
                                        <span style="color:var(--text-muted);font-size:0.82rem;">
                                            <i class="fa-solid fa-location-dot-slash"></i> Not set
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-status-<?= $t['status'] ?>">
                                        <?= str_replace('_', ' ', ucfirst($t['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="index.php?url=admin/assign" method="POST" style="display:flex;gap:6px;align-items:center;">
                                        <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                                        <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                        <select name="technician_id" class="form-select" style="font-size:0.85rem;padding:6px 10px;">
                                            <option value="">-- Assign Tech --</option>
                                            <?php foreach ($technicians as $tech): ?>
                                                <option value="<?= $tech['id'] ?>" <?= ($t['technician_id'] ?? '') == $tech['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($tech['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary" title="Assign">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                </td>
                                <td><strong>₹<?= number_format($t['estimated_cost'], 2) ?></strong></td>
                                <td style="white-space:nowrap"><?= date('M d, Y', strtotime($t['created_at'])) ?></td>
                                <td>
                                    <a href="index.php?url=ticket/view/<?= $t['ticket_code'] ?>" class="btn btn-sm btn-outline">
                                        <i class="fa-solid fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- System Users List -->
    <div class="card" style="margin-bottom:32px;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3><i class="fa-solid fa-users"></i> System Accounts & Personnel</h3>
            <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('newTechModal').classList.add('active')">
                <i class="fa-solid fa-user-plus"></i> Add Technician
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email Address</th>
                            <th>Phone</th>
                            <th>Current Role</th>
                            <th>Joined Date</th>
                            <th>Change Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>#<?= $u['id'] ?></td>
                                <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= htmlspecialchars($u['phone'] ?: 'N/A') ?></td>
                                <td><span class="badge badge-role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                                <td style="white-space:nowrap"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <form action="index.php?url=admin/change_role" method="POST" style="display:flex;gap:6px;align-items:center;margin:0;">
                                        <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <select name="role" class="form-select" style="padding:4px 8px;font-size:0.8rem;height:auto;min-width:115px;" <?= ($u['id'] === Session::user()['id']) ? 'disabled' : '' ?>>
                                            <option value="customer" <?= $u['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                            <option value="technician" <?= $u['role'] === 'technician' ? 'selected' : '' ?>>Technician</option>
                                            <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                        <?php if ($u['id'] !== Session::user()['id']): ?>
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Update Role" style="padding:4px 9px;">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add New Category Modal -->
<div class="modal-overlay" id="newCatModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fa-solid fa-folder-plus"></i> Add Hardware Category</h3>
            <button class="modal-close" onclick="document.getElementById('newCatModal').classList.remove('active')">&times;</button>
        </div>
        <form action="index.php?url=admin/create_category" method="POST">
            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label>Category Name <span class="required-star">*</span></label>
                    <input type="text" name="name" placeholder="e.g. Smart Watches & Wearables" required>
                </div>
                <div class="form-group">
                    <label>FontAwesome Icon Name</label>
                    <input type="text" name="icon" placeholder="e.g. tv, laptop, washer, snowflake, wind">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Category description..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Category</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('newCatModal').classList.remove('active')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Add New Technician Modal -->
<div class="modal-overlay" id="newTechModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3><i class="fa-solid fa-user-gear text-primary"></i> Register New Technician</h3>
            <button class="modal-close" onclick="document.getElementById('newTechModal').classList.remove('active')">&times;</button>
        </div>
        <form action="index.php?url=admin/create_technician" method="POST">
            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label>Technician Full Name <span class="required-star">*</span></label>
                    <input type="text" name="name" placeholder="e.g. Marcus Vance" required>
                </div>
                <div class="form-group">
                    <label>Work Email Address <span class="required-star">*</span></label>
                    <input type="email" name="email" placeholder="e.g. marcus.tech@fixmydevice.com" required>
                </div>
                <div class="form-group">
                    <label>Initial Password <span class="required-star">*</span></label>
                    <input type="password" name="password" placeholder="Minimum 6 characters" minlength="6" required>
                    <small class="text-muted" style="display:block;margin-top:4px;"><i class="fa-solid fa-shield-halved"></i> Password will be securely hashed with PASSWORD_DEFAULT.</small>
                </div>
                <div class="form-group">
                    <label>Phone Contact</label>
                    <input type="text" name="phone" placeholder="e.g. +1 800 555 0188">
                </div>
                <div class="form-group">
                    <label>Service Hub / Workshop Location</label>
                    <input type="text" name="address" placeholder="e.g. Repair Center Station 4">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-user-check"></i> Register Technician</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('newTechModal').classList.remove('active')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Location Map Modal -->
<div class="modal-overlay" id="locationModal">
    <div class="modal-card" style="max-width:680px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-location-dot" style="color:#10b981"></i> Customer Location — <span id="locModalTicket"></span></h3>
            <button class="modal-close" onclick="closeLocationModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding:0;">
            <div id="adminLocationMap" style="height:380px;width:100%;"></div>
            <div style="padding:18px 20px;background:#f8fafc;border-top:1px solid var(--border-color);">
                <p style="margin:0;font-size:0.92rem;"><i class="fa-solid fa-map-pin" style="color:var(--primary)"></i> <span id="locModalAddress" style="font-weight:500"></span></p>
                <p id="locModalCoords" style="margin:4px 0 0;font-size:0.8rem;color:var(--text-muted);font-family:monospace;"></p>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet for Admin Map Modal -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let adminMap = null;
let adminMarker = null;

function openLocationModalFromBtn(btn) {
    const ticketCode = btn.dataset.ticket;
    const lat = parseFloat(btn.dataset.lat);
    const lng = parseFloat(btn.dataset.lng);
    const address = btn.dataset.address || '';
    showLocationModal(ticketCode, lat, lng, address);
}

function showLocationModal(ticketCode, lat, lng, address) {
    document.getElementById('locModalTicket').textContent = ticketCode;
    document.getElementById('locModalAddress').textContent = address || (lat.toFixed(5) + ', ' + lng.toFixed(5));
    document.getElementById('locModalCoords').textContent = 'Coordinates: ' + lat.toFixed(7) + ', ' + lng.toFixed(7);
    document.getElementById('locationModal').classList.add('active');

    setTimeout(function () {
        if (!adminMap) {
            adminMap = L.map('adminLocationMap').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(adminMap);
        } else {
            adminMap.setView([lat, lng], 15);
        }

        if (adminMarker) { adminMap.removeLayer(adminMarker); }

        const icon = L.divIcon({
            html: '<div class="admin-map-pin"><i class="fa-solid fa-house" style="font-size:18px;color:#fff;"></i></div>',
            className: '',
            iconSize: [38, 38],
            iconAnchor: [19, 38]
        });

        adminMarker = L.marker([lat, lng]).addTo(adminMap);
        adminMarker.bindPopup('<b>Customer Location</b><br>' + (address || ''), { maxWidth: 250 }).openPopup();
        adminMap.invalidateSize();
    }, 120);
}

function closeLocationModal() {
    document.getElementById('locationModal').classList.remove('active');
}
</script>
