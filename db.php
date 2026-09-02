<?php
// Modern NewsPortal Database Connection & Bootstrap
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

$host = "localhost";
$user = "root";
$password = "";
$dbname = "newsportal";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения к базе данных: " . $conn->connect_error);
}

// Set full UTF-8 encoding
$conn->set_charset("utf8mb4");

// Schema self-migration helper: safely add modern columns if they don't exist yet
function migrate_database_if_needed($conn) {
    // 1. Ensure columns in 'news'
    $res = $conn->query("SHOW COLUMNS FROM `news` LIKE 'views'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE `news` ADD COLUMN `views` INT(11) NOT NULL DEFAULT 0");
    }
    
    $res = $conn->query("SHOW COLUMNS FROM `news` LIKE 'likes'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE `news` ADD COLUMN `likes` INT(11) NOT NULL DEFAULT 0");
    }

    $res = $conn->query("SHOW COLUMNS FROM `news` LIKE 'image_url'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE `news` ADD COLUMN `image_url` VARCHAR(500) NULL DEFAULT NULL");
    }

    $res = $conn->query("SHOW COLUMNS FROM `news` LIKE 'created_at'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE `news` ADD COLUMN `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

    $res = $conn->query("SHOW COLUMNS FROM `news` LIKE 'is_featured'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE `news` ADD COLUMN `is_featured` TINYINT(1) NOT NULL DEFAULT 0");
    }

    $res = $conn->query("SHOW COLUMNS FROM `news` LIKE 'reactions'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE `news` ADD COLUMN `reactions` TEXT NULL DEFAULT NULL");
    }

    $res = $conn->query("SHOW COLUMNS FROM `news` LIKE 'tags'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE `news` ADD COLUMN `tags` VARCHAR(255) NULL DEFAULT NULL");
    }

    // 2. Ensure columns in 'comments'
    $res = $conn->query("SHOW COLUMNS FROM `comments` LIKE 'user_id'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE `comments` ADD COLUMN `user_id` INT(11) NULL DEFAULT NULL");
    }

    $res = $conn->query("SHOW COLUMNS FROM `comments` LIKE 'author_name'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE `comments` ADD COLUMN `author_name` VARCHAR(100) NULL DEFAULT 'Lugeja'");
    }

    // 3. Ensure columns in 'users'
    $res = $conn->query("SHOW COLUMNS FROM `users` LIKE 'avatar'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) NULL DEFAULT NULL");
    }

    // 4. Ensure default admin and test user exist
    $checkAdmin = $conn->query("SELECT id FROM users WHERE login = 'admin'");
    if ($checkAdmin && $checkAdmin->num_rows === 0) {
        $conn->query("INSERT INTO users (name, job, email, telefon, login, parol, status, registratsion_date) 
                      VALUES ('Peatoimetaja', 'Peatoimetaja / Admin', 'admin@newsportal.ee', '+3725000001', 'admin', 'admin123', 'admin', CURDATE())");
    }

    $checkUser = $conn->query("SELECT id FROM users WHERE login = 'user'");
    if ($checkUser && $checkUser->num_rows === 0) {
        $conn->query("INSERT INTO users (name, job, email, telefon, login, parol, status, registratsion_date) 
                      VALUES ('Tavaline Lugeja', 'Lugeja', 'user@newsportal.ee', '+3725000002', 'user', 'user123', 'user', CURDATE())");
    }

    // 5. Ensure core categories exist
    $cats = ['Tehnoloogia', 'Haridus', 'Teadus', 'Internet', 'Majandus', 'Kultuur', 'Sport'];
    foreach ($cats as $cat) {
        $escaped = $conn->real_escape_string($cat);
        $checkCat = $conn->query("SELECT id FROM category WHERE name = '$escaped'");
        if ($checkCat && $checkCat->num_rows === 0) {
            $conn->query("INSERT INTO category (name) VALUES ('$escaped')");
        }
    }
}

// Run lightweight check once
migrate_database_if_needed($conn);

// Helper Functions
function get_logged_in_user($conn) {
    if (isset($_SESSION['user_id'])) {
        $id = (int)$_SESSION['user_id'];
        $res = $conn->query("SELECT * FROM users WHERE id = $id");
        if ($res && $res->num_rows > 0) {
            return $res->fetch_assoc();
        }
    }
    return null;
}

function is_admin($user) {
    return $user && isset($user['status']) && ($user['status'] === 'admin' || $user['status'] === 'Administrator');
}

function is_editor_or_admin($user) {
    return $user && isset($user['status']) && in_array($user['status'], ['admin', 'Administrator', 'editor', 'Editor', 'journalist']);
}

function get_article_image($news) {
    if (!empty($news['image_url'])) {
        return $news['image_url'];
    }
    
    $id = (int)$news['id'];
    $localFile1 = "images/picture" . $id . ".jpg";
    $localFile2 = "images/picture.jpg";
    
    if (file_exists(__DIR__ . "/" . $localFile1)) {
        return $localFile1;
    }
    if ($id == 1 && file_exists(__DIR__ . "/" . $localFile2)) {
        return $localFile2;
    }

    $fallbacks = [
        1 => "https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80",
        2 => "https://images.unsplash.com/photo-1507413245164-6160d8298b31?auto=format&fit=crop&w=1200&q=80",
        3 => "https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=80",
        4 => "https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1200&q=80",
        5 => "https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80",
        6 => "https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80",
        7 => "https://images.unsplash.com/photo-1551836022-deb4988cc6c0?auto=format&fit=crop&w=1200&q=80",
    ];
    
    $idx = (($id - 1) % 7) + 1;
    return $fallbacks[$idx] ?? "https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1200&q=80";
}

function calculate_reading_time($text) {
    $wordCount = str_word_count(strip_tags($text));
    $minutes = ceil($wordCount / 180);
    return max(1, $minutes);
}

function format_estonian_date($dateStr) {
    if (!$dateStr) return 'Täna';
    $timestamp = strtotime($dateStr);
    $months = [
        1 => 'jaanuar', 2 => 'veebruar', 3 => 'märts', 4 => 'aprill',
        5 => 'mai', 6 => 'juuni', 7 => 'juuli', 8 => 'august',
        9 => 'september', 10 => 'oktoober', 11 => 'november', 12 => 'detsember'
    ];
    $day = date('j', $timestamp);
    $monthNum = (int)date('n', $timestamp);
    $year = date('Y', $timestamp);
    $time = date('H:i', $timestamp);
    return "$day. " . $months[$monthNum] . " $year, $time";
}

function format_time_ago($dateStr) {
    $timestamp = strtotime($dateStr);
    $diff = time() - $timestamp;
    if ($diff < 60) return 'Just praegu';
    if ($diff < 3600) return floor($diff / 60) . ' min tagasi';
    if ($diff < 86400) return floor($diff / 3600) . ' tundi tagasi';
    if ($diff < 604800) return floor($diff / 86400) . ' päeva tagasi';
    return date('d.m.Y', $timestamp);
}
?>