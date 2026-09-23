<?php
require_once 'db.php';

$currentUser = get_logged_in_user($conn);
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Handle comment deletion by admin in article directly
if (isset($_GET['del_comment'])) {
    $delId = (int)$_GET['del_comment'];
    if ($delId > 0 && $currentUser && (is_admin($currentUser) || is_editor_or_admin($currentUser))) {
        if (verify_csrf_token($_GET['csrf_token'] ?? '')) {
            $stmtDel = $conn->prepare("DELETE FROM comments WHERE id = ? AND news_id = ?");
            if ($stmtDel) {
                $stmtDel->bind_param("ii", $delId, $id);
                $stmtDel->execute();
            }
            header("Location: news.php?id=" . $id . "&comm_deleted=1");
            exit;
        }
    }
}

// 1. Increment view counter
$stmtView = $conn->prepare("UPDATE news SET views = views + 1 WHERE id = ?");
if ($stmtView) {
    $stmtView->bind_param("i", $id);
    $stmtView->execute();
}

// 2. Fetch news with category and author
$stmtNews = $conn->prepare("SELECT n.*, c.name AS category_name, c.id AS cat_id, u.name AS author_name, u.job AS author_job, u.avatar AS author_avatar
        FROM news n
        LEFT JOIN category c ON n.category_id = c.id
        LEFT JOIN users u ON n.user_id = u.id
        WHERE n.id = ?");
$news = null;
if ($stmtNews) {
    $stmtNews->bind_param("i", $id);
    $stmtNews->execute();
    $res = $stmtNews->get_result();
    $news = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
}

if (!$news) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>Uudist ei leitud.</h2><p><a href='index.php'>Tagasi pealehele</a></p></div>");
}

$pageTitle = $news['title'];
$imgUrl = get_article_image($news);
$readTime = calculate_reading_time($news['text']);
$reactions = json_decode($news['reactions'] ?? '{}', true) ?: [];

// 3. Fetch Comments
$stmtComm = $conn->prepare("SELECT * FROM comments WHERE news_id = ? ORDER BY id DESC");
$commentsRes = null;
if ($stmtComm) {
    $stmtComm->bind_param("i", $id);
    $stmtComm->execute();
    $commentsRes = $stmtComm->get_result();
}
$commentsCount = $commentsRes ? $commentsRes->num_rows : 0;

// 4. Fetch Related News in same category
$catId = (int)$news['category_id'];
$stmtRel = $conn->prepare("SELECT * FROM news WHERE category_id = ? AND id != ? ORDER BY id DESC LIMIT 3");
$relatedRes = null;
if ($stmtRel) {
    $stmtRel->bind_param("ii", $catId, $id);
    $stmtRel->execute();
    $relatedRes = $stmtRel->get_result();
}

include 'includes/header.php';
?>

<main class="container">
    
    <!-- ARTICLE HEADER -->
    <header class="article-header">
        
        <nav class="breadcrumb">
            <a href="index.php">Avaleht</a>
            <span>/</span>
            <a href="category.php?id=<?= $news['category_id'] ?>"><?= htmlspecialchars($news['category_name'] ?? 'Uudised') ?></a>
            <span>/</span>
            <span style="color: var(--text-primary);"><?= htmlspecialchars(mb_substr($news['title'], 0, 30)) ?>...</span>
        </nav>

        <span class="badge-category" style="margin-bottom: 12px; display: inline-block;">
            <?= htmlspecialchars($news['category_name'] ?? 'UUDIS') ?>
        </span>

        <h1 class="article-title"><?= htmlspecialchars($news['title']) ?></h1>

        <!-- META BAR -->
        <div class="article-meta-bar">
            
            <!-- AUTHOR CARD -->
            <div class="author-info">
                <div class="author-avatar">
                    <?= mb_strtoupper(mb_substr($news['author_name'] ?? 'Toimetus', 0, 1)) ?>
                </div>
                <div>
                    <div class="author-name"><?= htmlspecialchars($news['author_name'] ?? 'NewsPortal Toimetus') ?></div>
                    <div class="article-date-read">
                        <?= format_estonian_date($news['created_at'] ?? null) ?> • <?= $readTime ?> min lugemist • 👁️ <?= number_format($news['views'] ?? 1) ?> vaatamist
                    </div>
                </div>
            </div>

            <!-- ACTION CONTROLS (Font resizer, Bookmark, Share) -->
            <div class="article-action-buttons">
                <button class="icon-btn" id="fontSizeOut" title="Vähenda kirja">A-</button>
                <button class="icon-btn" id="fontSizeIn" title="Suurenda kirja">A+</button>
                <button class="icon-btn btn-bookmark-action" 
                        data-id="<?= $news['id'] ?>"
                        data-title="<?= htmlspecialchars($news['title']) ?>"
                        data-image="<?= htmlspecialchars($imgUrl) ?>"
                        data-category="<?= htmlspecialchars($news['category_name'] ?? 'Uudis') ?>"
                        data-date="<?= format_estonian_date($news['created_at'] ?? null) ?>"
                        title="Salvesta lugemiseks">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
                </button>
                <button class="icon-btn" id="copyLinkBtn" title="Kopeeri link">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                </button>
            </div>

        </div>

        <!-- AUDIO READER (TTS) PLAYER WIDGET -->
        <div class="audio-player-widget" id="audioWidget">
            <button class="tts-play-btn" id="ttsPlayBtn" title="Kuula artiklit">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
            </button>
            <div class="tts-info">
                <div class="tts-title">
                    <span>🎧 Kuula artikli heliesitust</span>
                    <small style="background: var(--primary-light); color: var(--primary); padding: 1px 6px; border-radius: 4px; font-size: 0.6875rem;">AI Voice</small>
                </div>
                <div class="tts-status" id="ttsStatus">Klõpsa nupule, et alustada kuulamist eesti keeles</div>
                <div class="tts-progress-bar">
                    <div class="tts-progress-fill" id="ttsProgressFill"></div>
                </div>
            </div>
        </div>

    </header>

    <!-- FEATURED IMAGE -->
    <div class="article-featured-img">
        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($news['title']) ?>">
    </div>

    <!-- ARTICLE BODY -->
    <div class="article-body-wrapper">
        <?php
        $paragraphs = explode("
", trim($news['text']));
        $isFirst = true;
        foreach ($paragraphs as $p) {
            $p = trim($p);
            if (empty($p)) continue;
            if ($isFirst) {
                echo '<p class="article-lead">' . nl2br(htmlspecialchars($p)) . '</p>';
                $isFirst = false;
            } else {
                echo '<p>' . nl2br(htmlspecialchars($p)) . '</p>';
            }
        }
        ?>
    </div>

    <!-- REACTIONS BAR -->
    <div class="reactions-box">
        <div class="reactions-title">Kuidas see artikkel sulle meeldis? Anna tagasisidet!</div>
        <div class="reactions-list">
            <button class="reaction-btn" data-news-id="<?= $news['id'] ?>" data-type="like">
                <span>👍 Tubli</span>
                <span class="reaction-count"><?= $reactions['like'] ?? 0 ?></span>
            </button>
            <button class="reaction-btn" data-news-id="<?= $news['id'] ?>" data-type="heart">
                <span>❤️ Meeldib</span>
                <span class="reaction-count"><?= $reactions['heart'] ?? 0 ?></span>
            </button>
            <button class="reaction-btn" data-news-id="<?= $news['id'] ?>" data-type="insightful">
                <span>💡 Geniaalne</span>
                <span class="reaction-count"><?= $reactions['insightful'] ?? 0 ?></span>
            </button>
            <button class="reaction-btn" data-news-id="<?= $news['id'] ?>" data-type="fire">
                <span>🔥 Kuum teema</span>
                <span class="reaction-count"><?= $reactions['fire'] ?? 0 ?></span>
            </button>
        </div>
    </div>

    <!-- SHARE BAR -->
    <div class="share-bar">
        <span style="font-weight: 700; font-size: 0.9375rem;">Jaga seda lugu sõpradega:</span>
        <div class="share-links">
            <a href="https://twitter.com/intent/tweet?text=<?= urlencode($news['title']) ?>&url=<?= urlencode('http://localhost/newsportal/news.php?id=' . $news['id']) ?>" target="_blank" class="social-btn" title="Twitter / X"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
            <a href="https://t.me/share/url?url=<?= urlencode('http://localhost/newsportal/news.php?id=' . $news['id']) ?>&text=<?= urlencode($news['title']) ?>" target="_blank" class="social-btn" title="Telegram"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg></a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://localhost/newsportal/news.php?id=' . $news['id']) ?>" target="_blank" class="social-btn" title="Facebook"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
        </div>
    </div>

    <!-- RELATED NEWS -->
    <?php if ($relatedRes && $relatedRes->num_rows > 0): ?>
    <section style="max-width: 780px; margin: 0 auto 50px auto;">
        <h3 style="font-size: 1.375rem; font-weight: 800; margin-bottom: 20px;">Samast rubriigist veel:</h3>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px;">
            <?php while ($rel = $relatedRes->fetch_assoc()): ?>
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden;">
                    <div style="height: 120px; overflow: hidden;">
                        <img src="<?= htmlspecialchars(get_article_image($rel)) ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div style="padding: 12px;">
                        <h4 style="font-size: 0.875rem; font-weight: 700; line-height: 1.3;">
                            <a href="news.php?id=<?= $rel['id'] ?>"><?= htmlspecialchars($rel['title']) ?></a>
                        </h4>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- COMMENTS SECTION -->
    <section class="comments-section">
        
        <div class="comments-header">
            <h3 style="font-size: 1.5rem; font-weight: 900;">Arutelu ja kommentaarid</h3>
            <span class="comments-count-badge" id="commentsCountBadge"><?= $commentsCount ?></span>
        </div>

        <!-- COMMENT FORM -->
        <div class="comment-form-card">
            <form id="commentForm" data-news-id="<?= $news['id'] ?>">
                <?php if (!$currentUser): ?>
                    <div style="margin-bottom: 12px;">
                        <input type="text" name="author_name" placeholder="Sinu nimi (valikuline, vaikimisi: Lugeja)" class="form-control" style="font-size: 0.875rem; padding: 8px 12px;">
                    </div>
                <?php endif; ?>
                
                <textarea name="text" placeholder="Kirjuta oma viisakas arvamus artikli kohta..." required class="comment-textarea"></textarea>
                
                <div class="comment-form-bottom">
                    <small style="color: var(--text-muted);">Kommenteerides nõustud heade tavadega.</small>
                    <button type="submit" class="btn btn-primary-sm">Postita kommentaar</button>
                </div>
            </form>
        </div>

        <?php if (isset($_GET['comm_deleted'])): ?>
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #34d399; padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                <span>✅</span> Kommentaar edukalt kustutatud!
            </div>
        <?php endif; ?>

        <!-- COMMENTS LIST -->
        <div class="comments-list" id="commentsList" data-is-admin="<?= ($currentUser && (is_admin($currentUser) || is_editor_or_admin($currentUser))) ? '1' : '0' ?>">
            <?php if ($commentsRes && $commentsRes->num_rows > 0): ?>
                <?php while ($comm = $commentsRes->fetch_assoc()): 
                    $cAuthor = $comm['author_name'] ?? 'Lugeja';
                ?>
                    <div class="comment-card" data-comment-id="<?= $comm['id'] ?>">
                        <div class="comment-top">
                            <div class="comment-author-badge">
                                <div class="avatar-circle" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                    <?= mb_strtoupper(mb_substr($cAuthor, 0, 1)) ?>
                                </div>
                                <span class="comment-author-name"><?= htmlspecialchars($cAuthor) ?></span>
                                <span class="user-role-badge role-user">Lugeja</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="comment-date"><?= format_time_ago($comm['date']) ?></span>
                                <?php if ($currentUser && (is_admin($currentUser) || is_editor_or_admin($currentUser))): ?>
                                    <a href="news.php?id=<?= $news['id'] ?>&del_comment=<?= $comm['id'] ?>&csrf_token=<?= csrf_token() ?>" 
                                       class="comment-delete-btn" 
                                       data-comment-id="<?= $comm['id'] ?>"
                                       data-confirm-title="Kommentaari kustutamine"
                                       data-confirm-message="Kas soovid selle kommentaari kindlasti kustutada?"
                                       title="Kustuta kommentaar">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        Kustuta
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="comment-text"><?= nl2br(htmlspecialchars($comm['text'])) ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-comments-msg" style="text-align: center; padding: 30px; color: var(--text-muted);">
                    Ole esimene, kes selle uudise kohta arvamust avaldab!
                </div>
            <?php endif; ?>
        </div>

    </section>

</main>

<?php include 'includes/footer.php'; ?>
