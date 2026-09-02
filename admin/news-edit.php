<?php
require_once __DIR__ . '/../db.php';
$adminPage = 'news';
$pageTitle = 'Muuda artiklit';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: news.php");
    exit;
}

$newsRes = $conn->query("SELECT * FROM news WHERE id = $id");
$news = ($newsRes && $newsRes->num_rows > 0) ? $newsRes->fetch_assoc() : null;

if (!$news) {
    die("Uudist ei leitud.");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $text = trim($_POST['text'] ?? '');
    $catId = (int)($_POST['category_id'] ?? 1);
    $imageUrl = trim($_POST['image_url'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

    if (!empty($title) && !empty($text)) {
        $eTitle = $conn->real_escape_string($title);
        $eText = $conn->real_escape_string($text);
        $eImg = $conn->real_escape_string($imageUrl);
        $eTags = $conn->real_escape_string($tags);

        $sql = "UPDATE news SET 
                title = '$eTitle', 
                text = '$eText', 
                category_id = $catId, 
                image_url = '$eImg', 
                is_featured = $isFeatured, 
                tags = '$eTags'
                WHERE id = $id";
        
        if ($conn->query($sql)) {
            header("Location: news.php?updated=1");
            exit;
        } else {
            $error = 'Viga uuendamisel: ' . $conn->error;
        }
    } else {
        $error = 'Täida nõutud väljad!';
    }
}

$categories = $conn->query("SELECT * FROM category ORDER BY id ASC");
include 'header.php';
?>

<div style="max-width: 860px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div>
            <h1 style="font-size: 2rem; font-weight: 900; margin-bottom: 4px;">Muuda artiklit #<?= $news['id'] ?></h1>
            <p style="color: var(--text-secondary);">Uuenda artikli sisu, rubriiki või pilti.</p>
        </div>
        <a href="../news.php?id=<?= $news['id'] ?>" target="_blank" class="btn btn-outline-sm">Vaata artiklit ↗</a>
    </div>

    <?php if ($error): ?>
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="news-edit.php?id=<?= $news['id'] ?>" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 30px;">
        
        <div class="form-group">
            <label>Artikli pealkiri *</label>
            <input type="text" name="title" value="<?= htmlspecialchars($news['title']) ?>" required class="form-control">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label>Rubriik *</label>
                <select name="category_id" required class="form-control">
                    <?php if ($categories): ?>
                        <?php while ($c = $categories->fetch_assoc()): ?>
                            <option value="<?= $c['id'] ?>" <?= $news['category_id'] == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Märksõnad (tags)</label>
                <input type="text" name="tags" value="<?= htmlspecialchars($news['tags'] ?? '') ?>" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label>Pildi URL</label>
            <input type="url" name="image_url" value="<?= htmlspecialchars($news['image_url'] ?? '') ?>" class="form-control">
        </div>

        <div class="form-group">
            <label>Artikli sisu *</label>
            <textarea name="text" rows="14" required class="comment-textarea"><?= htmlspecialchars($news['text']) ?></textarea>
        </div>

        <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" name="is_featured" id="featCheck" <?= !empty($news['is_featured']) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--primary);">
            <label for="featCheck" style="margin-bottom: 0; cursor: pointer;">Määra esilooks (Hero juhtartikliks avalehel)</label>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 25px;">
            <button type="submit" class="btn btn-primary">Salvesta muudatused</button>
            <a href="news.php" class="btn btn-secondary">Tühista</a>
        </div>

    </form>
</div>

<?php include 'footer.php'; ?>
