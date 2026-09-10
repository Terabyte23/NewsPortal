<?php
use PHPUnit\Framework\TestCase;

// Подключаем тестируемый файл
require_once __DIR__ . '/../db.php';

class DbHelpersTest extends TestCase {

    // --- 1. Тесты функции calculate_reading_time ---

    public function testCalculateReadingTimeReturnsMinimumOneMinute() {
        $text = "Lühike tekst.";
        $this->assertEquals(1, calculate_reading_time($text));
    }

    public function testCalculateReadingTimeForLongText() {
        // 360 латинских слов без спецсимволов -> 360 / 180 = 2 минуты
        $words = implode(' ', array_fill(0, 360, 'word'));
        $this->assertEquals(2, calculate_reading_time($words));
    }

    public function testCalculateReadingTimeStripsHtmlTags() {
        $textWithHtml = "<p>" . implode(' ', array_fill(0, 180, '<span>word</span>')) . "</p>";
        $this->assertEquals(1, calculate_reading_time($textWithHtml));
    }

    // --- 2. Тесты проверки ролей пользователей ---

    public function testIsAdminReturnsTrueForAdminStatuses() {
        $adminUser = ['status' => 'admin'];
        $administratorUser = ['status' => 'Administrator'];

        $this->assertTrue(is_admin($adminUser));
        $this->assertTrue(is_admin($administratorUser));
    }

    public function testIsAdminReturnsFalseForRegularUsersAndNull() {
        $normalUser = ['status' => 'user'];
        $editorUser = ['status' => 'editor'];

        $this->assertFalse(is_admin($normalUser));
        $this->assertFalse(is_admin($editorUser));
        $this->assertFalse(is_admin(null));
        $this->assertFalse(is_admin([]));
    }

    public function testIsEditorOrAdminReturnsTrueForAuthorizedRoles() {
        $admin = ['status' => 'admin'];
        $editor = ['status' => 'editor'];
        $journalist = ['status' => 'journalist'];

        $this->assertTrue(is_editor_or_admin($admin));
        $this->assertTrue(is_editor_or_admin($editor));
        $this->assertTrue(is_editor_or_admin($journalist));
    }

    public function testIsEditorOrAdminReturnsFalseForUser() {
        $user = ['status' => 'user'];
        $this->assertFalse(is_editor_or_admin($user));
        $this->assertFalse(is_editor_or_admin(null));
    }

    // --- 3. Тесты форматирования времени и дат ---

    public function testFormatTimeAgoJustNow() {
        $now = date('Y-m-d H:i:s');
        $this->assertEquals('Just praegu', format_time_ago($now));
    }

    public function testFormatTimeAgoMinutes() {
        $fiveMinsAgo = date('Y-m-d H:i:s', time() - 300);
        $this->assertEquals('5 min tagasi', format_time_ago($fiveMinsAgo));
    }

    public function testFormatTimeAgoHours() {
        $twoHoursAgo = date('Y-m-d H:i:s', time() - 7200);
        $this->assertEquals('2 tundi tagasi', format_time_ago($twoHoursAgo));
    }

    public function testFormatTimeAgoDays() {
        $threeDaysAgo = date('Y-m-d H:i:s', time() - (86400 * 3));
        $this->assertEquals('3 päeva tagasi', format_time_ago($threeDaysAgo));
    }

    public function testFormatTimeAgoOldDateReturnsStandardFormat() {
        $oldDate = '2020-01-15 10:00:00';
        $this->assertEquals('15.01.2020', format_time_ago($oldDate));
    }

    public function testFormatEstonianDate() {
        $dateStr = '2026-09-10 14:30:00';
        $formatted = format_estonian_date($dateStr);
        $this->assertEquals('10. september 2026, 14:30', $formatted);
    }

    public function testFormatEstonianDateReturnsDefaultWhenNull() {
        $this->assertEquals('Täna', format_estonian_date(null));
    }

    // --- 4. Тесты функции get_article_image ---

    public function testGetArticleImageReturnsExplicitImageUrl() {
        $news = [
            'id' => 10,
            'image_url' => 'https://example.com/test-image.jpg'
        ];
        $this->assertEquals('https://example.com/test-image.jpg', get_article_image($news));
    }

    public function testGetArticleImageFallbackToUnsplashForNonExistingLocalFile() {
        $news = [
            'id' => 999, // Используем заведомо несуществующий ID
            'image_url' => null
        ];
        $image = get_article_image($news);
        $this->assertStringContainsString('images.unsplash.com', $image);
    }
}