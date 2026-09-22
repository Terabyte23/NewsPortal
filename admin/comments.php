<?php
require_once __DIR__ . '/auth_check.php';
$adminPage = 'comments';
$pageTitle = 'Kommentaaride modereerimine';

$msg = '';
if (isset($_GET['del'])) {
    admin_verify_csrf();
    $delId = (int)$_GET['del'];
    if ($delId > 0) {
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $delId);
            $stmt->execute();
            $msg = 'Kommentaar edukalt kustutatud!';
        }
    }
}

$sql = "SELECT cm.*, n.title AS news_title FROM comments cm LEFT JOIN news n ON cm.news_id = n.id ORDER BY cm.id DESC";
$commentsRes = $conn->query($sql);

include 'header.php';
?>

<div style="margin-bottom: 25px;">
    <h1 style="font-size: 2rem; font-weight: 900; margin-bottom: 4px;">Kommentaaride modereerimine</h1>
    <p style="color: var(--text-secondary);">Vaata ja modereeri lugejate arvamusi.</p>
</div>

<?php if ($msg): ?>
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #34d399; padding: 10px 14px; border-radius: var(--radius-sm); margin-bottom: 20px;">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div class="admin-table-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>Autor</th>
                <th>Artikkel</th>
                <th>Kommentaari tekst</th>
                <th>Aeg</th>
                <th style="text-align: right;">Tegevus</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($commentsRes && $commentsRes->num_rows > 0): ?>
                <?php while ($cm = $commentsRes->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $cm['id'] ?></td>
                        <td><b><?= htmlspecialchars($cm['author_name'] ?? 'Lugeja') ?></b></td>
                        <td>
                            <a href="../news.php?id=<?= $cm['news_id'] ?>" target="_blank" style="font-weight: 600;">
                                <?= htmlspecialchars(mb_substr($cm['news_title'] ?? 'Uudis', 0, 35)) ?>...
                            </a>
                        </td>
                        <td style="max-width: 320px;"><?= nl2br(htmlspecialchars($cm['text'])) ?></td>
                        <td><?= format_time_ago($cm['date']) ?></td>
                        <td style="text-align: right;">
                            <a href="comments.php?del=<?= $cm['id'] ?>&csrf_token=<?= csrf_token() ?>" class="action-btn-del" onclick="return confirm('Kustuta see kommentaar?');">Kustuta</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center; padding: 40px;">Kommentaare pole.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
