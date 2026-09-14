<div class="dashboard-wrapper">
    <div class="dashboard-header">
        <div class="dashboard-title">
            <h1><i class="fa-solid fa-user-gear text-primary"></i> Account Profile & Security</h1>
            <p>Manage your contact details, service address, and account credentials.</p>
        </div>
        <div class="dashboard-actions">
            <?php if ($user['role'] === 'customer'): ?>
                <a href="index.php?url=ticket/dashboard" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Complaints</a>
            <?php elseif ($user['role'] === 'technician'): ?>
                <a href="index.php?url=technician/dashboard" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Workbench</a>
            <?php else: ?>
                <a href="index.php?url=admin/dashboard" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Admin</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Profile Hero Card -->
    <div class="card profile-overview-card mb-4">
        <div class="card-body">
            <div class="profile-hero">
                <div class="profile-avatar-lg">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <div class="profile-meta">
                    <h2><?= htmlspecialchars($user['name']) ?></h2>
                    <p class="text-muted mb-2"><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                    <div class="profile-tags">
                        <span class="badge badge-role-<?= $user['role'] ?> badge-lg">
                            <i class="fa-solid fa-shield-halved"></i> <?= ucfirst($user['role']) ?> Account
                        </span>
                        <span class="profile-date"><i class="fa-solid fa-calendar-check"></i> Member since <?= date('M d, Y', strtotime($user['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid Layout: Left Edit Info, Right Password Change -->
    <div class="profile-grid">
        <!-- 1. Contact Information Form -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-id-card"></i> Contact & Address Information</h3>
            </div>
            <div class="card-body">
                <form action="index.php?url=update_profile" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">

                    <div class="form-group">
                        <label for="name">Full Name <span class="required-star">*</span></label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="email">Registered Email Address</label>
                        <div class="input-with-icon readonly-input">
                            <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly class="form-control">
                            <i class="fa-solid fa-lock input-trailing-icon" title="Email cannot be edited directly"></i>
                        </div>
                        <small class="text-muted">Primary account email used for service notifications and sign-in.</small>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone / Mobile Number</label>
                        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="e.g. +1 555 123 4567" class="form-control">
                        <small class="text-muted">Technicians use this number for repair updates and delivery confirmation.</small>
                    </div>

                    <div class="form-group">
                        <label for="address">Default Service Address</label>
                        <textarea id="address" name="address" rows="3" placeholder="Enter your full street address, apartment/suite, city, state and postal code..." class="form-control"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Profile Details
                    </button>
                </form>
            </div>
        </div>

        <!-- 2. Security & Password Form -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-key"></i> Security & Password</h3>
            </div>
            <div class="card-body">
                <form action="index.php?url=change_password" method="POST" id="changePasswordForm">
                    <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">

                    <div class="form-group">
                        <label for="current_password">Current Password <span class="required-star">*</span></label>
                        <div class="password-toggle-wrapper">
                            <input type="password" id="current_password" name="current_password" required placeholder="Enter current password" class="form-control">
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('current_password', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password <span class="required-star">*</span></label>
                        <div class="password-toggle-wrapper">
                            <input type="password" id="new_password" name="new_password" required placeholder="Min 8 characters, letters & numbers" class="form-control" oninput="checkPasswordStrength(this.value, 'profPassStrengthBar', 'profPassStrengthText')">
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('new_password', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <!-- Live Password Strength Meter -->
                        <div class="password-strength-container mt-2">
                            <div class="strength-bar-bg">
                                <div class="strength-bar-fill" id="profPassStrengthBar"></div>
                            </div>
                            <span class="strength-text" id="profPassStrengthText">Password strength</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password <span class="required-star">*</span></label>
                        <div class="password-toggle-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-type new password" class="form-control">
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('confirm_password', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-info py-2" style="font-size:0.85rem;">
                        <i class="fa-solid fa-circle-info"></i> Must be at least 8 characters long and contain both letters and digits.
                    </div>

                    <button type="submit" class="btn btn-accent">
                        <i class="fa-solid fa-shield-halved"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
