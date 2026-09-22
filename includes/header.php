<?php
if (!isset($conn)) {
    require_once __DIR__ . '/../db.php';
}

$currentUser = get_logged_in_user($conn);
$activePage = $activePage ?? 'home';

// Fetch categories for menu
$categoriesResult = $conn->query("SELECT * FROM category ORDER BY id ASC");
$allCategories = [];
if ($categoriesResult) {
    while ($cat = $categoriesResult->fetch_assoc()) {
        $allCategories[] = $cat;
    }
}

// Fetch 3 latest breaking news titles for ticker
$breakingResult = $conn->query("SELECT id, title FROM news ORDER BY id DESC LIMIT 4");
$breakingNews = [];
if ($breakingResult) {
    while ($row = $breakingResult->fetch_assoc()) {
        $breakingNews[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="et" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — Uudisteportaal' : 'Uudisteportaal — Viimased uudised ja teadus' ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Base CSS with dynamic cache buster -->
    <link rel="stylesheet" href="<?= (isset($depth) && $depth == 1 ? '../style.css' : 'style.css') . '?v=' . time() ?>">
    <script>
        // Init theme early to prevent flash of unstyled content
        (function() {
            const savedTheme = localStorage.getItem('np_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>
<body>

<!-- TOP TICKER BAR -->
<div class="top-bar">
    <div class="container top-bar-inner">
        <div class="top-left">
            <span class="live-clock" id="liveClock"></span>
            <span class="divider-dot">•</span>
            <div class="weather-widget" id="weatherWidget">
                <span class="weather-icon">⛅</span>
                <span class="weather-text">Tallinn +18°C</span>
            </div>
        </div>
        
        <div class="top-right">
            <a href="<?= isset($depth) && $depth == 1 ? '../saved.php' : 'saved.php' ?>" class="top-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
                Järjehoidjad <span class="badge-count" id="bookmarkCount">0</span>
            </a>
            
            <?php if (is_editor_or_admin($currentUser)): ?>
                <a href="<?= isset($depth) && $depth == 1 ? 'index.php' : 'admin/index.php' ?>" class="top-link top-admin-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                    Admin CMS
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MAIN HEADER -->
<header class="header">
    <div class="container nav-container">
        
        <!-- LOGO -->
        <a href="<?= isset($depth) && $depth == 1 ? '../index.php' : 'index.php' ?>" class="logo">
            <span class="logo-accent">NEWS</span><span class="logo-text">PORTAL</span>
            <span class="logo-pulse"></span>
        </a>

        <!-- NAV LINKS -->
        <nav class="main-nav" id="mainNav">
            <a href="<?= isset($depth) && $depth == 1 ? '../index.php' : 'index.php' ?>" class="<?= $activePage === 'home' ? 'active' : '' ?>">Avaleht</a>
            <?php foreach (array_slice($allCategories, 0, 5) as $cat): ?>
                <a href="<?= isset($depth) && $depth == 1 ? '../category.php?id=' . $cat['id'] : 'category.php?id=' . $cat['id'] ?>" class="<?= (isset($activeCategory) && $activeCategory == $cat['id']) ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- RIGHT CONTROLS -->
        <div class="nav-actions">
            
            <!-- SEARCH TRIGGER BUTTON -->
            <button class="icon-btn" id="openSearchBtn" title="Otsi uudiseid (Ctrl+K)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </button>

            <!-- THEME SWITCHER BUTTON -->
            <button class="icon-btn theme-btn" id="themeToggleBtn" title="Vaheta heledat/tumedat režiimi">
                <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            </button>

            <!-- USER PROFILE / LOGIN -->
            <?php if ($currentUser): ?>
                <div class="user-menu-wrapper">
                    <button class="user-avatar-btn" id="userMenuBtn">
                        <div class="avatar-circle">
                            <?= mb_strtoupper(mb_substr($currentUser['name'] ?? $currentUser['login'], 0, 1)) ?>
                        </div>
                        <span class="user-name-short"><?= htmlspecialchars(explode(' ', $currentUser['name'] ?? $currentUser['login'])[0]) ?></span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <div class="dropdown-header">
                            <strong><?= htmlspecialchars($currentUser['name'] ?? $currentUser['login']) ?></strong>
                            <small><?= htmlspecialchars($currentUser['email'] ?? $currentUser['login']) ?></small>
                            <span class="user-role-badge role-<?= strtolower($currentUser['status']) ?>"><?= htmlspecialchars($currentUser['status']) ?></span>
                        </div>
                        <a href="<?= isset($depth) && $depth == 1 ? '../profile.php' : 'profile.php' ?>">Minu profiil</a>
                        <a href="<?= isset($depth) && $depth == 1 ? '../saved.php' : 'saved.php' ?>">Salvestatud lood</a>
                        <?php if (is_editor_or_admin($currentUser)): ?>
                            <a href="<?= isset($depth) && $depth == 1 ? 'index.php' : 'admin/index.php' ?>" class="highlight-link">Admin paneel</a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a href="<?= isset($depth) && $depth == 1 ? '../logout.php' : 'logout.php' ?>" class="logout-link">Logi välja</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="<?= isset($depth) && $depth == 1 ? '../login.php' : 'login.php' ?>" class="btn btn-outline-sm">Logi sisse</a>
                    <a href="<?= isset($depth) && $depth == 1 ? '../register.php' : 'register.php' ?>" class="btn btn-primary-sm">Registreeru</a>
                </div>
            <?php endif; ?>

            <!-- MOBILE MENU TOGGLE -->
            <button class="mobile-toggle-btn" id="mobileToggleBtn" aria-label="Menüü">
                <span></span><span></span><span></span>
            </button>

        </div>
    </div>
</header>

<!-- BREAKING NEWS TICKER -->
<?php if (!empty($breakingNews) && $activePage === 'home'): ?>
<div class="breaking-news-bar">
    <div class="container breaking-inner">
        <div class="breaking-label">
            <span class="live-dot"></span>
            VÄRSKE
        </div>
        <div class="breaking-marquee">
            <div class="breaking-items">
                <?php foreach ($breakingNews as $b): ?>
                    <a href="news.php?id=<?= $b['id'] ?>" class="breaking-item">
                        ⚡ <?= htmlspecialchars($b['title']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- LIVE SEARCH MODAL -->
<div class="search-modal" id="searchModal">
    <div class="search-modal-backdrop" id="searchModalBackdrop"></div>
    <div class="search-modal-box">
        <div class="search-input-wrapper">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" id="modalSearchInput" placeholder="Otsi uudiseid, märksõnu või autoreid..." autocomplete="off">
            <button class="search-close-btn" id="closeSearchBtn">Esc</button>
        </div>
        <div class="search-results-box" id="modalSearchResults">
            <div class="search-hint">Sisesta vähemalt 2 tähemärki reaalajas otsinguks</div>
        </div>
    </div>
</div>

<!-- TOAST NOTIFICATION CONTAINER -->
<div class="toast-container" id="toastContainer"></div>
