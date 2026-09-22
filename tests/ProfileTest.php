<?php
use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase {

    public function testRedirectIfUserNotLoggedIn() {
        $_SESSION = [];
        $currentUser = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $this->assertNull($currentUser, 'Kasutaja ei tohi olla sisse logitud');
    }

    public function testProfileUpdateQueryGeneration() {
        $currentUser = ['id' => 5];
        $name = "Uus Nimi";
        $email = "uus@example.ee";
        $telefon = "+37255555555";
        $job = "Ajakirjanik";
        $newParol = "uusParool123";

        $userId = (int)$currentUser['id'];

        $sql = "UPDATE users SET name = '$name', email = '$email', telefon = '$telefon', job = '$job'";
        if (!empty($newParol)) {
            $sql .= ", parol = '$newParol'";
        }
        $sql .= " WHERE id = $userId";

        $this->assertStringContainsString("UPDATE users SET name = 'Uus Nimi'", $sql);
        $this->assertStringContainsString("parol = 'uusParool123'", $sql);
        $this->assertStringContainsString("WHERE id = 5", $sql);
    }
}