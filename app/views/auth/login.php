<?php
$defaultEmail = $email ?? '';
?>

<div class="auth-section">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            <h2>Sign In to FixMyDevice</h2>
            <p>Access your service tickets, technician desk, or management portal.</p>
        </div>

        <form action="index.php?url=login" method="POST" class="auth-form" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?= Session::generateCsrfToken() ?>">

            <div class="form-group">
                <label for="email"><i class="fa-solid fa-envelope"></i> Email Address</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($defaultEmail) ?>" placeholder="e.g. yourname@example.com" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Password</label>
                <div class="password-toggle-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" aria-label="Toggle password visibility">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg">
                <i class="fa-solid fa-right-to-bracket"></i> Login
            </button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account yet? <a href="index.php?url=register">Register for free</a></p>
            <p><a href="index.php?url=track"><i class="fa-solid fa-magnifying-glass"></i> Track complaint without login</a></p>
        </div>
    </div>
</div>
