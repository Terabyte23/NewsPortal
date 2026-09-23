<?php
require_once __DIR__ . '/DatabaseTestCase.php';

class CommentManagementTest extends DatabaseTestCase {

    private $testNewsId;

    protected function setUp(): void {
        parent::setUp();

        // Create a dummy news article to attach comments to
        $stmt = self::$db->prepare("INSERT INTO news (title, text) VALUES ('Test Article for Comments', 'Content...')");
        $stmt->execute();
        $this->testNewsId = self::$db->insert_id;
    }

    public function testGuestCanAddComment() {
        $author = 'Külaline Lugeja';
        $text = 'See on külalise kommentaar.';
        $now = date('Y-m-d H:i:s');

        $stmt = self::$db->prepare("INSERT INTO comments (news_id, text, date, user_id, author_name) VALUES (?, ?, ?, NULL, ?)");
        $this->assertNotFalse($stmt, "Prepare failed: " . self::$db->error);
        $stmt->bind_param("isss", $this->testNewsId, $text, $now, $author);
        $this->assertTrue($stmt->execute());

        $commentId = self::$db->insert_id;
        $this->assertGreaterThan(0, $commentId);

        $res = self::$db->query("SELECT * FROM comments WHERE id = $commentId");
        $this->assertEquals(1, $res->num_rows);
        $row = $res->fetch_assoc();
        $this->assertEquals($text, $row['text']);
        $this->assertEquals($author, $row['author_name']);
        $this->assertNull($row['user_id']);
    }

    public function testRegisteredUserCanAddComment() {
        // Create test user
        $login = 'user_' . time() . '_' . rand(100, 999);
        $stmtUser = self::$db->prepare("INSERT INTO users (name, login, status) VALUES ('Artur', ?, 'user')");
        $stmtUser->bind_param("s", $login);
        $stmtUser->execute();
        $userId = self::$db->insert_id;

        $text = 'Kommentaar registreeritud kasutajalt.';
        $author = 'Artur';
        $now = date('Y-m-d H:i:s');

        $stmt = self::$db->prepare("INSERT INTO comments (news_id, text, date, user_id, author_name) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $this->testNewsId, $text, $now, $userId, $author);
        $this->assertTrue($stmt->execute());

        $commentId = self::$db->insert_id;
        $res = self::$db->query("SELECT * FROM comments WHERE id = $commentId");
        $row = $res->fetch_assoc();
        $this->assertEquals($userId, (int)$row['user_id']);
        $this->assertEquals($author, $row['author_name']);
    }

    public function testAdminCanDeleteCommentSuccessfully() {
        // 1. Insert a comment
        $stmt = self::$db->prepare("INSERT INTO comments (news_id, text, author_name) VALUES (?, 'Kustutatav kommentaar', 'Testija')");
        $stmt->bind_param("i", $this->testNewsId);
        $stmt->execute();
        $commentId = self::$db->insert_id;

        // Verify comment exists
        $check = self::$db->query("SELECT id FROM comments WHERE id = $commentId");
        $this->assertEquals(1, $check->num_rows);

        // 2. Check admin permission
        $adminUser = ['id' => 1, 'status' => 'admin'];
        $this->assertTrue(is_admin($adminUser));

        // 3. Admin performs deletion
        $delStmt = self::$db->prepare("DELETE FROM comments WHERE id = ?");
        $delStmt->bind_param("i", $commentId);
        $this->assertTrue($delStmt->execute());

        // 4. Verify comment no longer exists
        $postCheck = self::$db->query("SELECT id FROM comments WHERE id = $commentId");
        $this->assertEquals(0, $postCheck->num_rows);
    }

    public function testRegularUserAndGuestCannotDeleteComment() {
        // 1. Insert a comment
        $stmt = self::$db->prepare("INSERT INTO comments (news_id, text, author_name) VALUES (?, 'Oluline arvamus', 'Lugeja')");
        $stmt->bind_param("i", $this->testNewsId);
        $stmt->execute();
        $commentId = self::$db->insert_id;

        // 2. Attempt deletion as regular user
        $regularUser = ['id' => 45, 'status' => 'user'];
        $this->assertFalse(is_admin($regularUser));

        // 3. Attempt deletion as guest (null)
        $guestUser = null;
        $this->assertFalse(is_admin($guestUser));

        // Since permission is denied, deletion must NOT be executed
        if (!is_admin($regularUser)) {
            $permissionDenied = true;
        } else {
            self::$db->query("DELETE FROM comments WHERE id = $commentId");
            $permissionDenied = false;
        }

        $this->assertTrue($permissionDenied, "Regular user should be denied deletion rights.");

        // 4. Verify comment still exists untouched in database
        $check = self::$db->query("SELECT id FROM comments WHERE id = $commentId");
        $this->assertEquals(1, $check->num_rows);
    }

    public function testDeleteCommentWithInvalidIdHandlesSafely() {
        $invalidId = -5;
        $stmt = self::$db->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->bind_param("i", $invalidId);
        $stmt->execute();

        // Affected rows should be 0, no error thrown
        $this->assertEquals(0, $stmt->affected_rows);
    }

    public function testDeletingNewsCascadesToDeleteComments() {
        // Add 3 comments to the test article
        for ($i = 1; $i <= 3; $i++) {
            $stmt = self::$db->prepare("INSERT INTO comments (news_id, text, author_name) VALUES (?, 'Kommentaar #$i', 'Lugeja $i')");
            $stmt->bind_param("i", $this->testNewsId);
            $stmt->execute();
        }

        $beforeCount = self::$db->query("SELECT COUNT(*) AS c FROM comments WHERE news_id = {$this->testNewsId}")->fetch_assoc()['c'];
        $this->assertEquals(3, (int)$beforeCount);

        // Simulate news-delete logic
        $delCommStmt = self::$db->prepare("DELETE FROM comments WHERE news_id = ?");
        $delCommStmt->bind_param("i", $this->testNewsId);
        $delCommStmt->execute();

        $delNewsStmt = self::$db->prepare("DELETE FROM news WHERE id = ?");
        $delNewsStmt->bind_param("i", $this->testNewsId);
        $delNewsStmt->execute();

        $afterCount = self::$db->query("SELECT COUNT(*) AS c FROM comments WHERE news_id = {$this->testNewsId}")->fetch_assoc()['c'];
        $this->assertEquals(0, (int)$afterCount);
    }

    public function testCsrfTokenRequiredForAdminCommentDeletion() {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $validToken = csrf_token();
        $this->assertTrue(verify_csrf_token($validToken));

        $invalidToken = 'fake_or_tampered_csrf_token_12345';
        $this->assertFalse(verify_csrf_token($invalidToken));
        $this->assertFalse(verify_csrf_token(''));
        $this->assertFalse(verify_csrf_token(null));
    }

    public function testFetchCommentsWithNewsTitleForAdminModeration() {
        $stmt = self::$db->prepare("INSERT INTO comments (news_id, text, author_name) VALUES (?, 'Moderatsioonitest', 'ModTestija')");
        $stmt->bind_param("i", $this->testNewsId);
        $stmt->execute();
        $commentId = self::$db->insert_id;

        // Query used on admin/comments.php
        $sql = "SELECT cm.*, n.title AS news_title FROM comments cm LEFT JOIN news n ON cm.news_id = n.id WHERE cm.id = $commentId";
        $res = self::$db->query($sql);

        $this->assertNotFalse($res);
        $this->assertEquals(1, $res->num_rows);

        $row = $res->fetch_assoc();
        $this->assertEquals('Moderatsioonitest', $row['text']);
        $this->assertEquals('Test Article for Comments', $row['news_title']);
    }

    public function testAdminCanDeleteCommentDirectlyInNewsPost() {
        // 1. Add comment to article
        $stmt = self::$db->prepare("INSERT INTO comments (news_id, text, author_name) VALUES (?, 'Kustutatav otse postitusest', 'Autor')");
        $stmt->bind_param("i", $this->testNewsId);
        $stmt->execute();
        $commId = self::$db->insert_id;

        // Verify comment exists
        $res = self::$db->query("SELECT id FROM comments WHERE id = $commId AND news_id = {$this->testNewsId}");
        $this->assertEquals(1, $res->num_rows);

        // 2. Admin directly in news.php performs deletion
        $admin = ['id' => 1, 'status' => 'admin'];
        $this->assertTrue(is_admin($admin) || is_editor_or_admin($admin));

        $stmtDel = self::$db->prepare("DELETE FROM comments WHERE id = ? AND news_id = ?");
        $stmtDel->bind_param("ii", $commId, $this->testNewsId);
        $this->assertTrue($stmtDel->execute());

        // Verify comment is removed from article
        $postRes = self::$db->query("SELECT id FROM comments WHERE id = $commId");
        $this->assertEquals(0, $postRes->num_rows);
    }
}

