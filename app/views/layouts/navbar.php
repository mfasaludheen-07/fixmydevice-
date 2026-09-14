<?php
$isLoggedIn = Session::isLoggedIn();
$user = Session::user();
$role = Session::role();
$flashSuccess = Session::getFlash('success');
$flashError = Session::getFlash('error');
?>
<header class="app-header">
    <div class="header-container">
        <div class="header-left">
            <button class="mobile-menu-toggle" id="mobileMenuBtn" aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>
            <a href="index.php?url=home" class="brand-logo">
                <div class="logo-icon">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div class="logo-text">
                    FixMy<span>Device</span>
                </div>
            </a>
        </div>

        <nav class="main-nav" id="mainNav">
            <div class="mobile-nav-header">
                <div class="brand-logo">
                    <div class="logo-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                    <div class="logo-text">FixMy<span>Device</span></div>
                </div>
                <button class="mobile-nav-close" id="mobileNavClose">&times;</button>
            </div>

            <a href="index.php?url=home" class="nav-link"><i class="fa-solid fa-house"></i> Home</a>
            <a href="index.php?url=track" class="nav-link"><i class="fa-solid fa-magnifying-glass"></i> Track Repair</a>
            
            <?php if ($isLoggedIn): ?>
                <?php if ($role === 'customer'): ?>
                    <a href="index.php?url=ticket/dashboard" class="nav-link"><i class="fa-solid fa-table-columns"></i> My Complaints</a>
                    <a href="index.php?url=ticket/create" class="nav-link btn-link-accent"><i class="fa-solid fa-plus-circle"></i> Submit Complaint</a>
                <?php elseif ($role === 'technician'): ?>
                    <a href="index.php?url=technician/dashboard" class="nav-link"><i class="fa-solid fa-toolbox"></i> Tech Workbench</a>
                <?php elseif ($role === 'admin'): ?>
                    <a href="index.php?url=admin/dashboard" class="nav-link"><i class="fa-solid fa-gauge-high"></i> Admin Portal</a>
                <?php endif; ?>
                <a href="index.php?url=profile" class="nav-link mobile-only-link"><i class="fa-solid fa-user-gear"></i> Profile & Security</a>
                <a href="index.php?url=logout" class="nav-link mobile-only-link text-danger"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            <?php endif; ?>
        </nav>

        <div class="header-right">
            <!-- Theme Toggle Button -->
            <button type="button" class="theme-toggle-btn" id="themeToggleBtn" title="Switch Dark / Light Mode" aria-label="Toggle theme">
                <i class="fa-solid fa-moon dark-icon"></i>
                <i class="fa-solid fa-sun light-icon"></i>
            </button>

            <div class="auth-actions">
                <?php if ($isLoggedIn): ?>
                    <a href="index.php?url=profile" class="user-pill" title="View Profile & Security">
                        <div class="user-avatar">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                        <div class="user-info">
                            <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                            <span class="user-role badge-role-<?= $role ?>"><?= ucfirst($role) ?></span>
                        </div>
                    </a>
                    <a href="index.php?url=logout" class="btn btn-sm btn-outline-danger" title="Sign Out">
                        <i class="fa-solid fa-right-from-bracket"></i> <span class="d-none-sm">Logout</span>
                    </a>
                <?php else: ?>
                    <a href="index.php?url=login" class="btn btn-outline"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
                    <a href="index.php?url=register" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
<div class="mobile-nav-backdrop" id="mobileNavBackdrop"></div>

<div class="container message-container">
    <?php if ($flashSuccess): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <span><?= htmlspecialchars($flashSuccess) ?></span>
            <button class="alert-close" onclick="this.parentElement.remove();">&times;</button>
        </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($flashError) ?></span>
            <button class="alert-close" onclick="this.parentElement.remove();">&times;</button>
        </div>
    <?php endif; ?>
</div>
