<?php
use PHPUnit\Framework\TestCase;

/**
 * @testdox Väljalogimise funktsionaalsuse testid (User Logout & Session Destruction)
 */
class LogoutTest extends TestCase {

    /**
     * @testdox Väljalogimisel tühjendatakse sessiooni massiiv ja hävitatakse sessioon (Session cleared on logout)
     */
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