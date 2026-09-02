<?php
require_once 'db.php';

$activePage = 'home';
$pageTitle = 'Viimased uudised ja olulised sündmused';

// Filtering and sorting
$selectedCat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$whereClause = "WHERE 1=1";
if ($selectedCat > 0) {
    $whereClause .= " AND n.category_id = $selectedCat";
}

$orderBy = "ORDER BY n.id DESC";
if ($sortBy === 'views') {
    $orderBy = "ORDER BY n.views DESC, n.id DESC";
} elseif ($sortBy === 'popular') {
    $orderBy = "ORDER BY n.likes DESC, n.views DESC";
}

// 1. Fetch Lead Featured Story (first or most viewed)
$leadSql = "SELECT n.*, c.name AS category_name, u.name AS author_name 
            FROM news n 
            LEFT JOIN category c ON n.category_id = c.id 
            LEFT JOIN users u ON n.user_id = u.id 
            ORDER BY n.is_featured DESC, n.views DESC, n.id DESC 
            LIMIT 1";
$leadRes = $conn->query($leadSql);
$leadStory = ($leadRes && $leadRes->num_rows > 0) ? $leadRes->fetch_assoc() : null;

// 2. Fetch Side Stories (3 items excluding lead)
$leadId = $leadStory ? (int)$leadStory['id'] : 0;
$sideSql = "SELECT n.*, c.name AS category_name 
            FROM news n 
            LEFT JOIN category c ON n.category_id = c.id 
            WHERE n.id != $leadId 
            ORDER BY n.id DESC 
            LIMIT 3";
$sideRes = $conn->query($sideSql);
$sideStories = [];
if ($sideRes) {
    while ($row = $sideRes->fetch_assoc()) {
        $sideStories[] = $row;
    }
}

// 3. Fetch Main News Grid
$gridSql = "SELECT n.*, c.name AS category_name, u.name AS author_name,
            (SELECT COUNT(*) FROM comments cm WHERE cm.news_id = n.id) AS comment_count
            FROM news n
            LEFT JOIN category c ON n.category_id = c.id
            LEFT JOIN users u ON n.user_id = u.id
            $whereClause
            $orderBy";
$gridRes = $conn->query($gridSql);

// 4. Fetch Most Popular for Sidebar (Top 5)
$popSql = "SELECT n.id, n.title, n.views, n.image_url, c.name AS category_name 
           FROM news n 
           LEFT JOIN category c ON n.category_id = c.id 
           ORDER BY n.views DESC, n.id DESC 
           LIMIT 5";
$popRes = $conn->query($popSql);
$popularStories = [];
if ($popRes) {
    while ($p = $popRes->fetch_assoc()) {
        $popularStories[] = $p;
    }
}

include 'includes/header.php';
?>

<!-- HERO SECTION -->
<?php if ($leadStory && $selectedCat === 0): ?>
<section class="hero-section">
    <div class="container hero-grid">
        
        <!-- LEAD STORY -->
        <article class="hero-lead-card">
            <img src="<?= htmlspecialchars(get_article_image($leadStory)) ?>" alt="<?= htmlspecialchars($leadStory['title']) ?>" class="hero-bg-img">
            <div class="hero-gradient-overlay"></div>
            
            <div class="hero-lead-content">
                <div class="hero-meta">
                    <span class="badge-category"><?= htmlspecialchars($leadStory['category_name'] ?? 'PÕHILUGU') ?></span>
                    <span style="font-size: 0.8125rem; opacity: 0.9;">⚡ ESILUGU</span>
                </div>
                
                <h2 class="hero-lead-title">
                    <a href="news.php?id=<?= $leadStory['id'] ?>">
                        <?= htmlspecialchars($leadStory['title']) ?>
                    </a>
                </h2>
                
                <p class="hero-lead-excerpt">
                    <?= htmlspecialchars(mb_substr(strip_tags($leadStory['text']), 0, 160)) ?>...
                </p>
                
                <div class="card-footer-meta">
                    <span class="meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <?= calculate_reading_time($leadStory['text']) ?> min lugemist
                    </span>
                    <span>•</span>
                    <span class="meta-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        <?= number_format($leadStory['views'] ?? 0) ?> vaatamist
                    </span>
                    <span>•</span>
                    <span><?= format_time_ago($leadStory['created_at'] ?? null) ?></span>
                </div>
            </div>
        </article>

        <!-- SIDE STORIES -->
        <div class="hero-side-grid">
            <?php foreach ($sideStories as $side): ?>
                <article class="hero-side-card">
                    <div class="side-img-wrapper">
                        <img src="<?= htmlspecialchars(get_article_image($side)) ?>" alt="<?= htmlspecialchars($side['title']) ?>">
                    </div>
                    <div class="side-card-body">
                        <span class="badge-category" style="font-size: 0.625rem; padding: 2px 8px;"><?= htmlspecialchars($side['category_name'] ?? 'UUDIS') ?></span>
                        <h3>
                            <a href="news.php?id=<?= $side['id'] ?>">
                                <?= htmlspecialchars($side['title']) ?>
                            </a>
                        </h3>
                        <div class="side-meta">
                            <span><?= format_time_ago($side['created_at'] ?? null) ?></span>
                            <span>•</span>
                            <span><?= calculate_reading_time($side['text']) ?> min</span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

    </div>
</section>
<?php endif; ?>

<!-- SECTION HEADER & FILTERS -->
<div class="container section-header-bar">
    <div class="section-title-wrap">
        <span class="section-pill-tag">UUDISVOOG</span>
        <h2 class="section-main-title">
            <?= $selectedCat > 0 ? 'Filtreeritud uudised' : 'Kõik värsked lood' ?>
        </h2>
    </div>

    <!-- CATEGORY FILTER PILLS -->
    <div class="category-tabs">
        <a href="index.php" class="category-tab <?= $selectedCat === 0 ? 'active' : '' ?>">Kõik</a>
        <?php foreach ($allCategories as $cat): ?>
            <a href="index.php?cat=<?= $cat['id'] ?>" class="category-tab <?= $selectedCat == $cat['id'] ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- MAIN LAYOUT -->
<main class="container content-layout">
    
    <!-- NEWS GRID -->
    <div class="news-grid-column">
        <?php if ($gridRes && $gridRes->num_rows > 0): ?>
            <div class="news-grid">
                <?php while ($news = $gridRes->fetch_assoc()): 
                    $imgUrl = get_article_image($news);
                    $readTime = calculate_reading_time($news['text']);
                ?>
                    <article class="news-card">
                        
                        <!-- CARD IMAGE & BADGES -->
                        <div class="card-image-box">
                            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($news['title']) ?>" loading="lazy">
                            <span class="card-category-badge"><?= htmlspecialchars($news['category_name'] ?? 'UUDIS') ?></span>
                            <button class="card-bookmark-btn" 
                                    data-id="<?= $news['id'] ?>" 
                                    data-title="<?= htmlspecialchars($news['title']) ?>" 
                                    data-image="<?= htmlspecialchars($imgUrl) ?>"
                                    data-category="<?= htmlspecialchars($news['category_name'] ?? 'Uudis') ?>"
                                    data-date="<?= format_estonian_date($news['created_at'] ?? null) ?>"
                                    title="Salvesta järjehoidjatesse">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
                            </button>
                        </div>

                        <!-- CARD BODY -->
                        <div class="card-body">
                            <h3 class="card-title">
                                <a href="news.php?id=<?= $news['id'] ?>">
                                    <?= htmlspecialchars($news['title']) ?>
                                </a>
                            </h3>

                            <p class="card-excerpt">
                                <?= htmlspecialchars(mb_substr(strip_tags($news['text']), 0, 140)) ?>...
                            </p>

                            <div class="card-bottom-bar">
                                <div class="card-meta">
                                    <span><?= format_time_ago($news['created_at'] ?? null) ?></span>
                                    <span>•</span>
                                    <span><?= $readTime ?> min</span>
                                </div>

                                <a href="news.php?id=<?= $news['id'] ?>" class="card-read-more">
                                    Loe edasi →
                                </a>
                            </div>
                        </div>

                    </article>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="widget" style="text-align: center; padding: 60px 20px;">
                <h3 style="margin-bottom: 10px;">Selles kategoorias uudiseid ei leitud</h3>
                <p style="color: var(--text-muted); margin-bottom: 20px;">Vali teine kategooria või naase pealehele.</p>
                <a href="index.php" class="btn btn-primary-sm">Kõik uudised</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        
        <!-- POPULAR NEWS WIDGET -->
        <div class="widget">
            <div class="widget-title">
                <h3>Loetuimad lood</h3>
                <span>TOP 5</span>
            </div>
            
            <div class="popular-list">
                <?php $rank = 1; foreach ($popularStories as $pop): ?>
                    <div class="popular-item">
                        <span class="popular-num">0<?= $rank++ ?></span>
                        <div class="popular-text">
                            <h4><a href="news.php?id=<?= $pop['id'] ?>"><?= htmlspecialchars($pop['title']) ?></a></h4>
                            <div class="popular-meta">
                                <span><?= htmlspecialchars($pop['category_name'] ?? 'Uudis') ?></span>
                                <span>•</span>
                                <span>👁️ <?= number_format($pop['views'] ?? 0) ?> vaatamist</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- WEATHER & FINANCIAL WIDGET -->
        <div class="widget">
            <div class="widget-title">
                <h3>Ilm Eestis</h3>
                <span>Live</span>
            </div>
            <div style="display: flex; flex-direction: column; gap: 10px; font-size: 0.875rem;">
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border-subtle);">
                    <span>Tallinn</span>
                    <b>⛅ +18°C</b>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border-subtle);">
                    <span>Tartu</span>
                    <b>☀️ +21°C</b>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border-subtle);">
                    <span>Narva / Jõhvi</span>
                    <b>🌤️ +19°C</b>
                </div>
            </div>
        </div>

        <!-- NEWSLETTER WIDGET -->
        <div class="widget newsletter-card">
            <h3 style="font-size: 1.125rem; font-weight: 800; margin-bottom: 8px;">Päeva kokkuvõte</h3>
            <p>Liitu 15 000+ lugejaga ja saa hommikune pressiülevaade tasuta.</p>
            <form class="newsletter-form">
                <div class="newsletter-input-group">
                    <input type="email" placeholder="Sinu e-post" required class="newsletter-input">
                    <button type="submit" class="btn btn-primary-sm">Liitu</button>
                </div>
            </form>
        </div>

    </aside>

</main>

<?php include 'includes/footer.php'; ?>
