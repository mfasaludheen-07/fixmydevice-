<?php
$role = $user['role'] ?? 'guest';
$isStaff = in_array($role, ['technician', 'admin']);

$statuses = [
    'pending' => 1,
    'assigned' => 2,
    'in_diagnosis' => 3,
    'awaiting_parts' => 3,
    'repair_in_progress' => 4,
    'ready' => 5,
    'resolved' => 6,
    'cancelled' => 0
];
$currentStep = $statuses[$ticket['status']] ?? 1;
?>

<div class="ticket-view-wrapper">
    <!-- Header Banner -->
    <div class="ticket-header-card">
        <div class="ticket-header-main">
            <div class="ticket-code-display">
                <span class="ticket-label">TICKET CODE</span>
                <h2><i class="fa-solid fa-hashtag"></i> <?= htmlspecialchars($ticket['ticket_code']) ?></h2>
            </div>
            <div class="ticket-header-badges">
                <a href="index.php?url=ticket/invoice/<?= htmlspecialchars($ticket['ticket_code']) ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="Print Official Job Sheet & Billing Invoice">
                    <i class="fa-solid fa-print"></i> Print Invoice
                </a>
                <span class="badge badge-lg badge-status-<?= $ticket['status'] ?>">
                    <?= str_replace('_', ' ', strtoupper($ticket['status'])) ?>
                </span>
                <span class="badge badge-lg badge-priority-<?= $ticket['priority'] ?>">
                    PRIORITY: <?= strtoupper($ticket['priority']) ?>
                </span>
            </div>
        </div>

        <!-- Visual Step Tracker Progress Bar -->
        <?php if ($ticket['status'] !== 'cancelled'): 
            $progressPct = max(0, min(100, ($currentStep - 1) * 20));
        ?>
            <div class="stepper-wrapper">
                <div class="stepper-progress-fill" style="width: calc((100% - 80px) * <?= $progressPct / 100 ?>);"></div>
                <div class="stepper-item <?= $currentStep >= 1 ? 'completed' : '' ?> <?= $currentStep == 1 ? 'active' : '' ?>">
                    <div class="step-counter"><?= $currentStep > 1 ? '<i class="fa-solid fa-check"></i>' : '1' ?></div>
                    <div class="step-name">Submitted</div>
                </div>
                <div class="stepper-item <?= $currentStep >= 2 ? 'completed' : '' ?> <?= $currentStep == 2 ? 'active' : '' ?>">
                    <div class="step-counter"><?= $currentStep > 2 ? '<i class="fa-solid fa-check"></i>' : '2' ?></div>
                    <div class="step-name">Assigned</div>
                </div>
                <div class="stepper-item <?= $currentStep >= 3 ? 'completed' : '' ?> <?= $currentStep == 3 ? 'active' : '' ?>">
                    <div class="step-counter"><?= $currentStep > 3 ? '<i class="fa-solid fa-check"></i>' : '3' ?></div>
                    <div class="step-name">Diagnosis</div>
                </div>
                <div class="stepper-item <?= $currentStep >= 4 ? 'completed' : '' ?> <?= $currentStep == 4 ? 'active' : '' ?>">
                    <div class="step-counter"><?= $currentStep > 4 ? '<i class="fa-solid fa-check"></i>' : '4' ?></div>
                    <div class="step-name">In Repair</div>
                </div>
                <div class="stepper-item <?= $currentStep >= 5 ? 'completed' : '' ?> <?= $currentStep == 5 ? 'active' : '' ?>">
                    <div class="step-counter"><?= $currentStep > 5 ? '<i class="fa-solid fa-check"></i>' : '5' ?></div>
                    <div class="step-name">Ready</div>
                </div>
                <div class="stepper-item <?= $currentStep >= 6 ? 'completed' : '' ?> <?= $currentStep == 6 ? 'active' : '' ?>">
                    <div class="step-counter"><?= $currentStep >= 6 ? '<i class="fa-solid fa-check"></i>' : '6' ?></div>
                    <div class="step-name">Resolved</div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger mt-3">
                <i class="fa-solid fa-ban"></i> This complaint ticket has been cancelled.
            </div>
        <?php endif; ?>
    </div>

    <?php if ($role === 'technician' && empty($ticket['technician_id']) && $ticket['status'] !== 'cancelled'): ?>
        <div class="alert alert-warning" style="display:flex; justify-content:space-between; align-items:center; background:#fef3c7; border:1px solid #fde68a; border-left:5px solid #d97706; padding:16px 20px; border-radius:var(--radius-md); margin-top:20px; margin-bottom:20px;">
            <div>
                <h4 style="margin:0 0 4px 0; color:#92400e; font-size:1.05rem;"><i class="fa-solid fa-hand-sparkles"></i> Unassigned Repair Job</h4>
                <p style="margin:0; color:#78350f; font-size:0.9rem;">This complaint is currently pending technician assignment. You can claim it to start diagnostics and work on it.</p>
            </div>
            <form action="index.php?url=technician/claim" method="POST" style="margin:0;">
                <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <input type="hidden" name="ticket_code" value="<?= $ticket['ticket_code'] ?>">
                <button type="submit" class="btn btn-primary" style="white-space:nowrap;">
                    <i class="fa-solid fa-clipboard-check"></i> Claim This Job
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Main Content Layout Grid -->
    <div class="ticket-grid">
        <!-- Left Side: Issue Details & Comments Thread -->
        <div class="ticket-main-col">
            <!-- Issue & Device Summary Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fa-solid fa-circle-info"></i> Fault & Device Specification</h3>
                </div>
                <div class="card-body">
                    <h4 class="ticket-issue-title"><?= htmlspecialchars($ticket['issue_title']) ?></h4>
                    <p class="ticket-issue-desc"><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>

                    <hr class="my-4">

                    <div class="specs-grid">
                        <div class="spec-item">
                            <span class="spec-label">Appliance Category</span>
                            <span class="spec-val"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($ticket['category_name']) ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Brand & Model</span>
                            <span class="spec-val"><?= htmlspecialchars($ticket['brand'] . ' ' . $ticket['device_name']) ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Model # / Serial #</span>
                            <span class="spec-val"><?= htmlspecialchars(($ticket['model_number'] ?: 'N/A') . ' / ' . ($ticket['serial_number'] ?: 'N/A')) ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Warranty Status</span>
                            <span class="spec-val"><?= ucfirst(str_replace('_', ' ', $ticket['warranty_status'])) ?></span>
                        </div>
                    </div>

                    <?php if (!empty($ticket['attachment_url'])): ?>
                        <div class="attachment-box mt-3">
                            <strong><i class="fa-solid fa-paperclip"></i> Uploaded Attachment:</strong>
                            <a href="<?= htmlspecialchars($ticket['attachment_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary ms-2" rel="noopener noreferrer">
                                <i class="fa-solid fa-download"></i> View Photo/File
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Technician & Staff Workbench Controls (Visible to Tech/Admin) -->
            <?php if ($isStaff): ?>
                <div class="card mb-4 border-tech-highlight">
                    <div class="card-header bg-tech-dark text-white d-flex justify-content-between align-items-center">
                        <h3><i class="fa-solid fa-toolbox"></i> Technician Workbench & Controls</h3>
                        <span class="badge badge-warning" style="font-size:0.75rem; text-transform:uppercase;"><?= ucfirst($role) ?> Access</span>
                    </div>
                    <div class="card-body">
                        <!-- Quick Stage Advancement Buttons -->
                        <div class="mb-4">
                            <label style="font-weight:700;font-size:0.85rem;color:var(--text-muted);margin-bottom:8px;display:block;">
                                <i class="fa-solid fa-forward-step text-primary"></i> Quick Advance Progress Stage:
                            </label>
                            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                                <?php
                                $stageButtons = [
                                    'assigned' => ['icon' => 'fa-user-check', 'label' => 'Accept & Assign', 'btn' => 'btn-outline-primary'],
                                    'in_diagnosis' => ['icon' => 'fa-magnifying-glass', 'label' => 'Diagnosis', 'btn' => 'btn-outline-primary'],
                                    'awaiting_parts' => ['icon' => 'fa-clock', 'label' => 'Awaiting Parts', 'btn' => 'btn-outline-warning'],
                                    'repair_in_progress' => ['icon' => 'fa-screwdriver-wrench', 'label' => 'In Repair', 'btn' => 'btn-outline-info'],
                                    'ready' => ['icon' => 'fa-box-check', 'label' => 'Ready for Pickup', 'btn' => 'btn-outline-success'],
                                    'resolved' => ['icon' => 'fa-circle-check', 'label' => 'Mark Resolved', 'btn' => 'btn-success']
                                ];
                                foreach ($stageButtons as $stKey => $stMeta):
                                    $isCurrent = ($ticket['status'] === $stKey);
                                ?>
                                    <form action="index.php?url=technician/update_status" method="POST" style="margin:0;display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                                        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                                        <input type="hidden" name="ticket_code" value="<?= $ticket['ticket_code'] ?>">
                                        <input type="hidden" name="status" value="<?= $stKey ?>">
                                        <input type="hidden" name="note" value="Advanced to <?= str_replace('_', ' ', $stKey) ?> via quick control">
                                        <button type="submit" class="btn btn-sm <?= $isCurrent ? 'btn-primary active disabled' : $stMeta['btn'] ?>" <?= $isCurrent ? 'disabled title="Current Stage"' : '' ?>>
                                            <i class="fa-solid <?= $stMeta['icon'] ?>"></i> <?= $stMeta['label'] ?>
                                        </button>
                                    </form>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <hr class="my-3">

                        <form action="index.php?url=technician/update_status" method="POST" class="mb-3">
                            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                            <input type="hidden" name="ticket_code" value="<?= $ticket['ticket_code'] ?>">
                            
                            <div class="form-row">
                                <div class="form-group col-6">
                                    <label>Manual Status Override</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" <?= $ticket['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="assigned" <?= $ticket['status'] === 'assigned' ? 'selected' : '' ?>>Assigned</option>
                                        <option value="in_diagnosis" <?= $ticket['status'] === 'in_diagnosis' ? 'selected' : '' ?>>In Diagnosis</option>
                                        <option value="awaiting_parts" <?= $ticket['status'] === 'awaiting_parts' ? 'selected' : '' ?>>Awaiting Replacement Parts</option>
                                        <option value="repair_in_progress" <?= $ticket['status'] === 'repair_in_progress' ? 'selected' : '' ?>>Repair in Progress</option>
                                        <option value="ready" <?= $ticket['status'] === 'ready' ? 'selected' : '' ?>>Ready for Pickup / Delivery</option>
                                        <option value="resolved" <?= $ticket['status'] === 'resolved' ? 'selected' : '' ?>>Resolved / Closed</option>
                                        <option value="cancelled" <?= $ticket['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div class="form-group col-6">
                                    <label>Log Status Note / Work Done</label>
                                    <input type="text" name="note" placeholder="e.g. Replaced display capacitor" class="form-control">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-accent btn-sm">
                                <i class="fa-solid fa-rotate"></i> Update Status & Note
                            </button>
                        </form>

                        <hr>

                        <form action="index.php?url=technician/update_cost" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                            <input type="hidden" name="ticket_code" value="<?= $ticket['ticket_code'] ?>">
                            
                            <div class="form-row">
                                <div class="form-group col-6">
                                    <label>Estimated Repair Cost (₹ INR)</label>
                                    <input type="number" step="0.01" name="cost" value="<?= htmlspecialchars($ticket['estimated_cost']) ?>" class="form-control">
                                </div>
                                <div class="form-group col-6">
                                    <label>Quote Note</label>
                                    <input type="text" name="note" placeholder="e.g. Parts ₹800 + Labor ₹450" class="form-control">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="fa-solid fa-indian-rupee-sign"></i> Save Estimate Quote
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Customer Review / Rating Component -->
            <?php if (in_array($ticket['status'], ['ready', 'resolved']) && $role === 'customer'): ?>
                <div class="card mb-4 card-review-prompt">
                    <div class="card-header bg-success text-white">
                        <h3><i class="fa-solid fa-star"></i> Rate Your Service Experience</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($review): ?>
                            <div class="submitted-review">
                                <div class="review-stars mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa-solid fa-star <?= $i <= $review['rating'] ? 'star-filled' : 'star-empty' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="mb-0">"<?= htmlspecialchars($review['comment']) ?>"</p>
                            </div>
                        <?php else: ?>
                            <form action="index.php?url=ticket/review" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                                <input type="hidden" name="ticket_code" value="<?= $ticket['ticket_code'] ?>">
                                
                                <div class="form-group">
                                    <label>Your Rating (1 to 5 Stars)</label>
                                    <div class="star-rating-input">
                                        <select name="rating" required class="form-select style-stars">
                                            <option value="5" selected>⭐⭐⭐⭐⭐ (5/5) Excellent Service</option>
                                            <option value="4">⭐⭐⭐⭐ (4/5) Very Good</option>
                                            <option value="3">⭐⭐⭐ (3/5) Average</option>
                                            <option value="2">⭐⭐ (2/5) Below Expectations</option>
                                            <option value="1">⭐ (1/5) Poor</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Service Feedback Comment</label>
                                    <textarea name="comment" rows="2" placeholder="Tell us how our technician handled your repair..." class="form-control" required></textarea>
                                </div>

                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fa-solid fa-paper-plane"></i> Submit Feedback
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Ticket Comments & Conversation Thread -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fa-solid fa-comments"></i> Service Communication Thread</h3>
                </div>
                <div class="card-body">
                    <div class="comments-list">
                        <?php if (empty($comments)): ?>
                            <p class="text-muted">No messages posted yet. Use the form below to communicate with staff.</p>
                        <?php else: ?>
                            <?php foreach ($comments as $c): ?>
                                <div class="comment-item <?= !empty($c['is_internal']) ? 'comment-internal' : ($c['user_role'] === 'customer' ? 'comment-customer' : 'comment-staff') ?>">
                                    <div class="comment-header">
                                        <span class="comment-author">
                                            <i class="fa-solid fa-user-circle"></i> <?= htmlspecialchars($c['user_name']) ?>
                                            <span class="badge badge-role-<?= $c['user_role'] ?>"><?= ucfirst($c['user_role']) ?></span>
                                            <?php if (!empty($c['is_internal'])): ?>
                                                <span class="badge badge-warning"><i class="fa-solid fa-lock"></i> Staff Internal Note</span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="comment-time"><?= date('M d, Y h:i A', strtotime($c['created_at'])) ?></span>
                                    </div>
                                    <div class="comment-body">
                                        <?= nl2br(htmlspecialchars($c['comment'])) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (Session::isLoggedIn()): ?>
                        <hr class="my-4">
                        <form action="index.php?url=ticket/comment" method="POST" class="comment-form">
                            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                            <input type="hidden" name="ticket_code" value="<?= $ticket['ticket_code'] ?>">
                            
                            <div class="form-group">
                                <label for="comment">Post Message / Note</label>
                                <textarea name="comment" id="comment" rows="3" placeholder="<?= $isStaff ? 'Type your message or technical note...' : 'Type your message to technician...' ?>" required class="form-control"></textarea>
                            </div>

                            <?php if ($isStaff): ?>
                                <div class="form-group mb-3">
                                    <label class="internal-note-toggle" style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.9rem;">
                                        <input type="checkbox" name="is_internal" value="1" style="width:17px;height:17px;accent-color:#eab308;">
                                        <span><i class="fa-solid fa-lock text-warning"></i> <strong>Staff Private Note</strong> (Only visible to technicians & admins)</span>
                                    </label>
                                </div>
                            <?php endif; ?>

                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-reply"></i> Send Message
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Side: Sidebar Info & Status Timeline Log -->
        <div class="ticket-side-col">
            <!-- Cost Estimate Quote Box -->
            <div class="card mb-4 border-quote-highlight">
                <div class="card-header bg-quote">
                    <h3><i class="fa-solid fa-file-invoice-dollar"></i> Cost Estimate</h3>
                </div>
                <div class="card-body text-center">
                    <div class="quote-amount">
                        ₹<?= number_format($ticket['estimated_cost'], 2) ?>
                    </div>
                    <p class="quote-subtitle">Estimated Repair & Parts Quote</p>
                    <small class="text-muted">Final invoice generated upon physical testing completion.</small>
                </div>
            </div>

            <!-- Assigned Technician Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3><i class="fa-solid fa-user-gear"></i> Assigned Technician</h3>
                    <?php if ($role === 'admin'): ?>
                        <span class="badge badge-primary"><i class="fa-solid fa-shield-halved"></i> Admin</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($ticket['technician_name'])): ?>
                        <div class="tech-profile">
                            <div class="tech-avatar">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                            <div class="tech-details">
                                <h4><?= htmlspecialchars($ticket['technician_name']) ?></h4>
                                <p><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($ticket['technician_email']) ?></p>
                                <?php if (!empty($ticket['technician_phone'])): ?>
                                    <p><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($ticket['technician_phone']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3 text-muted">
                            <i class="fa-solid fa-clock-rotate-left fa-2x mb-2"></i>
                            <p>Awaiting technician assignment by manager.</p>
                        </div>
                    <?php endif; ?>

                    <?php if ($role === 'admin'): ?>
                        <hr class="my-3">
                        <form action="index.php?url=admin/assign" method="POST" class="assign-tech-form">
                            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">
                            <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                            <input type="hidden" name="ticket_code" value="<?= $ticket['ticket_code'] ?>">
                            <label for="technician_id" style="font-size:0.85rem;font-weight:700;display:block;margin-bottom:6px;color:var(--text-main);">
                                <?= empty($ticket['technician_id']) ? '<i class="fa-solid fa-user-plus text-primary"></i> Assign Technician:' : '<i class="fa-solid fa-user-pen text-primary"></i> Reassign Technician:' ?>
                            </label>
                            <div style="display:flex;gap:8px;">
                                <select name="technician_id" id="technician_id" class="form-control form-select" required>
                                    <option value="">-- Choose Tech --</option>
                                    <?php if (!empty($technicians)): ?>
                                        <?php foreach ($technicians as $tech): ?>
                                            <option value="<?= $tech['id'] ?>" <?= ($ticket['technician_id'] == $tech['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tech['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <button type="submit" class="btn btn-primary btn-sm" style="white-space:nowrap;">
                                    <i class="fa-solid fa-check"></i> <?= empty($ticket['technician_id']) ? 'Assign' : 'Change' ?>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Customer Details Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fa-solid fa-user-tag"></i> Customer Information</h3>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <?= htmlspecialchars($ticket['customer_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($ticket['customer_email']) ?></p>
                    <?php if (!empty($ticket['customer_phone'])): ?>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($ticket['customer_phone']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($ticket['customer_address'])): ?>
                        <p><strong>Address:</strong> <?= htmlspecialchars($ticket['customer_address']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Timeline History Audit Log -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-clock-rotate-left"></i> Repair History Log</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="timeline-list">
                        <?php foreach ($history as $h): ?>
                            <li class="timeline-item">
                                <div class="timeline-bullet"></div>
                                <div class="timeline-content">
                                    <div class="timeline-time"><?= date('M d, Y H:i', strtotime($h['created_at'])) ?></div>
                                    <div class="timeline-title">
                                        Status: <strong><?= strtoupper(str_replace('_', ' ', $h['status_to'])) ?></strong>
                                    </div>
                                    <div class="timeline-by">By <?= htmlspecialchars($h['user_name']) ?> (<?= ucfirst($h['user_role']) ?>)</div>
                                    <?php if (!empty($h['note'])): ?>
                                        <p class="timeline-note"><?= htmlspecialchars($h['note']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
