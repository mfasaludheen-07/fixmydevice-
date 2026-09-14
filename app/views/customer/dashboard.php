<div class="dashboard-wrapper">
    <div class="dashboard-header">
        <div class="dashboard-title">
            <h1><i class="fa-solid fa-gauge-high"></i> Customer Service Dashboard</h1>
            <p>Welcome back, <strong><?= htmlspecialchars($user['name']) ?></strong>! Track your registered hardware service tickets.</p>
        </div>
        <div class="dashboard-actions">
            <a href="index.php?url=ticket/create" class="btn btn-primary btn-lg">
                <i class="fa-solid fa-plus-circle"></i> Submit New Complaint
            </a>
        </div>
    </div>

    <!-- Quick Ticket Summary Stats -->
    <div class="dashboard-stats-grid">
        <?php
        $totalCount = count($tickets);
        $pendingCount = 0;
        $inProgressCount = 0;
        $readyCount = 0;
        $resolvedCount = 0;

        foreach ($tickets as $t) {
            if ($t['status'] === 'pending') $pendingCount++;
            elseif (in_array($t['status'], ['assigned', 'in_diagnosis', 'awaiting_parts', 'repair_in_progress'])) $inProgressCount++;
            elseif ($t['status'] === 'ready') $readyCount++;
            elseif ($t['status'] === 'resolved') $resolvedCount++;
        }
        ?>
        <div class="dash-stat-card border-blue">
            <div class="stat-num"><?= $totalCount ?></div>
            <div class="stat-label"><i class="fa-solid fa-ticket"></i> Total Registered</div>
        </div>
        <div class="dash-stat-card border-amber">
            <div class="stat-num"><?= $inProgressCount + $pendingCount ?></div>
            <div class="stat-label"><i class="fa-solid fa-wrench"></i> In Repair / Active</div>
        </div>
        <div class="dash-stat-card border-green">
            <div class="stat-num"><?= $readyCount ?></div>
            <div class="stat-label"><i class="fa-solid fa-circle-check"></i> Ready for Pickup</div>
        </div>
        <div class="dash-stat-card border-purple">
            <div class="stat-num"><?= $resolvedCount ?></div>
            <div class="stat-label"><i class="fa-solid fa-square-check"></i> Completed & Closed</div>
        </div>
    </div>

    <!-- Ticket Records Section -->
    <div class="card card-dashboard">
        <div class="card-header dashboard-table-header">
            <h3><i class="fa-solid fa-list-check"></i> Your Hardware Complaints</h3>
            <?php if (!empty($tickets)): ?>
                <div class="table-search-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="custTicketSearch" class="table-search-input" placeholder="Search complaints..." onkeyup="filterTable('custTicketSearch', 'customerTicketTable')">
                </div>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($tickets)): ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="fa-solid fa-box-open"></i></div>
                    <h3>No complaints registered yet</h3>
                    <p>Facing issues with your TV, Refrigerator, AC, or Laptop? Register a ticket for fast technician support.</p>
                    <a href="index.php?url=ticket/create" class="btn btn-primary">
                        <i class="fa-solid fa-plus-circle"></i> Register Complaint Now
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="customerTicketTable">
                        <thead>
                            <tr>
                                <th>Ticket Code</th>
                                <th>Category & Device</th>
                                <th>Issue Summary</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Assigned Tech</th>
                                <th>Submitted Date</th>
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
                                        <div class="device-cell">
                                            <strong><?= htmlspecialchars($t['brand'] . ' ' . $t['device_name']) ?></strong>
                                            <span class="category-tag"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($t['category_name']) ?></span>
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
                                        <?= !empty($t['technician_name']) ? htmlspecialchars($t['technician_name']) : '<span class="text-muted"><i class="fa-solid fa-hourglass-start"></i> Pending Assignment</span>' ?>
                                    </td>
                                    <td class="text-nowrap">
                                        <?= date('M d, Y', strtotime($t['created_at'])) ?>
                                    </td>
                                    <td>
                                        <a href="index.php?url=ticket/view/<?= $t['ticket_code'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fa-solid fa-eye"></i> Details
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
