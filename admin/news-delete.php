<?php
require_once __DIR__ . '/auth_check.php';
admin_verify_csrf();

$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($id > 0) {
    // Delete comments first
    $stmt1 = $conn->prepare("DELETE FROM comments WHERE news_id = ?");
    if ($stmt1) {
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
    }
    
    // Delete article
    $stmt2 = $conn->prepare("DELETE FROM news WHERE id = ?");
    if ($stmt2) {
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
    }
}

header("Location: news.php?deleted=1");
exit;
