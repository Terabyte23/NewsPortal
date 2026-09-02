<?php
require_once 'db.php';

$catId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$activeCategory = $catId;

$catRes = $conn->query("SELECT * FROM category WHERE id = $catId");
$category = ($catRes && $catRes->num_rows > 0) ? $catRes->fetch_assoc() : null;

if (!$category) {
    header("Location: index.php");
    exit;
}

$pageTitle = $category['name'] . ' uudised';

$gridSql = "SELECT n.*, c.name AS category_name, u.name AS author_name,
            (SELECT COUNT(*) FROM comments cm WHERE cm.news_id = n.id) AS comment_count
            FROM news n
            LEFT JOIN category c ON n.category_id = c.id
            LEFT JOIN users u ON n.user_id = u.id
            WHERE n.category_id = $catId
            ORDER BY n.id DESC";
$gridRes = $conn->query($gridSql);

include 'includes/header.php';
?>

<div class="container" style="padding: 40px 0 20px 0;">
    <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 35px; margin-bottom: 35px;">
        <span class="badge-category" style="margin-bottom: 10px; display: inline-block;">RUBRIIK</span>
        <h1 style="font-size: 2.25rem; font-weight: 900; margin-bottom: 8px;"><?= htmlspecialchars($category['name']) ?></h1>
        <p style="color: var(--text-secondary);">Kõik viimased sündmused, artiklid ja analüüsid teemal <?= htmlspecialchars($category['name']) ?>.</p>
    </div>

    <div class="news-grid">
        <?php if ($gridRes && $gridRes->num_rows > 0): ?>
            <?php while ($news = $gridRes->fetch_assoc()): 
                $imgUrl = get_article_image($news);
            ?>
                <article class="news-card">
                    <div class="card-image-box">
                        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($news['title']) ?>" loading="lazy">
                        <span class="card-category-badge"><?= htmlspecialchars($news['category_name'] ?? 'UUDIS') ?></span>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><a href="news.php?id=<?= $news['id'] ?>"><?= htmlspecialchars($news['title']) ?></a></h3>
                        <p class="card-excerpt"><?= htmlspecialchars(mb_substr(strip_tags($news['text']), 0, 140)) ?>...</p>
                        <div class="card-bottom-bar">
                            <span class="card-meta"><?= format_time_ago($news['created_at'] ?? null) ?></span>
                            <a href="news.php?id=<?= $news['id'] ?>" class="card-read-more">Loe edasi →</a>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                <h3>Selles kategoorias pole veel artikleid.</h3>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
