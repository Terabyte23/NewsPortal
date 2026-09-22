<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (mb_strlen($q) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$likeParam = '%' . $q . '%';

$stmt = $conn->prepare("SELECT n.id, n.title, n.image_url, n.created_at, c.name AS category_name
        FROM news n
        LEFT JOIN category c ON n.category_id = c.id
        WHERE n.title LIKE ? 
           OR n.text LIKE ?
           OR n.tags LIKE ?
        ORDER BY n.id DESC
        LIMIT 6");

$results = [];

if ($stmt) {
    $stmt->bind_param("sss", $likeParam, $likeParam, $likeParam);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $results[] = [
                'id' => (int)$row['id'],
                'title' => $row['title'],
                'category' => $row['category_name'] ?? 'Uudis',
                'image' => get_article_image($row),
                'date' => format_estonian_date($row['created_at'] ?? null)
            ];
        }
    }
}

echo json_encode(['results' => $results]);
