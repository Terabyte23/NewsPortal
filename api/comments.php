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
        $userId = 'NULL';
    }

    if ($newsId <= 0 || empty($text)) {
        echo json_encode(['success' => false, 'error' => 'Vigased andmed või tühi kommentaar']);
        exit;
    }

    $textSafe = $conn->real_escape_string($text);
    $authorSafe = $conn->real_escape_string($authorName);
    $now = date('Y-m-d H:i:s');

    $sql = "INSERT INTO comments (news_id, text, date, user_id, author_name) 
            VALUES ($newsId, '$textSafe', '$now', $userId, '$authorSafe')";
    
    if ($conn->query($sql)) {
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
        echo json_encode(['success' => false, 'error' => 'Andmebaasi viga: ' . $conn->error]);
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

    $conn->query("DELETE FROM comments WHERE id = $id");
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Toetamata päring']);
