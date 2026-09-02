<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Ainult POST päringud']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$newsId = isset($input['news_id']) ? (int)$input['news_id'] : 0;
$type = isset($input['type']) ? trim($input['type']) : 'like';

$allowedTypes = ['like', 'heart', 'fire', 'insightful'];
if (!in_array($type, $allowedTypes) || $newsId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Vigane tüüp või uudise ID']);
    exit;
}

$res = $conn->query("SELECT reactions, likes FROM news WHERE id = $newsId");
if (!$res || $res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Uudist ei leitud']);
    exit;
}

$row = $res->fetch_assoc();
$reactions = json_decode($row['reactions'] ?? '{}', true) ?: [];

if (!isset($reactions[$type])) {
    $reactions[$type] = 0;
}
$reactions[$type]++;

$newLikes = (int)$row['likes'] + 1;
$reactionsJson = $conn->real_escape_string(json_encode($reactions));

$conn->query("UPDATE news SET reactions = '$reactionsJson', likes = $newLikes WHERE id = $newsId");

echo json_encode([
    'success' => true,
    'type' => $type,
    'new_count' => $reactions[$type],
    'total_likes' => $newLikes
]);
