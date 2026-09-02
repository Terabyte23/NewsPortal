<?php
require_once __DIR__ . '/../db.php';

$currentUser = get_logged_in_user($conn);
if (!is_editor_or_admin($currentUser)) {
    header("Location: ../login.php");
    exit;
}

$adminPage = $adminPage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="et" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — NewsPortal CMS' : 'Toimetuse CMS — NewsPortal' ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800;900&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../style.css">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('np_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>
<body>

<!-- TOP ADMIN BAR -->
<div class="top-bar">
    <div class="container top-bar-inner">
        <div class="top-left">
            <span style="font-weight: 700; color: #f8fafc;">⚡ NewsPortal Toimetuse Juhtpaneel</span>
            <span class="divider-dot">•</span>
            <span>Kasutaja: <b><?= htmlspecialchars($currentUser['name']) ?></b> (<?= htmlspecialchars($currentUser['status']) ?>)</span>
        </div>
        <div class="top-right">
            <a href="../index.php" class="top-link" target="_blank">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                Ava avalik veebileht ↗
            </a>
            <span class="divider-dot">•</span>
            <a href="../logout.php" class="top-link" style="color: #f87171;">Logi välja</a>
        </div>
    </div>
</div>

<!-- ADMIN WRAPPER -->
<div class="admin-layout">
    
    <!-- SIDEBAR -->
    <aside class="admin-sidebar">
        <div style="padding: 10px 16px 20px 16px;">
            <a href="index.php" class="logo" style="font-size: 1.375rem;">
                <span class="logo-accent">NEWS</span><span class="logo-text">CMS</span>
            </a>
        </div>

        <a href="index.php" class="admin-nav-item <?= $adminPage === 'dashboard' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            Ülevaade (Dashboard)
        </a>

        <a href="news.php" class="admin-nav-item <?= $adminPage === 'news' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v1m2 13a2 2 0 0 1-2-2V7m2 13a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
            Kõik uudised
        </a>

        <a href="news-add.php" class="admin-nav-item <?= $adminPage === 'news-add' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Lisa uus artikkel
        </a>

        <a href="categories.php" class="admin-nav-item <?= $adminPage === 'categories' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
            Rubriigid & Kategooriad
        </a>

        <a href="comments.php" class="admin-nav-item <?= $adminPage === 'comments' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            Kommentaaride modereerimine
        </a>

        <?php if (is_admin($currentUser)): ?>
            <a href="users.php" class="admin-nav-item <?= $adminPage === 'users' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                Kasutajate haldus
            </a>
        <?php endif; ?>

        <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid var(--border-subtle);">
            <a href="seed.php" class="admin-nav-item" style="color: var(--accent-amber);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                Täida demo andmetega
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="admin-main-content">
