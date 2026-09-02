<?php
require_once __DIR__ . '/../db.php';

$currentUser = get_logged_in_user($conn);
if (!is_editor_or_admin($currentUser)) {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    // Delete comments first
    $conn->query("DELETE FROM comments WHERE news_id = $id");
    // Delete article
    $conn->query("DELETE FROM news WHERE id = $id");
}

header("Location: news.php?deleted=1");
exit;
