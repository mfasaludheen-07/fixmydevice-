<div class="dashboard-wrapper">
    <div class="dashboard-header">
        <div class="dashboard-title">
            <h1><i class="fa-solid fa-toolbox text-amber"></i> Technician Repair Workbench</h1>
            <p>Welcome, <strong><?= htmlspecialchars($user['name']) ?></strong>! Manage your assigned repair jobs and update diagnostic status.</p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="dashboard-stats-grid">
        <?php
        $totalJobs = count($tickets);
        $inDiagnosis = 0;
        $inRepair = 0;
        $readyForPickup = 0;

        foreach ($tickets as $t) {
            if ($t['status'] === 'in_diagnosis') $inDiagnosis++;
            elseif (in_array($t['status'], ['repair_in_progress', 'awaiting_parts'])) $inRepair++;
            elseif ($t['status'] === 'ready') $readyForPickup++;
        }
        ?>
        <div class="dash-stat-card border-blue">
            <div class="stat-num"><?= $totalJobs ?></div>
            <div class="stat-label"><i class="fa-solid fa-briefcase"></i> Total Assigned Jobs</div>
        </div>
        <div class="dash-stat-card border-amber">
            <div class="stat-num"><?= $inDiagnosis ?></div>
            <div class="stat-label"><i class="fa-solid fa-magnifying-glass"></i> In Diagnosis</div>
        </div>
        <div class="dash-stat-card border-purple">
            <div class="stat-num"><?= $inRepair ?></div>
            <div class="stat-label"><i class="fa-solid fa-microchip"></i> Under Repair</div>
        </div>
        <div class="dash-stat-card border-green">
            <div class="stat-num"><?= $readyForPickup ?></div>
            <div class="stat-label"><i class="fa-solid fa-circle-check"></i> Ready for Pickup</div>
        </div>
    </div>

    <!-- Available / Unassigned Queue Card -->
    <div class="card card-dashboard mb-4" style="border-top: 4px solid var(--accent);">
        <div class="card-header dashboard-table-header" style="background: rgba(245, 158, 11, 0.08);">
            <div style="display:flex; align-items:center; gap:10px;">
                <h3 style="margin:0;"><i class="fa-solid fa-hand-sparkles text-amber"></i> Available & Unassigned Jobs Queue</h3>
                <span class="badge badge-warning"><?= count($unassignedTickets ?? []) ?> Available</span>
            </div>
            <p style="margin:0; font-size:0.85rem; color:var(--text-muted);">Complaints awaiting technician assignment. Click "Claim & Start Job" to take ownership.</p>
        </div>
        <div class="card-body p-0">
            <?php if (empty($unassignedTickets)): ?>
                <div class="empty-state py-4 text-center">
                    <p class="text-muted mb-0"><i class="fa-solid fa-circle-check text-success"></i> Great! No unassigned complaints in queue. All service tickets are currently handled.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Ticket Code</th>
                                <th>Category & Appliance</th>
                                <th>Customer Info</th>
                                <th>Fault / Issue</th>
                                <th>Priority</th>
                                <th>Submitted</th>
                                <th>Claim Job</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($unassignedTickets as $ut): ?>
                                <tr>
                                    <td>
                                        <a href="index.php?url=ticket/view/<?= $ut['ticket_code'] ?>" class="ticket-code-badge">
                                            <i class="fa-solid fa-hashtag"></i> <?= htmlspecialchars($ut['ticket_code']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($ut['brand'] . ' ' . $ut['device_name']) ?></strong>
                                            <br><small class="text-muted"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($ut['category_name']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($ut['customer_name']) ?></strong>
                                            <br><small class="text-muted"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($ut['customer_phone'] ?: 'N/A') ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="issue-cell" title="<?= htmlspecialchars($ut['description']) ?>">
                                            <?= htmlspecialchars($ut['issue_title']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-priority-<?= $ut['priority'] ?>">
                                            <?= ucfirst($ut['priority']) ?>
                                        </span>
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <?= date('M d, Y', strtotime($ut['created_at'])) ?>
                                    </td>
                                    <td>
                                        <form action="index.php?url=technician/claim" method="POST" style="margin:0;display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                                            <input type="hidden" name="ticket_id" value="<?= $ut['id'] ?>">
                                            <input type="hidden" name="ticket_code" value="<?= $ut['ticket_code'] ?>">
                                            <button type="submit" class="btn btn-sm btn-primary" style="white-space:nowrap;">
                                                <i class="fa-solid fa-hand-sparkles"></i> Claim & Start
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Assigned Tickets Table -->
    <div class="card card-dashboard">
        <div class="card-header dashboard-table-header">
            <h3><i class="fa-solid fa-list-check"></i> My Active Repair Queue</h3>
            <?php if (!empty($tickets)): ?>
                <div class="table-search-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="techTicketSearch" class="table-search-input" placeholder="Search assigned queue..." onkeyup="filterTable('techTicketSearch', 'techTicketTable')">
                </div>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($tickets)): ?>
                <div class="empty-state">
                    <div class="empty-icon text-muted"><i class="fa-solid fa-clipboard-check"></i></div>
                    <h3>No repair jobs currently assigned to you</h3>
                    <p>Check the Available Jobs queue above to claim pending complaints or wait for manager assignment.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="techTicketTable">
                        <thead>
                            <tr>
                                <th>Ticket Code</th>
                                <th>Category & Appliance</th>
                                <th>Customer Info</th>
                                <th>Issue Summary</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Est. Cost (₹)</th>
                                <th>Action</th>
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
                                        <div>
                                            <strong><?= htmlspecialchars($t['brand'] . ' ' . $t['device_name']) ?></strong>
                                            <br><small class="text-muted"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($t['category_name']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($t['customer_name']) ?></strong>
                                            <br><small class="text-muted"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($t['customer_phone'] ?: 'N/A') ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="issue-cell" title="<?= htmlspecialchars($t['description']) ?>">
                                            <?= htmlspecialchars($t['issue_title']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-priority-<?= $t['priority'] ?>">
                                            <?= ucfirst($t['priority']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-status-<?= $t['status'] ?>">
                                            <?= str_replace('_', ' ', ucfirst($t['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong>₹<?= number_format($t['estimated_cost'], 2) ?></strong>
                                    </td>
                                    <td>
                                        <a href="index.php?url=ticket/view/<?= $t['ticket_code'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fa-solid fa-toolbox"></i> Manage Repair
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
