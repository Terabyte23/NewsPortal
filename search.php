<?php
require_once 'db.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$pageTitle = 'Otsing: ' . ($q ? htmlspecialchars($q) : 'Kõik uudised');

$results = [];
if (!empty($q)) {
    $escaped = $conn->real_escape_string($q);
    $sql = "SELECT n.*, c.name AS category_name 
            FROM news n 
            LEFT JOIN category c ON n.category_id = c.id 
            WHERE n.title LIKE '%$escaped%' OR n.text LIKE '%$escaped%' OR n.tags LIKE '%$escaped%'
            ORDER BY n.id DESC";
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $results[] = $row;
        }
    }
}

include 'includes/header.php';
?>

<main class="container" style="padding: 40px 0 60px 0;">
    <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 35px; margin-bottom: 35px;">
        <h1 style="font-size: 2rem; font-weight: 900; margin-bottom: 16px;">Otsing portaalis</h1>
        <form action="search.php" method="GET" style="display: flex; gap: 10px; max-width: 600px;">
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Sisesta otsisõna..." required class="form-control">
            <button type="submit" class="btn btn-primary-sm">Otsi</button>
        </form>
    </div>

    <?php if (!empty($q)): ?>
        <h3 style="margin-bottom: 20px;">Otsingu tulemused päringule "<?= htmlspecialchars($q) ?>" (<?= count($results) ?>):</h3>
        <?php if (!empty($results)): ?>
            <div class="news-grid">
                <?php foreach ($results as $news): ?>
                    <article class="news-card">
                        <div class="card-image-box">
                            <img src="<?= htmlspecialchars(get_article_image($news)) ?>" alt="">
                            <span class="card-category-badge"><?= htmlspecialchars($news['category_name'] ?? 'UUDIS') ?></span>
                        </div>
                        <div class="card-body">
                            <h3 class="card-title"><a href="news.php?id=<?= $news['id'] ?>"><?= htmlspecialchars($news['title']) ?></a></h3>
                            <p class="card-excerpt"><?= htmlspecialchars(mb_substr(strip_tags($news['text']), 0, 140)) ?>...</p>
                            <div class="card-bottom-bar">
                                <span><?= format_time_ago($news['created_at'] ?? null) ?></span>
                                <a href="news.php?id=<?= $news['id'] ?>" class="card-read-more">Loe edasi →</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px;">
                <h3>Vasteid ei leitud. Proovi teisi märksõnu.</h3>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
