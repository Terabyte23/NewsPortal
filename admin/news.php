<?php
require_once __DIR__ . '/../db.php';
$adminPage = 'news';
$pageTitle = 'Kõik uudised';

$catFilter = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$search = isset($_GET['s']) ? trim($_GET['s']) : '';

$where = "WHERE 1=1";
if ($catFilter > 0) $where .= " AND n.category_id = $catFilter";
if (!empty($search)) {
    $e = $conn->real_escape_string($search);
    $where .= " AND (n.title LIKE '%$e%' OR n.text LIKE '%$e%')";
}

$sql = "SELECT n.*, c.name AS category_name, u.name AS author_name,
        (SELECT COUNT(*) FROM comments cm WHERE cm.news_id = n.id) AS comments_count
        FROM news n
        LEFT JOIN category c ON n.category_id = c.id
        LEFT JOIN users u ON n.user_id = u.id
        $where
        ORDER BY n.id DESC";
$newsRes = $conn->query($sql);

$categories = $conn->query("SELECT * FROM category ORDER BY name ASC");

include 'header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 900; margin-bottom: 4px;">Uudiste haldamine</h1>
        <p style="color: var(--text-secondary);">Kokku artikleid: <b><?= $newsRes ? $newsRes->num_rows : 0 ?></b></p>
    </div>
    <a href="news-add.php" class="btn btn-primary-sm">➕ Lisa uus artikkel</a>
</div>

<!-- FILTER BAR -->
<div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; margin-bottom: 25px; display: flex; gap: 12px; flex-wrap: wrap;">
    <form method="GET" action="news.php" style="display: flex; gap: 10px; flex-grow: 1;">
        <input type="text" name="s" value="<?= htmlspecialchars($search) ?>" placeholder="Otsi pealkirja või teksti järgi..." class="form-control" style="max-width: 320px;">
        <select name="cat" class="form-control" style="max-width: 200px;">
            <option value="0">Kõik kategooriad</option>
            <?php if ($categories): ?>
                <?php while ($c = $categories->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>" <?= $catFilter == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>
        <button type="submit" class="btn btn-secondary" style="padding: 0 18px;">Filtreeri</button>
        <?php if (!empty($search) || $catFilter > 0): ?>
            <a href="news.php" class="btn btn-outline-sm" style="display: flex; align-items: center;">Tühjenda</a>
        <?php endif; ?>
    </form>
</div>

<!-- NEWS TABLE -->
<div class="admin-table-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th style="width: 70px;">Pilt</th>
                <th>Pealkiri</th>
                <th>Rubriik</th>
                <th>Autor</th>
                <th>Statistika</th>
                <th>Kuupäev</th>
                <th style="text-align: right;">Tegevused</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($newsRes && $newsRes->num_rows > 0): ?>
                <?php while ($n = $newsRes->fetch_assoc()): ?>
                    <tr>
                        <td><b>#<?= $n['id'] ?></b></td>
                        <td>
                            <div style="width: 55px; height: 40px; border-radius: 4px; overflow: hidden; background: #222;">
                                <img src="<?= htmlspecialchars(get_article_image($n)) ?>" alt="" style="width:100%; height:100%; object-fit: cover;">
                            </div>
                        </td>
                        <td>
                            <a href="../news.php?id=<?= $n['id'] ?>" target="_blank" style="font-weight: 700;">
                                <?= htmlspecialchars($n['title']) ?>
                            </a>
                            <?php if (!empty($n['is_featured'])): ?>
                                <span style="background: var(--primary-light); color: var(--primary); font-size: 0.6875rem; padding: 1px 6px; border-radius: 4px; font-weight: 800; margin-left: 6px;">ESILUGU</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge-category" style="font-size: 0.6875rem;"><?= htmlspecialchars($n['category_name'] ?? 'Määramata') ?></span></td>
                        <td><?= htmlspecialchars($n['author_name'] ?? 'Toimetus') ?></td>
                        <td>👁️ <?= number_format($n['views'] ?? 0) ?> | 💬 <?= $n['comments_count'] ?></td>
                        <td><?= format_time_ago($n['created_at'] ?? null) ?></td>
                        <td style="text-align: right;">
                            <div class="action-btns" style="justify-content: flex-end;">
                                <a href="news-edit.php?id=<?= $n['id'] ?>" class="action-btn-edit">Muuda</a>
                                <span style="color: var(--border-color);">|</span>
                                <a href="news-delete.php?id=<?= $n['id'] ?>" class="action-btn-del" onclick="return confirm('Kas soovid artikli kindlasti kustutada?');">Kustuta</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8" style="text-align: center; padding: 40px;">Uudiseid ei leitud.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
