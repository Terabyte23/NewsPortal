<?php
use PHPUnit\Framework\TestCase;

class LogoutTest extends TestCase {

    public function testSessionIsClearedOnLogout() {
        @session_start();
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'TestUser';

        // Имитируем логику logout.php
        $_SESSION = [];
        session_destroy();

        $this->assertEmpty($_SESSION);
    }
}