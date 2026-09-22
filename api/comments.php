<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

$method = $_SERVER['REQUEST_METHOD'];
$currentUser = get_logged_in_user($conn);

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $newsId = isset($input['news_id']) ? (int)$input['news_id'] : 0;
    $text = isset($input['text']) ? trim($input['text']) : '';
    $authorName = isset($input['author_name']) ? trim($input['author_name']) : 'Lugeja';

    if ($currentUser) {
        $authorName = $currentUser['name'] ?? $currentUser['login'];
        $userId = (int)$currentUser['id'];
    } else {
        $userId = null;
    }

    if ($newsId <= 0 || empty($text)) {
        echo json_encode(['success' => false, 'error' => 'Vigased andmed või tühi kommentaar']);
        exit;
    }

    // Sanitize author name length and text length
    $authorName = mb_substr($authorName, 0, 100);
    $text = mb_substr($text, 0, 2000);
    $now = date('Y-m-d H:i:s');

    if ($userId === null) {
        $stmt = $conn->prepare("INSERT INTO comments (news_id, text, date, user_id, author_name) VALUES (?, ?, ?, NULL, ?)");
        if ($stmt) {
            $stmt->bind_param("isss", $newsId, $text, $now, $authorName);
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO comments (news_id, text, date, user_id, author_name) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("issis", $newsId, $text, $now, $userId, $authorName);
        }
    }

    if (isset($stmt) && $stmt->execute()) {
        $newId = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'comment' => [
                'id' => $newId,
                'news_id' => $newsId,
                'text' => $text,
                'date' => $now,
                'author' => $authorName
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Andmebaasi viga: ' . ($stmt ? $stmt->error : $conn->error)]);
    }
    exit;
}

if ($method === 'DELETE' || (isset($_GET['action']) && $_GET['action'] === 'delete')) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Vigane ID']);
        exit;
    }

    if (!is_admin($currentUser)) {
        echo json_encode(['success' => false, 'error' => 'Puuduvad õigused']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Toetamata päring']);
