<section class="hero-section">
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fa-solid fa-screwdriver-wrench"></i> Fast & Reliable Hardware Appliance Repair
            </div>
            <h1 class="hero-title">
                Hardware Repair & Service <span>Ticketing System</span>
            </h1>
            <p class="hero-subtitle">
                Got a broken TV, non-cooling refrigerator, leaking AC, or faulty laptop? Submit your service request online, track live repair progress, and get verified technicians at your doorstep.
            </p>

            <div class="hero-cta-buttons">
                <a href="index.php?url=ticket/create" class="btn btn-primary btn-lg">
                    <i class="fa-solid fa-plus-circle"></i> Register Complaint Now
                </a>
                <a href="index.php?url=track" class="btn btn-outline-light btn-lg">
                    <i class="fa-solid fa-magnifying-glass"></i> Track Ticket Status
                </a>
            </div>

            <div class="quick-track-box">
                <form action="index.php" method="GET" class="quick-track-form">
                    <input type="hidden" name="url" value="track">
                    <div class="input-with-icon">
                        <i class="fa-solid fa-ticket"></i>
                        <input type="text" name="code" placeholder="Enter your Ticket Code (e.g. TKT-2026-XXXX)..." required>
                    </div>
                    <button type="submit" class="btn btn-accent">
                        <i class="fa-solid fa-arrow-right"></i> Check Status
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- System Statistics Bar -->
<section class="stats-section">
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon icon-blue"><i class="fa-solid fa-clipboard-check"></i></div>
            <div class="stat-info">
                <h3><?= number_format($stats['resolved'] ?? ($stats['total'] ?? 0)) ?></h3>
                <p>Complaints Resolved</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-amber"><i class="fa-solid fa-wrench"></i></div>
            <div class="stat-info">
                <h3><?= number_format($stats['in_progress'] ?? 0) ?></h3>
                <p>Active Repairs</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-green"><i class="fa-solid fa-user-shield"></i></div>
            <div class="stat-info">
                <h3>99.4%</h3>
                <p>Satisfaction Rate</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-purple"><i class="fa-solid fa-clock-rotate-left"></i></div>
            <div class="stat-info">
                <h3>24-48 hrs</h3>
                <p>Average Turnaround</p>
            </div>
        </div>
    </div>
</section>

<!-- Hardware Repair Categories -->
<section class="categories-section" id="categories">
    <div class="section-container">
        <div class="section-header">
            <span class="sub-title">Expert Service Coverage</span>
            <h2>Supported Hardware Categories</h2>
            <p>Select your appliance category to register a service ticket or view repair procedures.</p>
        </div>

        <div class="categories-grid">
            <?php 
            $iconMap = [
                'smart-tvs' => 'fa-tv',
                'refrigerators' => 'fa-snowflake',
                'washing-machines' => 'fa-soap',
                'air-conditioners' => 'fa-wind',
                'laptops-pcs' => 'fa-laptop',
                'microwaves' => 'fa-fire-burner'
            ];
            foreach ($categories as $cat): 
                $iconClass = $iconMap[$cat['slug']] ?? 'fa-screwdriver-wrench';
            ?>
                <div class="category-card">
                    <div class="category-icon">
                        <i class="fa-solid <?= $iconClass ?>"></i>
                    </div>
                    <h3><?= htmlspecialchars($cat['name']) ?></h3>
                    <p><?= htmlspecialchars($cat['description']) ?></p>
                    <a href="index.php?url=ticket/create&category=<?= $cat['id'] ?>" class="category-link">
                        Book Repair Request <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="workflow-section">
    <div class="section-container">
        <div class="section-header">
            <span class="sub-title">Seamless Repair Journey</span>
            <h2>How FixMyDevice Works</h2>
            <p>Four simple steps to get your hardware repaired by certified professionals.</p>
        </div>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">01</div>
                <div class="step-icon"><i class="fa-solid fa-file-pen"></i></div>
                <h3>Submit Complaint</h3>
                <p>Register your hardware item with brand, model, serial # and problem details online.</p>
            </div>

            <div class="step-card">
                <div class="step-number">02</div>
                <div class="step-icon"><i class="fa-solid fa-magnifying-glass-chart"></i></div>
                <h3>Technician Diagnosis</h3>
                <p>A certified hardware technician is assigned to inspect the fault and issue a quote.</p>
            </div>

            <div class="step-card">
                <div class="step-number">03</div>
                <div class="step-icon"><i class="fa-solid fa-microchip"></i></div>
                <h3>Hardware Repair</h3>
                <p>Component replacement and testing using 100% genuine manufacturer parts.</p>
            </div>

            <div class="step-card">
                <div class="step-number">04</div>
                <div class="step-icon"><i class="fa-solid fa-truck-fast"></i></div>
                <h3>Pickup / Delivery</h3>
                <p>Receive your fully tested device back with a service warranty certificate.</p>
            </div>
        </div>
    </div>
</section>


<!-- Customer Feedback & Reviews -->
<?php if (!empty($reviews)): ?>
<section class="reviews-section">
    <div class="section-container">
        <div class="section-header">
            <span class="sub-title">Verified Customer Reviews</span>
            <h2>What Customers Say About Our Hardware Service</h2>
        </div>

        <div class="reviews-grid">
            <?php foreach ($reviews as $rev): ?>
                <div class="review-card">
                    <div class="review-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fa-solid fa-star <?= $i <= $rev['rating'] ? 'star-filled' : 'star-empty' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="review-text">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                    <div class="review-meta">
                        <div class="reviewer-name"><?= htmlspecialchars($rev['customer_name']) ?></div>
                        <div class="reviewer-device"><?= htmlspecialchars($rev['brand'] . ' ' . $rev['device_name']) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
