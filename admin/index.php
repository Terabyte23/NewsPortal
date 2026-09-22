<?php
require_once __DIR__ . '/auth_check.php';
$adminPage = 'dashboard';
$pageTitle = 'Juhtpaneel';

// KPI Stats
$totalNews = $conn->query("SELECT COUNT(*) AS c FROM news")->fetch_assoc()['c'] ?? 0;
$totalViews = $conn->query("SELECT SUM(views) AS s FROM news")->fetch_assoc()['s'] ?? 0;
$totalComments = $conn->query("SELECT COUNT(*) AS c FROM comments")->fetch_assoc()['c'] ?? 0;
$totalUsers = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'] ?? 0;

// Recent News
$recentNews = $conn->query("SELECT n.*, c.name AS category_name FROM news n LEFT JOIN category c ON n.category_id = c.id ORDER BY n.id DESC LIMIT 5");

// Recent Comments
$recentComments = $conn->query("SELECT cm.*, n.title AS news_title FROM comments cm LEFT JOIN news n ON cm.news_id = n.id ORDER BY cm.id DESC LIMIT 5");

include 'header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 900; margin-bottom: 4px;">Toimetuse ülevaade</h1>
        <p style="color: var(--text-secondary);">Reaalajas statistika ja viimased tegevused portaalis.</p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="news-add.php" class="btn btn-primary-sm">➕ Lisa uus artikkel</a>
        <a href="seed.php" class="btn btn-outline-sm">⚡ Täida demoandmetega</a>
    </div>
</div>

<!-- KPI GRID -->
<div class="kpi-grid">
    <div class="kpi-card">
        <span class="kpi-label">Uudiseid kokku</span>
        <span class="kpi-value"><?= number_format($totalNews) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label">Vaatamisi kokku</span>
        <span class="kpi-value" style="color: var(--accent-blue);"><?= number_format($totalViews) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label">Kommentaare</span>
        <span class="kpi-value" style="color: var(--accent-green);"><?= number_format($totalComments) ?></span>
    </div>
    <div class="kpi-card">
        <span class="kpi-label">Kasutajaid</span>
        <span class="kpi-value" style="color: var(--accent-purple);"><?= number_format($totalUsers) ?></span>
    </div>
</div>

<!-- RECENT NEWS TABLE -->
<div class="admin-table-card">
    <div class="admin-table-header">
        <h3 style="font-size: 1.125rem; font-weight: 800;">Viimati lisatud uudised</h3>
        <a href="news.php" style="color: var(--primary); font-weight: 700; font-size: 0.875rem;">Vaata kõiki →</a>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th>Pealkiri</th>
                <th>Rubriik</th>
                <th>Vaatamisi</th>
                <th>Kuupäev</th>
                <th style="text-align: right;">Tegevused</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($recentNews && $recentNews->num_rows > 0): ?>
                <?php while ($row = $recentNews->fetch_assoc()): ?>
                    <tr>
                        <td><b>#<?= $row['id'] ?></b></td>
                        <td>
                            <a href="../news.php?id=<?= $row['id'] ?>" target="_blank" style="font-weight: 700;">
                                <?= htmlspecialchars(mb_substr($row['title'], 0, 55)) ?>...
                            </a>
                        </td>
                        <td><span class="badge-category" style="font-size: 0.6875rem;"><?= htmlspecialchars($row['category_name'] ?? 'Määramata') ?></span></td>
                        <td>👁️ <?= number_format($row['views'] ?? 0) ?></td>
                        <td><?= format_time_ago($row['created_at'] ?? null) ?></td>
                        <td style="text-align: right;">
                            <div class="action-btns" style="justify-content: flex-end;">
                                <a href="news-edit.php?id=<?= $row['id'] ?>" class="action-btn-edit">Muuda</a>
                                <span style="color: var(--border-color);">|</span>
                                <a href="news-delete.php?id=<?= $row['id'] ?>" class="action-btn-del" onclick="return confirm('Kas oled kindel, et soovid selle uudise kustutada?');">Kustuta</a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center; padding: 30px;">Hetkel pole ühtegi uudist.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- RECENT COMMENTS -->
<div class="admin-table-card">
    <div class="admin-table-header">
        <h3 style="font-size: 1.125rem; font-weight: 800;">Viimased kommentaarid</h3>
        <a href="comments.php" style="color: var(--primary); font-weight: 700; font-size: 0.875rem;">Modereeri kõiki →</a>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th>Autor</th>
                <th>Uudis</th>
                <th>Kommentaar</th>
                <th>Kuupäev</th>
                <th style="text-align: right;">Tegevus</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($recentComments && $recentComments->num_rows > 0): ?>
                <?php while ($c = $recentComments->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $c['id'] ?></td>
                        <td><b><?= htmlspecialchars($c['author_name'] ?? 'Lugeja') ?></b></td>
                        <td><a href="../news.php?id=<?= $c['news_id'] ?>" target="_blank"><?= htmlspecialchars(mb_substr($c['news_title'] ?? 'Uudis', 0, 30)) ?>...</a></td>
                        <td><?= htmlspecialchars(mb_substr($c['text'], 0, 50)) ?>...</td>
                        <td><?= format_time_ago($c['date']) ?></td>
                        <td style="text-align: right;">
                            <a href="comments.php?del=<?= $c['id'] ?>" class="action-btn-del" onclick="return confirm('Kustuta kommentaar?');">Kustuta</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center; padding: 30px;">Kommentaare pole veel.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
