<?php
$data = $formData ?? [];
?>

<div class="auth-section">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h2>Create Your Account</h2>
            <p>Register to submit complaints, track repairs, and manage your device warranties.</p>
        </div>

        <form action="index.php?url=register" method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">

            <div class="form-group">
                <label for="name"><i class="fa-solid fa-user"></i> Full Name *</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($data['name'] ?? '') ?>" placeholder="e.g. John Doe" required>
            </div>

            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Email Address *</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" placeholder="e.g. john@example.com" required>
            </div>

            <div class="form-row">
                <div class="form-group col-6">
                    <label for="phone"><i class="fa-solid fa-phone"></i> Phone Number</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($data['phone'] ?? '') ?>" placeholder="e.g. +1 555 123 4567">
                </div>

                <div class="form-group col-6">
                    <label for="address"><i class="fa-solid fa-location-dot"></i> Service Address</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($data['address'] ?? '') ?>" placeholder="e.g. 742 Evergreen Terrace">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-6">
                    <label for="password"><i class="fa-solid fa-lock"></i> Password *</label>
                    <div class="password-toggle-wrapper">
                        <input type="password" id="password" name="password" placeholder="Min 8 chars, letter & number" required oninput="checkPasswordStrength(this.value, 'regStrengthBar', 'regStrengthText')">
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" aria-label="Toggle password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength-container mt-2">
                        <div class="strength-bar-bg">
                            <div class="strength-bar-fill" id="regStrengthBar"></div>
                        </div>
                        <span class="strength-text" id="regStrengthText">Password strength</span>
                    </div>
                </div>

                <div class="form-group col-6">
                    <label for="confirm_password"><i class="fa-solid fa-lock"></i> Confirm Password *</label>
                    <div class="password-toggle-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('confirm_password', this)" aria-label="Toggle confirm password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="fa-solid fa-user-check"></i> Register Account
            </button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="index.php?url=login">Log in here</a></p>
        </div>
    </div>
</div>
