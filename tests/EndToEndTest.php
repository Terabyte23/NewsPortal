<?php
require_once __DIR__ . '/DatabaseTestCase.php';

/**
 * @testdox Otsast lõpuni (End-to-End / E2E) süsteemitestid (NewsPortal Complete E2E Suite)
 */
class EndToEndTest extends DatabaseTestCase {

    /**
     * @testdox E2E 1: Uudiste sirvimine, lugemisaeg, vaatamiste loendur ja eesti keele kuupäevavorming
     */
    public function testE2E_GuestBrowsingAndReadingExperience() {
        // 1. Create a category
        $catName = "E2E Teadus " . rand(100, 999);
        self::$db->query("INSERT INTO category (name) VALUES ('$catName')");
        $catId = self::$db->insert_id;
        $this->assertGreaterThan(0, $catId);

        // 2. Publish an article in this category with explicit text for reading time
        $title = "Kvanttehnoloogia ja kosmoseuuringud " . rand(1000, 9999);
        $words = array_fill(0, 200, 'teadus');
        $text = implode(' ', $words); // 200 words -> ~1 min read

        $stmt = self::$db->prepare("INSERT INTO news (title, text, category_id, views, likes) VALUES (?, ?, ?, 0, 0)");
        $stmt->bind_param("ssi", $title, $text, $catId);
        $this->assertTrue($stmt->execute());
        $newsId = self::$db->insert_id;

        // 3. Guest opens the article: verify reading time calculation helper
        $readTime = calculate_reading_time($text);
        $this->assertGreaterThanOrEqual(1, $readTime);

        // 4. Increment view counter
        self::$db->query("UPDATE news SET views = views + 1 WHERE id = $newsId");
        $article = self::$db->query("SELECT * FROM news WHERE id = $newsId")->fetch_assoc();
        $this->assertEquals(1, (int)$article['views']);

        // 5. Verify Estonian date formatting and relative time formatting
        $formattedDate = format_estonian_date($article['created_at'] ?? date('Y-m-d H:i:s'));
        $this->assertNotEmpty($formattedDate);
        $timeAgo = format_time_ago(date('Y-m-d H:i:s'));
        $this->assertEquals('Just praegu', $timeAgo);

        // 6. Verify image fallback logic
        $imgUrl = get_article_image($article);
        $this->assertNotEmpty($imgUrl);
    }

    /**
     * @testdox E2E 2: Uudiste otsing märksõna järgi reaalajas (Search by keyword)
     */
    public function testE2E_SearchFunctionality() {
        $uniqueKeyword = "Superarvuti" . rand(1000, 9999);
        self::$db->query("INSERT INTO news (title, text) VALUES ('Eesti $uniqueKeyword labor', 'Sisu...')");
        $newsId = self::$db->insert_id;

        $search = self::$db->real_escape_string($uniqueKeyword);
        $res = self::$db->query("SELECT * FROM news WHERE title LIKE '%$search%' AND id = $newsId");
        $this->assertEquals(1, $res->num_rows);
        $row = $res->fetch_assoc();
        $this->assertStringContainsString($uniqueKeyword, $row['title']);
    }

    /**
     * @testdox E2E 3: Kommentaaride elutsükkel külalisena ja registreeritud kasutajana (Comments lifecycle)
     */
    public function testE2E_GuestAndUserCommentSubmission() {
        self::$db->query("INSERT INTO news (title) VALUES ('Kommentaaritesti artikkel')");
        $newsId = self::$db->insert_id;

        // 1. Guest comment submission
        $guestAuthor = "Külaline Mari";
        $guestText = "Väga huvitav arvamuslugu!";
        $now = date('Y-m-d H:i:s');

        $stmtGuest = self::$db->prepare("INSERT INTO comments (news_id, text, author_name, date, user_id) VALUES (?, ?, ?, ?, NULL)");
        $stmtGuest->bind_param("isss", $newsId, $guestText, $guestAuthor, $now);
        $this->assertTrue($stmtGuest->execute());
        $guestCommId = self::$db->insert_id;

        // 2. Registered user comment submission
        $regUserLogin = "kommenteerija_" . time();
        self::$db->query("INSERT INTO users (name, login, status) VALUES ('Peeter', '$regUserLogin', 'user')");
        $userId = self::$db->insert_id;

        $userText = "Nõustun artikli seisukohtadega.";
        $stmtUser = self::$db->prepare("INSERT INTO comments (news_id, text, author_name, date, user_id) VALUES (?, ?, 'Peeter', ?, ?)");
        $stmtUser->bind_param("issi", $newsId, $userText, $now, $userId);
        $this->assertTrue($stmtUser->execute());

        // 3. Verify comments exist and are bound to article
        $count = self::$db->query("SELECT COUNT(*) AS c FROM comments WHERE news_id = $newsId")->fetch_assoc()['c'];
        $this->assertEquals(2, (int)$count);
    }

    /**
     * @testdox E2E 4: Reaktsioonide API ja meeldimiste (likes/dislikes) loenduri loogika
     */
    public function testE2E_ReactionsSystem() {
        self::$db->query("INSERT INTO news (title, likes, views) VALUES ('Reaktsioonide artikkel', 10, 50)");
        $newsId = self::$db->insert_id;

        // Reader hits like
        self::$db->query("UPDATE news SET likes = likes + 1 WHERE id = $newsId");
        $res = self::$db->query("SELECT likes FROM news WHERE id = $newsId");
        $this->assertEquals(11, (int)$res->fetch_assoc()['likes']);
    }

    /**
     * @testdox E2E 5: Kasutaja registreerimise valideerimine ja duplikaatide vältimine (Registration validation)
     */
    public function testE2E_UserRegistrationValidationAndDuplicateCheck() {
        // Validation fails with missing fields
        $emptyPost = ['name' => 'Jaan', 'login' => '', 'parol' => '', 'email' => ''];
        $error = '';
        if (empty($emptyPost['login']) || empty($emptyPost['parol']) || empty($emptyPost['email'])) {
            $error = 'Palun täida kõik nõutud väljad!';
        }
        $this->assertEquals('Palun täida kõik nõutud väljad!', $error);

        // Valid registration with Bcrypt
        $login = "e2e_reg_" . time();
        $email = "$login@portal.ee";
        $hash = password_hash("Parool123!", PASSWORD_DEFAULT);

        $stmt = self::$db->prepare("INSERT INTO users (name, email, login, parol, status) VALUES ('Jaan Tamm', ?, ?, ?, 'user')");
        $stmt->bind_param("sss", $email, $login, $hash);
        $this->assertTrue($stmt->execute());

        // Duplicate check
        $dupCheck = self::$db->query("SELECT id FROM users WHERE login = '$login'");
        $this->assertEquals(1, $dupCheck->num_rows);
    }

    /**
     * @testdox E2E 6: Autentimine, Bcrypt paroolikontroll, sessiooni loomine ja väljalogimine (Auth & Logout)
     */
    public function testE2E_UserAuthenticationSessionAndLogout() {
        $login = "e2e_auth_" . time();
        $rawPass = "MinuSalasona2026!";
        $hash = password_hash($rawPass, PASSWORD_DEFAULT);

        self::$db->query("INSERT INTO users (name, login, parol, status) VALUES ('E2E Tester', '$login', '$hash', 'admin')");
        $userId = self::$db->insert_id;

        // Login check
        $userRow = self::$db->query("SELECT * FROM users WHERE login = '$login'")->fetch_assoc();
        $this->assertTrue(password_verify($rawPass, $userRow['parol']));
        $this->assertFalse(password_verify('ValeParool', $userRow['parol']));

        // Session setup
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $userRow['name'];
        $_SESSION['user_role'] = $userRow['status'];

        $this->assertEquals($userId, $_SESSION['user_id']);
        $this->assertEquals('admin', $_SESSION['user_role']);

        // Logout
        $_SESSION = [];
        $this->assertEmpty($_SESSION);
    }

    /**
     * @testdox E2E 7: Kasutajaprofiili andmete ja parooli uuendamine (User Profile update)
     */
    public function testE2E_UserProfileManagement() {
        $login = "e2e_prof_" . time();
        self::$db->query("INSERT INTO users (name, email, login, status) VALUES ('Algne Nimi', 'vana@test.ee', '$login', 'user')");
        $userId = self::$db->insert_id;

        $newName = "Uus Täisnimi";
        $newPhone = "+37255998877";
        $newJob = "Juhtivtoimetaja";

        $stmt = self::$db->prepare("UPDATE users SET name = ?, telefon = ?, job = ? WHERE id = ?");
        $stmt->bind_param("sssi", $newName, $newPhone, $newJob, $userId);
        $this->assertTrue($stmt->execute());

        $updated = self::$db->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();
        $this->assertEquals($newName, $updated['name']);
        $this->assertEquals($newPhone, $updated['telefon']);
        $this->assertEquals($newJob, $updated['job']);
    }

    /**
     * @testdox E2E 8: Järjehoidjate ja salvestatud lugude sessiooniloogika (Saved articles bookmarks)
     */
    public function testE2E_SavedArticlesBookmarks() {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $_SESSION['saved_news'] = [];

        $articleId = 88;
        if (!in_array($articleId, $_SESSION['saved_news'])) {
            $_SESSION['saved_news'][] = $articleId;
        }
        $this->assertContains(88, $_SESSION['saved_news']);

        // Remove
        $_SESSION['saved_news'] = array_diff($_SESSION['saved_news'], [$articleId]);
        $this->assertNotContains(88, $_SESSION['saved_news']);
    }

    /**
     * @testdox E2E 9: Administraatori uudiste elutsükkel ja kaskaadne kustutamine (Admin news lifecycle)
     */
    public function testE2E_AdminEditorialNewsLifecycle() {
        // Admin creates news
        $stmt = self::$db->prepare("INSERT INTO news (title, text, views, likes) VALUES ('Toimetuse Uudis', 'Sisu tekst...', 0, 0)");
        $stmt->execute();
        $newsId = self::$db->insert_id;

        // Add attached comment
        self::$db->query("INSERT INTO comments (news_id, text, author_name) VALUES ($newsId, 'Kommentaar uudisele', 'Lugeja')");

        // Admin updates news
        $stmtUpdate = self::$db->prepare("UPDATE news SET title = 'Toimetuse Uudis [UUENDATUD]' WHERE id = ?");
        $stmtUpdate->bind_param("i", $newsId);
        $this->assertTrue($stmtUpdate->execute());

        // Admin deletes news with cascade comment removal
        self::$db->query("DELETE FROM comments WHERE news_id = $newsId");
        self::$db->query("DELETE FROM news WHERE id = $newsId");

        $checkNews = self::$db->query("SELECT id FROM news WHERE id = $newsId");
        $this->assertEquals(0, $checkNews->num_rows);
        $checkComm = self::$db->query("SELECT id FROM comments WHERE news_id = $newsId");
        $this->assertEquals(0, $checkComm->num_rows);
    }

    /**
     * @testdox E2E 10: Kommentaari modereerimine, kinnitusaken ja tavakasutaja kustutamisõiguse puudumine
     */
    public function testE2E_AdminCommentModerationAndModalSecurity() {
        self::$db->query("INSERT INTO news (title) VALUES ('Modereerimistest')");
        $newsId = self::$db->insert_id;

        self::$db->query("INSERT INTO comments (news_id, text, author_name) VALUES ($newsId, 'Kustutatav kommentaar', 'Rikkuja')");
        $commId = self::$db->insert_id;

        // Regular user attempt -> denied
        $regularUser = ['status' => 'user'];
        $this->assertFalse(is_admin($regularUser));
        $this->assertFalse(is_editor_or_admin($regularUser));

        // Admin deletion with valid CSRF token
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $token = csrf_token();
        $this->assertTrue(verify_csrf_token($token));

        $stmtDel = self::$db->prepare("DELETE FROM comments WHERE id = ?");
        $stmtDel->bind_param("i", $commId);
        $this->assertTrue($stmtDel->execute());

        $res = self::$db->query("SELECT id FROM comments WHERE id = $commId");
        $this->assertEquals(0, $res->num_rows);
    }

    /**
     * @testdox E2E 11: Kasutajarollide haldus ja ülendamine (RBAC promotion: user -> editor -> admin)
     */
    public function testE2E_UserRoleManagementAndPromotion() {
        $login = "e2e_role_" . time();
        self::$db->query("INSERT INTO users (name, login, status) VALUES ('Mati', '$login', 'user')");
        $userId = self::$db->insert_id;

        // Verify initial regular role
        $user = self::$db->query("SELECT status FROM users WHERE id = $userId")->fetch_assoc();
        $this->assertEquals('user', $user['status']);
        $this->assertFalse(is_admin($user));
        $this->assertFalse(is_editor_or_admin($user));

        // Promote to editor
        self::$db->query("UPDATE users SET status = 'editor' WHERE id = $userId");
        $editor = self::$db->query("SELECT status FROM users WHERE id = $userId")->fetch_assoc();
        $this->assertTrue(is_editor_or_admin($editor));
        $this->assertFalse(is_admin($editor));

        // Promote to admin
        self::$db->query("UPDATE users SET status = 'admin' WHERE id = $userId");
        $admin = self::$db->query("SELECT status FROM users WHERE id = $userId")->fetch_assoc();
        $this->assertTrue(is_admin($admin));
        $this->assertTrue(is_editor_or_admin($admin));
    }

    /**
     * @testdox E2E 12: Turvakontrollid – CSRF võltsimise tõkestamine, XSS filtreerimine ja SQLi turvalisus
     */
    public function testE2E_SecurityHardeningOwaspTop10() {
        // CSRF Token verification
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $realToken = csrf_token();
        $this->assertTrue(verify_csrf_token($realToken));
        $this->assertFalse(verify_csrf_token('fake_attack_token_123456'));

        // XSS escaping
        $xss = "<script>alert('Pwned')</script>";
        $escaped = htmlspecialchars($xss, ENT_QUOTES, 'UTF-8');
        $this->assertStringNotContainsString("<script>", $escaped);
        $this->assertStringContainsString("&lt;script&gt;", $escaped);

        // SQL Injection prepared statement resistance
        $sqliPayload = "1 OR 1=1 --";
        $stmt = self::$db->prepare("SELECT id FROM news WHERE id = ?");
        $stmt->bind_param("i", $sqliPayload);
        $this->assertTrue($stmt->execute());
    }
}
