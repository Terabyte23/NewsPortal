<?php
require_once __DIR__ . '/../db.php';
$adminPage = 'categories';
$pageTitle = 'Rubriigid & Kategooriad';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_category'])) {
    $name = trim($_POST['name'] ?? '');
    if (!empty($name)) {
        $eName = $conn->real_escape_string($name);
        $conn->query("INSERT INTO category (name) VALUES ('$eName')");
        $msg = 'Uus rubriik edukalt lisatud!';
    }
}

if (isset($_GET['del'])) {
    $delId = (int)$_GET['del'];
    if ($delId > 0) {
        $conn->query("DELETE FROM category WHERE id = $delId");
        $msg = 'Rubriik kustutatud!';
    }
}

$cats = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM news n WHERE n.category_id = c.id) AS article_count FROM category c ORDER BY c.id ASC");

include 'header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 900; margin-bottom: 4px;">Rubriikide haldamine</h1>
        <p style="color: var(--text-secondary);">Halda portaali uudisterubriike ja vaata artiklite jaotust.</p>
    </div>
</div>

<?php if ($msg): ?>
    <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #34d399; padding: 10px 14px; border-radius: var(--radius-sm); margin-bottom: 20px;">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 30px;">
    
    <!-- ADD CATEGORY FORM -->
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 24px;">
        <h3 style="font-size: 1.125rem; font-weight: 800; margin-bottom: 16px;">Lisa uus rubriik</h3>
        <form method="POST" action="categories.php">
            <input type="hidden" name="new_category" value="1">
            <div class="form-group">
                <label>Rubriigi nimetus *</label>
                <input type="text" name="name" required class="form-control" placeholder="nt. Kosmos või Tervis">
            </div>
            <button type="submit" class="btn btn-primary-sm" style="width: 100%;">Lisa rubriik</button>
        </form>
    </div>

    <!-- CATEGORIES LIST -->
    <div class="admin-table-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Nimetus</th>
                    <th>Artikleid</th>
                    <th style="text-align: right;">Tegevus</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($cats && $cats->num_rows > 0): ?>
                    <?php while ($c = $cats->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $c['id'] ?></td>
                            <td><span class="badge-category"><?= htmlspecialchars($c['name']) ?></span></td>
                            <td><b><?= $c['article_count'] ?></b> tk</td>
                            <td style="text-align: right;">
                                <a href="categories.php?del=<?= $c['id'] ?>" class="action-btn-del" onclick="return confirm('Kas oled kindel?');">Kustuta</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include 'footer.php'; ?>
