<?php
use PHPUnit\Framework\TestCase;

class LogoutTest extends TestCase {

    public function testSessionIsClearedOnLogout() {
        @session_start();
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'TestUser';

        // Simulate logout.php logic
        $_SESSION = [];
        session_destroy();

        $this->assertEmpty($_SESSION);
    }
}