<?php
require_once __DIR__ . '/DatabaseTestCase.php';

/**
 * @testdox Uudiste andmebaasi funktsioonide testid (News Database Operations)
 */
class NewsDatabaseTest extends DatabaseTestCase {

    /**
     * @testdox Vigase või aegunud sessiooni korral tagastatakse sisselogitud kasutajaks null
     */
    public function testGetLoggedInUserReturnsNullForInvalidSession() {
        $_SESSION['user_id'] = 999999;
        $user = get_logged_in_user(self::$db);
        $this->assertNull($user);
    }

    /**
     * @testdox Uue uudiseartikli loomine ja andmebaasist pärimine (Create and fetch news article)
     */
    public function testCreateAndFetchNewsArticle() {
        $title = "Uudis Test " . time();
        
        // News table uses column 'title'
        $stmt = self::$db->prepare("INSERT INTO news (title) VALUES (?)");
        $this->assertNotFalse($stmt, "Prepare failed: " . self::$db->error);
        
        $stmt->bind_param("s", $title);
        $this->assertTrue($stmt->execute());
        
        $newId = self::$db->insert_id;
        
        $res = self::$db->query("SELECT * FROM news WHERE id = $newId");
        $this->assertNotFalse($res, "Query failed: " . self::$db->error);
        
        $article = $res->fetch_assoc();
        $this->assertEquals($title, $article['title']);
    }

    /**
     * @testdox Artikli vaatamiste ja meeldimiste (likes/views) loenduri suurendamine andmebaasis
     */
    public function testIncrementNewsViewsAndLikes() {
        $resInsert = self::$db->query("INSERT INTO news (title) VALUES ('Test View')");
        $this->assertTrue($resInsert, "Insert failed: " . self::$db->error);
        $newsId = self::$db->insert_id;

        self::$db->query("UPDATE news SET views = views + 1, likes = likes + 1 WHERE id = $newsId");

        $res = self::$db->query("SELECT views, likes FROM news WHERE id = $newsId");
        $this->assertNotFalse($res);
        $updated = $res->fetch_assoc();

        $this->assertNotNull($updated);
        $this->assertEquals(1, $updated['views']);
        $this->assertEquals(1, $updated['likes']);
    }

    /**
     * @testdox Uudiste filtreerimine kategooria järgi (Fetch news by category)
     */
    public function testFetchNewsByCategory() {
        // Create test category
        $catName = 'TestCat_' . time();
        $resCat = self::$db->query("INSERT INTO category (name) VALUES ('$catName')");
        $this->assertTrue($resCat, "Category insert failed: " . self::$db->error);
        $catId = self::$db->insert_id;

        // Insert news associated with category
        $resNews = self::$db->query("INSERT INTO news (title, category_id) VALUES ('Cat News', $catId)");
        $this->assertTrue($resNews, "News insert failed: " . self::$db->error);

        $res = self::$db->query("SELECT * FROM news WHERE category_id = $catId");
        $this->assertNotFalse($res);
        $this->assertEquals(1, $res->num_rows);
    }
}