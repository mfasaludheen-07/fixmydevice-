<div class="container py-5" style="max-width: 780px; margin: 60px auto; text-align: center;">
    <div class="card" style="padding: 48px 32px; border-radius: var(--radius-lg); box-shadow: var(--shadow-lg); border: 1px solid var(--border-color);">
        <div style="width: 80px; height: 80px; margin: 0 auto 24px; background: rgba(239, 68, 68, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--danger); font-size: 2.5rem;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <h1 style="font-size: 2.75rem; font-weight: 800; color: var(--text-main); margin-bottom: 12px; font-family: 'Outfit', sans-serif;">
            404 <span style="font-size: 1.5rem; font-weight: 600; color: var(--text-muted); display: block;">Page Not Found</span>
        </h1>

        <p style="color: var(--text-muted); font-size: 1.05rem; line-height: 1.6; max-width: 520px; margin: 0 auto 28px;">
            The page, service action, or repair ticket URL you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </p>

        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; max-width: 500px; margin: 0 auto 32px;">
            <form action="index.php" method="GET" style="display: flex; gap: 8px;">
                <input type="hidden" name="url" value="track">
                <input type="text" name="code" placeholder="Track a ticket code (e.g. TKT-2026-XXXX)..." style="flex: 1; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 0.95rem;">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-magnifying-glass"></i> Track
                </button>
            </form>
        </div>

        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="index.php?url=home" class="btn btn-primary">
                <i class="fa-solid fa-house"></i> Return to Homepage
            </a>
            <a href="index.php?url=track" class="btn btn-outline">
                <i class="fa-solid fa-magnifying-glass"></i> Public Tracker
            </a>
            <a href="index.php?url=login" class="btn btn-outline">
                <i class="fa-solid fa-right-to-bracket"></i> Account Login
            </a>
        </div>
    </div>
</div>
