<div class="track-wrapper">
    <div class="track-hero">
        <div class="track-hero-content">
            <div class="track-icon-badge">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <h1>Public Complaint Tracker</h1>
            <p>Check real-time hardware repair status, technician updates, and service stage using your unique Ticket Code.</p>

            <form action="index.php?url=track" method="GET" class="track-search-form">
                <input type="hidden" name="url" value="track">
                <div class="track-input-group">
                    <i class="fa-solid fa-ticket"></i>
                    <input type="text" name="code" value="<?= htmlspecialchars($code) ?>" placeholder="Enter your Ticket Code (e.g. TKT-2026-XXXX)..." required autofocus>
                    <button type="submit" class="btn btn-accent">
                        <i class="fa-solid fa-magnifying-glass"></i> Track Ticket
                    </button>
                </div>
            </form>
            <small class="track-hint"><i class="fa-solid fa-shield-halved"></i> Enter the unique ticket code received upon registering your service request</small>
        </div>
    </div>

    <?php if ($searched): ?>
        <div class="container mt-4 mb-5">
            <?php if (!$ticket): ?>
                <div class="card text-center p-5">
                    <div class="empty-icon text-danger"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <h3>Ticket Code Not Found</h3>
                    <p>No complaint record was found matching ticket code "<strong><?= htmlspecialchars($code) ?></strong>". Please double check your code or register a new request.</p>
                    <div class="mt-3">
                        <a href="index.php?url=ticket/create" class="btn btn-primary"><i class="fa-solid fa-plus-circle"></i> Register New Complaint</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Render Ticket Result Details -->
                <div class="ticket-view-wrapper p-0">
                    <div class="ticket-header-card">
                        <div class="ticket-header-main">
                            <div>
                                <span class="ticket-label">FOUND COMPLAINT TICKET</span>
                                <h2><i class="fa-solid fa-hashtag"></i> <?= htmlspecialchars($ticket['ticket_code']) ?></h2>
                                <p class="mb-0 text-muted"><?= htmlspecialchars($ticket['brand'] . ' ' . $ticket['device_name']) ?> - Category: <strong><?= htmlspecialchars($ticket['category_name']) ?></strong></p>
                            </div>
                            <div>
                                <span class="badge badge-lg badge-status-<?= $ticket['status'] ?>">
                                    STATUS: <?= str_replace('_', ' ', strtoupper($ticket['status'])) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Stepper Progress Bar -->
                        <?php
                        $statuses = [
                            'pending' => 1,
                            'assigned' => 2,
                            'in_diagnosis' => 3,
                            'awaiting_parts' => 3,
                            'repair_in_progress' => 4,
                            'ready' => 5,
                            'resolved' => 6
                        ];
                        $currentStep = $statuses[$ticket['status']] ?? 1;
                        $progressPct = max(0, min(100, ($currentStep - 1) * 20));
                        ?>
                        <div class="stepper-wrapper mt-4">
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
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h3><i class="fa-solid fa-clock-rotate-left"></i> Live Progress Log</h3>
                        </div>
                        <div class="card-body">
                            <ul class="timeline-list">
                                <?php foreach ($history as $h): ?>
                                    <li class="timeline-item">
                                        <div class="timeline-bullet"></div>
                                        <div class="timeline-content">
                                            <div class="timeline-time"><?= date('M d, Y H:i', strtotime($h['created_at'])) ?></div>
                                            <div class="timeline-title">
                                                Stage: <strong><?= strtoupper(str_replace('_', ' ', $h['status_to'])) ?></strong>
                                            </div>
                                            <?php if (!empty($h['note'])): ?>
                                                <p class="timeline-note"><?= htmlspecialchars($h['note']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <div class="mt-4 pt-3 border-top text-center">
                                <a href="index.php?url=ticket/view/<?= $ticket['ticket_code'] ?>" class="btn btn-primary">
                                    <i class="fa-solid fa-user-lock"></i> Log in to reply or view full details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
