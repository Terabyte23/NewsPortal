<?php
use PHPUnit\Framework\TestCase;

// Include tested file
require_once __DIR__ . '/../db.php';

/**
 * @testdox Andmebaasi abifunktsioonide testid (Database & Formatting Helpers)
 */
class DbHelpersTest extends TestCase {

    // --- 1. calculate_reading_time tests ---

    /**
     * @testdox Lugemisaja arvutamine: minimaalne lugemisaeg on 1 minut (Calculate reading time returns minimum 1 min)
     */
    public function testCalculateReadingTimeReturnsMinimumOneMinute() {
        $text = "Lühike tekst.";
        $this->assertEquals(1, calculate_reading_time($text));
    }

    /**
     * @testdox Lugemisaja arvutamine pikale tekstile (360 sõna = 2 minutit) (Calculate reading time for long text)
     */
    public function testCalculateReadingTimeForLongText() {
        // 360 words -> 360 / 180 = 2 minutes
        $words = implode(' ', array_fill(0, 360, 'word'));
        $this->assertEquals(2, calculate_reading_time($words));
    }

    /**
     * @testdox Lugemisaja arvutamisel eemaldatakse HTML-sildid (Calculate reading time strips HTML tags)
     */
    public function testCalculateReadingTimeStripsHtmlTags() {
        $textWithHtml = "<p>" . implode(' ', array_fill(0, 180, '<span>word</span>')) . "</p>";
        $this->assertEquals(1, calculate_reading_time($textWithHtml));
    }

    // --- 2. User roles verification tests ---

    /**
     * @testdox Administraatori staatuse tuvastamine: is_admin tagastab true admin rollidele (is_admin returns true for admin)
     */
    public function testIsAdminReturnsTrueForAdminStatuses() {
        $adminUser = ['status' => 'admin'];
        $administratorUser = ['status' => 'Administrator'];

        $this->assertTrue(is_admin($adminUser));
        $this->assertTrue(is_admin($administratorUser));
    }

    /**
     * @testdox Administraatori staatuse tuvastamine: is_admin tagastab false tavakasutajale ja tühjale väärtusele
     */
    public function testIsAdminReturnsFalseForRegularUsersAndNull() {
        $normalUser = ['status' => 'user'];
        $editorUser = ['status' => 'editor'];

        $this->assertFalse(is_admin($normalUser));
        $this->assertFalse(is_admin($editorUser));
        $this->assertFalse(is_admin(null));
        $this->assertFalse(is_admin([]));
    }

    /**
     * @testdox Toimetaja või administraatori rolli tuvastamine: is_editor_or_admin tagastab true volitatud rollidele
     */
    public function testIsEditorOrAdminReturnsTrueForAuthorizedRoles() {
        $admin = ['status' => 'admin'];
        $editor = ['status' => 'editor'];
        $journalist = ['status' => 'journalist'];

        $this->assertTrue(is_editor_or_admin($admin));
        $this->assertTrue(is_editor_or_admin($editor));
        $this->assertTrue(is_editor_or_admin($journalist));
    }

    /**
     * @testdox Toimetaja või administraatori kontroll tagastab false tavalugejale (is_editor_or_admin returns false for regular user)
     */
    public function testIsEditorOrAdminReturnsFalseForUser() {
        $user = ['status' => 'user'];
        $this->assertFalse(is_editor_or_admin($user));
        $this->assertFalse(is_editor_or_admin(null));
    }

    // --- 3. Date & time formatting tests ---

    /**
     * @testdox Suhteline ajavorming: äsja lisatud kirje kuvab 'Just praegu' (Format time ago just now)
     */
    public function testFormatTimeAgoJustNow() {
        $now = date('Y-m-d H:i:s');
        $this->assertEquals('Just praegu', format_time_ago($now));
    }

    /**
     * @testdox Suhteline ajavorming: 5 minutit tagasi (Format time ago minutes)
     */
    public function testFormatTimeAgoMinutes() {
        $fiveMinsAgo = date('Y-m-d H:i:s', time() - 300);
        $this->assertEquals('5 min tagasi', format_time_ago($fiveMinsAgo));
    }

    /**
     * @testdox Suhteline ajavorming: 2 tundi tagasi (Format time ago hours)
     */
    public function testFormatTimeAgoHours() {
        $twoHoursAgo = date('Y-m-d H:i:s', time() - 7200);
        $this->assertEquals('2 tundi tagasi', format_time_ago($twoHoursAgo));
    }

    /**
     * @testdox Suhteline ajavorming: 3 päeva tagasi (Format time ago days)
     */
    public function testFormatTimeAgoDays() {
        $threeDaysAgo = date('Y-m-d H:i:s', time() - (86400 * 3));
        $this->assertEquals('3 päeva tagasi', format_time_ago($threeDaysAgo));
    }

    /**
     * @testdox Suhteline ajavorming: vanale kuupäevale tagastatakse standardvorming (Format time ago old date)
     */
    public function testFormatTimeAgoOldDateReturnsStandardFormat() {
        $oldDate = '2020-01-15 10:00:00';
        $this->assertEquals('15.01.2020', format_time_ago($oldDate));
    }

    /**
     * @testdox Kuupäeva vormindamine eesti keeles (Format Estonian date: päev. kuu aasta, kell)
     */
    public function testFormatEstonianDate() {
        $dateStr = '2026-09-10 14:30:00';
        $formatted = format_estonian_date($dateStr);
        $this->assertEquals('10. september 2026, 14:30', $formatted);
    }

    /**
     * @testdox Kuupäeva vormindamine tühja väärtuse korral tagastab vaikeväärtuse 'Täna'
     */
    public function testFormatEstonianDateReturnsDefaultWhenNull() {
        $this->assertEquals('Täna', format_estonian_date(null));
    }

    // --- 4. get_article_image tests ---

    /**
     * @testdox Artikli pildi URL: tagastab otse määratud image_url väärtuse (Explicit article image URL)
     */
    public function testGetArticleImageReturnsExplicitImageUrl() {
        $news = [
            'id' => 10,
            'image_url' => 'https://example.com/test-image.jpg'
        ];
        $this->assertEquals('https://example.com/test-image.jpg', get_article_image($news));
    }

    /**
     * @testdox Artikli pildi URL: pildi puudumisel tagastatakse Unsplash varupilt (Fallback to Unsplash image)
     */
    public function testGetArticleImageFallbackToUnsplashForNonExistingLocalFile() {
        $news = [
            'id' => 999, // Non-existent ID for fallback test
            'image_url' => null
        ];
        $image = get_article_image($news);
        $this->assertStringContainsString('images.unsplash.com', $image);
    }
}