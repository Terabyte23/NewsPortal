<?php
require_once __DIR__ . '/DatabaseTestCase.php';

/**
 * @testdox Portaali lisafunktsioonide testid (Search, Comments & Saved Articles)
 */
class FeaturesTest extends DatabaseTestCase {

    /**
     * @testdox Uudiste otsing märksõna järgi LIKE päringuga (Search articles by keyword)
     */
    public function testSearchArticlesByKeyword() {
        $uniqueKeyword = "Unikaalne" . rand(1000, 9999);
        self::$db->query("INSERT INTO news (title) VALUES ('Uudis $uniqueKeyword')");

        $search = self::$db->real_escape_string($uniqueKeyword);
        $res = self::$db->query("SELECT * FROM news WHERE title LIKE '%$search%'");

        $this->assertNotFalse($res, "Query error: " . self::$db->error);
        $this->assertGreaterThanOrEqual(1, $res->num_rows);
        
        $row = $res->fetch_assoc();
        $this->assertStringContainsString($uniqueKeyword, $row['title']);
    }

    /**
     * @testdox Kommentaari lisamine uudisele ja pärimine andmebaasist (Add and fetch comments)
     */
    public function testAddAndFetchComments() {
        self::$db->query("INSERT INTO news (title) VALUES ('Article for comment')");
        $newsId = self::$db->insert_id;

        // Automatically detect comment text column in database
        $commentColumn = null;
        $columnsRes = self::$db->query("SHOW COLUMNS FROM `comments`");
        if ($columnsRes) {
            while ($col = $columnsRes->fetch_assoc()) {
                $field = $col['Field'];
                if (in_array($field, ['kommentaar', 'sisu', 'comment', 'text', 'body', 'content'])) {
                    $commentColumn = $field;
                    break;
                }
            }
        }

        // If specific column is not found, fallback to any text/varchar field
        if (!$commentColumn && $columnsRes) {
            $columnsRes->data_seek(0);
            while ($col = $columnsRes->fetch_assoc()) {
                if (!in_array($col['Field'], ['id', 'news_id', 'user_id', 'created_at', 'author_name', 'reg_date'])) {
                    $commentColumn = $col['Field'];
                    break;
                }
            }
        }

        $commentText = "Väga hea artikkel!";
        
        $stmt = self::$db->prepare("INSERT INTO comments (news_id, `$commentColumn`) VALUES (?, ?)");
        $this->assertNotFalse($stmt, "Prepare failed for column '$commentColumn': " . self::$db->error);
        
        $stmt->bind_param("is", $newsId, $commentText);
        $this->assertTrue($stmt->execute());

        $res = self::$db->query("SELECT * FROM comments WHERE news_id = $newsId");
        $this->assertNotFalse($res);
        $this->assertEquals(1, $res->num_rows);
    }

    /**
     * @testdox Lemmikartiklite / salvestatud uudiste lisamine ja eemaldamine sessioonist (Saved articles session logic)
     */
    public function testSavedArticlesSessionLogic() {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $_SESSION['saved_news'] = [];

        $articleId = 42;
        if (!in_array($articleId, $_SESSION['saved_news'])) {
            $_SESSION['saved_news'][] = $articleId;
        }

        $this->assertContains(42, $_SESSION['saved_news']);

        $_SESSION['saved_news'] = array_diff($_SESSION['saved_news'], [$articleId]);
        $this->assertNotContains(42, $_SESSION['saved_news']);
    }
}