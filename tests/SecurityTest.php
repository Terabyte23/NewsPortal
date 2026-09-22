<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../db.php';

class SecurityTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }

    public function testCsrfTokenGenerationAndValidation() {
        $token = csrf_token();
        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token));

        // Test valid token
        $this->assertTrue(verify_csrf_token($token));

        // Test invalid token
        $this->assertFalse(verify_csrf_token('invalid_token_123456'));
        $this->assertFalse(verify_csrf_token(''));
        $this->assertFalse(verify_csrf_token(null));
    }

    public function testPasswordHashingBcrypt() {
        $plain = 'Salasona2026!';
        $hash = password_hash($plain, PASSWORD_DEFAULT);

        $this->assertNotEquals($plain, $hash);
        $this->assertTrue(password_verify($plain, $hash));
        $this->assertFalse(password_verify('ValeParool', $hash));
    }

    public function testRoleAccessPermissions() {
        $admin = ['status' => 'admin'];
        $editor = ['status' => 'editor'];
        $reader = ['status' => 'user'];

        $this->assertTrue(is_admin($admin));
        $this->assertFalse(is_admin($editor));
        $this->assertFalse(is_admin($reader));

        $this->assertTrue(is_editor_or_admin($admin));
        $this->assertTrue(is_editor_or_admin($editor));
        $this->assertFalse(is_editor_or_admin($reader));
    }

    public function testHtmlSpecialCharsEscaping() {
        $malicious = "<script>alert('XSS')</script>";
        $escaped = htmlspecialchars($malicious, ENT_QUOTES, 'UTF-8');

        $this->assertStringNotContainsString("<script>", $escaped);
        $this->assertEquals("&lt;script&gt;alert(&#039;XSS&#039;)&lt;/script&gt;", $escaped);
    }
}
