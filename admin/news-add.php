<?php
require_once __DIR__ . '/../db.php';
$adminPage = 'news-add';
$pageTitle = 'Lisa uus artikkel';

$error = '';
$success = '';

$categories = $conn->query("SELECT * FROM category ORDER BY id ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $text = trim($_POST['text'] ?? '');
    $catId = (int)($_POST['category_id'] ?? 1);
    $imageUrl = trim($_POST['image_url'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $userId = (int)$currentUser['id'];

    if (!empty($title) && !empty($text)) {
        $eTitle = $conn->real_escape_string($title);
        $eText = $conn->real_escape_string($text);
        $eImg = $conn->real_escape_string($imageUrl);
        $eTags = $conn->real_escape_string($tags);

        $sql = "INSERT INTO news (title, text, picture, category_id, user_id, image_url, is_featured, tags, created_at)
                VALUES ('$eTitle', '$eText', '', $catId, $userId, '$eImg', $isFeatured, '$eTags', NOW())";
        
        if ($conn->query($sql)) {
            $newId = $conn->insert_id;
            header("Location: news.php?success=1");
            exit;
        } else {
            $error = 'Andmebaasi viga: ' . $conn->error;
        }
    } else {
        $error = 'Pealkiri ja artikli sisu on kohustuslikud!';
    }
}

include 'header.php';
?>

<div style="max-width: 860px; margin: 0 auto;">
    <div style="margin-bottom: 25px;">
        <h1 style="font-size: 2rem; font-weight: 900; margin-bottom: 4px;">Uue artikli loomine</h1>
        <p style="color: var(--text-secondary);">Täida vajalikud väljad ja avalda uudis.</p>
    </div>

    <?php if ($error): ?>
        <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #f87171; padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="news-add.php" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 30px;">
        
        <div class="form-group">
            <label>Artikli pealkiri *</label>
            <input type="text" name="title" required class="form-control" placeholder="Sisesta kõlav ja informatiivne pealkiri...">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label>Rubriik / Kategooria *</label>
                <select name="category_id" required class="form-control">
                    <?php if ($categories): ?>
                        <?php while ($c = $categories->fetch_assoc()): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Märksõnad / Tagid (eralda komaga)</label>
                <input type="text" name="tags" placeholder="nt. Tehisintellekt, Robotid, Eesti" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label>Pildi URL (Unsplash või veebilink)</label>
            <input type="url" name="image_url" id="imageUrlInput" placeholder="https://images.unsplash.com/..." class="form-control">
            <small style="color: var(--text-muted); display: block; margin-top: 6px;">Või vali kiire pilt allolevast galeriist:</small>
            
            <div style="display: flex; gap: 8px; margin-top: 8px; overflow-x: auto; padding-bottom: 6px;">
                <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?w=200" style="width: 70px; height: 45px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px solid var(--border-color);" onclick="document.getElementById('imageUrlInput').value = this.src.replace('w=200', 'w=1200')">
                <img src="https://images.unsplash.com/photo-1507413245164-6160d8298b31?w=200" style="width: 70px; height: 45px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px solid var(--border-color);" onclick="document.getElementById('imageUrlInput').value = this.src.replace('w=200', 'w=1200')">
                <img src="https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=200" style="width: 70px; height: 45px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px solid var(--border-color);" onclick="document.getElementById('imageUrlInput').value = this.src.replace('w=200', 'w=1200')">
                <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=200" style="width: 70px; height: 45px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px solid var(--border-color);" onclick="document.getElementById('imageUrlInput').value = this.src.replace('w=200', 'w=1200')">
                <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=200" style="width: 70px; height: 45px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px solid var(--border-color);" onclick="document.getElementById('imageUrlInput').value = this.src.replace('w=200', 'w=1200')">
            </div>
        </div>

        <div class="form-group">
            <label>Artikli täistekst *</label>
            <textarea name="text" rows="12" required class="comment-textarea" placeholder="Kirjuta artikli tekst siia..."></textarea>
        </div>

        <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" name="is_featured" id="featCheck" style="width: 18px; height: 18px; accent-color: var(--primary);">
            <label for="featCheck" style="margin-bottom: 0; cursor: pointer;">Määra esilooks (Hero juhtartikliks avalehel)</label>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 25px;">
            <button type="submit" class="btn btn-primary">Avalda uudis kohe</button>
            <a href="news.php" class="btn btn-secondary">Tühista</a>
        </div>

    </form>
</div>

<?php include 'footer.php'; ?>
