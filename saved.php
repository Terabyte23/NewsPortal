<?php
require_once 'db.php';
$pageTitle = 'Minu järjehoidjad';
include 'includes/header.php';
?>

<main class="container" style="padding: 40px 0 60px 0;">
    <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 35px; margin-bottom: 35px;">
        <span class="badge-category" style="margin-bottom: 10px; display: inline-block;">LUGEMISNIMEKIRI</span>
        <h1 style="font-size: 2.25rem; font-weight: 900; margin-bottom: 8px;">Salvestatud artiklid</h1>
        <p style="color: var(--text-secondary);">Siin on artiklid, mille oled salvestanud hilisemaks lugemiseks.</p>
    </div>

    <div id="savedContainer">
        <div id="savedList" class="news-grid"></div>
        <div id="noSaved" style="text-align: center; padding: 60px 20px; display: none;">
            <div style="font-size: 3rem; margin-bottom: 15px;">📑</div>
            <h3 style="margin-bottom: 10px;">Sul pole veel salvestatud artikleid</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px;">Klõpsa uudise kaardil järjehoidja ikoonile, et lisada lugu siia nimekirja.</p>
            <a href="index.php" class="btn btn-primary-sm">Sirvi uudiseid</a>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const list = getBookmarks();
    const listEl = document.getElementById('savedList');
    const noSavedEl = document.getElementById('noSaved');

    if (!list.length) {
        noSavedEl.style.display = 'block';
        return;
    }

    let html = '';
    list.forEach(item => {
        const safeId = parseInt(item.id, 10) || 0;
        const safeImg = escapeHtml(item.image || 'images/picture.jpg');
        const safeCategory = escapeHtml(item.category || 'Uudis');
        const safeTitle = escapeHtml(item.title || '');
        const safeDate = escapeHtml(item.date || 'Täna');

        html += `
            <article class="news-card">
                <div class="card-image-box">
                    <img src="${safeImg}" alt="${safeTitle}">
                    <span class="card-category-badge">${safeCategory}</span>
                    <button class="card-bookmark-btn saved" data-id="${safeId}" title="Eemalda">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
                    </button>
                </div>
                <div class="card-body">
                    <h3 class="card-title"><a href="news.php?id=${safeId}">${safeTitle}</a></h3>
                    <div class="card-bottom-bar" style="margin-top: auto;">
                        <span>${safeDate}</span>
                        <a href="news.php?id=${safeId}" class="card-read-more">Loe edasi →</a>
                    </div>
                </div>
            </article>
        `;
    });
    listEl.innerHTML = html;
    initBookmarks();
});
</script>

<?php include 'includes/footer.php'; ?>
