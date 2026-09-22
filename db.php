<?php
// Modern NewsPortal Database Connection & Security Bootstrap

// 1. Secure Session Initialization
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    $currentParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $currentParams['lifetime'],
        'path' => $currentParams['path'],
        'domain' => $currentParams['domain'],
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    @session_start();
}

$host = "localhost";
$user = "root";
$password = "";
$dbname = "newsportal";

// 2. Resilient Database Connection with Auto-Create capability
$conn = @new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Andmebaasiga ühenduse loomine ebaõnnestus: " . htmlspecialchars($conn->connect_error));
}

// Ensure database exists
$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

if (!$conn->select_db($dbname)) {
    die("Andmebaasi '$dbname' valimine ebaõnnestus: " . htmlspecialchars($conn->error));
}

// Set full UTF-8 encoding
$conn->set_charset("utf8mb4");

// 3. Complete Schema Self-Migration & Zero-Config Auto-Install
function migrate_database_if_needed($conn) {
    // 3.1 Category table
    $conn->query("CREATE TABLE IF NOT EXISTS `category` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 3.2 Users table
    $conn->query("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(150) NOT NULL,
        `job` VARCHAR(100) NULL DEFAULT 'Lugeja',
        `email` VARCHAR(150) NOT NULL,
        `telefon` VARCHAR(50) NULL DEFAULT NULL,
        `login` VARCHAR(100) NOT NULL UNIQUE,
        `parol` VARCHAR(255) NOT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'user',
        `registratsion_date` DATE NOT NULL,
        `avatar` VARCHAR(255) NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `idx_login` (`login`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 3.3 News table
    $conn->query("CREATE TABLE IF NOT EXISTS `news` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `title` VARCHAR(255) NOT NULL,
        `text` MEDIUMTEXT NULL,
        `picture` VARCHAR(255) NULL DEFAULT '',
        `category_id` INT(11) NULL DEFAULT 1,
        `user_id` INT(11) NULL DEFAULT 1,
        `views` INT(11) NOT NULL DEFAULT 0,
        `likes` INT(11) NOT NULL DEFAULT 0,
        `image_url` VARCHAR(500) NULL DEFAULT NULL,
        `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
        `reactions` TEXT NULL DEFAULT NULL,
        `tags` VARCHAR(255) NULL DEFAULT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_category` (`category_id`),
        KEY `idx_featured` (`is_featured`),
        KEY `idx_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 3.4 Comments table
    $conn->query("CREATE TABLE IF NOT EXISTS `comments` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `news_id` INT(11) NOT NULL,
        `text` TEXT NOT NULL,
        `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_id` INT(11) NULL DEFAULT NULL,
        `author_name` VARCHAR(100) NULL DEFAULT 'Lugeja',
        PRIMARY KEY (`id`),
        KEY `idx_news_id` (`news_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 3.5 Ensure columns in 'news' if table pre-existed from an older schema
    $columnsToCheck = [
        'views' => "INT(11) NOT NULL DEFAULT 0",
        'likes' => "INT(11) NOT NULL DEFAULT 0",
        'image_url' => "VARCHAR(500) NULL DEFAULT NULL",
        'created_at' => "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP",
        'is_featured' => "TINYINT(1) NOT NULL DEFAULT 0",
        'reactions' => "TEXT NULL DEFAULT NULL",
        'tags' => "VARCHAR(255) NULL DEFAULT NULL"
    ];

    foreach ($columnsToCheck as $col => $definition) {
        $res = $conn->query("SHOW COLUMNS FROM `news` LIKE '$col'");
        if ($res && $res->num_rows === 0) {
            $conn->query("ALTER TABLE `news` ADD COLUMN `$col` $definition");
        }
    }

    // 3.6 Ensure columns in 'comments'
    $res = $conn->query("SHOW COLUMNS FROM `comments` LIKE 'user_id'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE `comments` ADD COLUMN `user_id` INT(11) NULL DEFAULT NULL");
    }
    $res = $conn->query("SHOW COLUMNS FROM `comments` LIKE 'author_name'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE `comments` ADD COLUMN `author_name` VARCHAR(100) NULL DEFAULT 'Lugeja'");
    }

    // 3.7 Ensure columns in 'users'
    $res = $conn->query("SHOW COLUMNS FROM `users` LIKE 'avatar'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) NULL DEFAULT NULL");
    }

    // 3.8 Ensure default admin exists with secure bcrypt hash
    $checkAdmin = $conn->query("SELECT id, parol FROM users WHERE login = 'admin'");
    if ($checkAdmin && $checkAdmin->num_rows === 0) {
        $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, job, email, telefon, login, parol, status, registratsion_date) 
                                VALUES ('Peatoimetaja', 'Peatoimetaja / Admin', 'admin@newsportal.ee', '+3725000001', 'admin', ?, 'admin', CURDATE())");
        if ($stmt) {
            $stmt->bind_param("s", $adminHash);
            $stmt->execute();
        }
    }

    // 3.9 Ensure test user exists with secure bcrypt hash
    $checkUser = $conn->query("SELECT id, parol FROM users WHERE login = 'user'");
    if ($checkUser && $checkUser->num_rows === 0) {
        $userHash = password_hash('user123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, job, email, telefon, login, parol, status, registratsion_date) 
                                VALUES ('Tavaline Lugeja', 'Lugeja', 'user@newsportal.ee', '+3725000002', 'user', ?, 'user', CURDATE())");
        if ($stmt) {
            $stmt->bind_param("s", $userHash);
            $stmt->execute();
        }
    }

    // 3.10 Ensure core categories exist
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

// 4. CSRF Protection Helpers
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

// 5. User & Role Helpers (Prepared Statements)
function get_logged_in_user($conn) {
    if (isset($_SESSION['user_id'])) {
        $id = (int)$_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                return $res->fetch_assoc();
            }
        }
    }
    return null;
}

function is_admin($user) {
    return $user && isset($user['status']) && in_array(strtolower($user['status']), ['admin', 'administrator']);
}

function is_editor_or_admin($user) {
    return $user && isset($user['status']) && in_array(strtolower($user['status']), ['admin', 'administrator', 'editor', 'journalist']);
}

function get_article_image($news) {
    if (!empty($news['image_url'])) {
        return $news['image_url'];
    }
    
    $id = (int)($news['id'] ?? 1);
    $localFile1 = "images/picture" . $id . ".jpg";
    $localFile2 = "images/picture.jpg";
    
    if (file_exists(__DIR__ . "/" . $localFile1)) {
        return $localFile1;
    }
    if ($id == 1 && file_exists(__DIR__ . "/" . $localFile2)) {
        return $localFile2;
    }

    $fallbacks = [
        1 => "https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80",
        2 => "https://images.unsplash.com/photo-1592478411213-6153e4ebc07d?auto=format&fit=crop&w=1200&q=80",
        3 => "https://images.unsplash.com/photo-1614728894747-a83421e2b9c9?auto=format&fit=crop&w=1200&q=80",
        4 => "https://images.unsplash.com/photo-1563986768609-322da13575f3?auto=format&fit=crop&w=1200&q=80",
        5 => "https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80",
        6 => "https://images.unsplash.com/photo-1508739773434-c26b3d09e071?auto=format&fit=crop&w=1200&q=80",
        7 => "https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=1200&q=80",
    ];
    
    $idx = (($id - 1) % 7) + 1;
    return $fallbacks[$idx] ?? "https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80";
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
    if (!$dateStr) return 'Just praegu';
    $timestamp = strtotime($dateStr);
    $diff = time() - $timestamp;
    if ($diff < 60) return 'Just praegu';
    if ($diff < 3600) return floor($diff / 60) . ' min tagasi';
    if ($diff < 86400) return floor($diff / 3600) . ' tundi tagasi';
    if ($diff < 604800) return floor($diff / 86400) . ' päeva tagasi';
    return date('d.m.Y', $timestamp);
}
?>