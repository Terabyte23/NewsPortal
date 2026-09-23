<?php
require_once __DIR__ . '/DatabaseTestCase.php';

/**
 * @testdox Kasutajate haldamise ja rollide muutmise testid (User Management & Role Assignment)
 */
class UserManagementTest extends DatabaseTestCase {

    /**
     * @testdox Kasutaja loomine andmebaasi ja unikaalse kasutajanime (login) duplikaadi kontroll
     */
    public function testUserRegistrationAndDuplicateCheck() {
        $login = "testuser_" . time();
        $email = "test@newsportal.ee";

        // Create user
        $stmt = self::$db->prepare("INSERT INTO users (name, email, login, parol, status) VALUES ('Test', ?, ?, 'pass123', 'user')");
        $stmt->bind_param("ss", $email, $login);
        $this->assertTrue($stmt->execute());

        // Duplicate check
        $check = self::$db->query("SELECT id FROM users WHERE login = '$login'");
        $this->assertEquals(1, $check->num_rows);
    }

    /**
     * @testdox Kasutaja rolli muutmine administraatori poolt (nt kasutaja ülendamine toimetajaks: user -> editor)
     */
    public function testUpdateUserRole() {
        self::$db->query("INSERT INTO users (name, login, status) VALUES ('Promote Me', 'promuser', 'user')");
        $userId = self::$db->insert_id;

        self::$db->query("UPDATE users SET status = 'editor' WHERE id = $userId");

        $res = self::$db->query("SELECT status FROM users WHERE id = $userId");
        $user = $res->fetch_assoc();
        $this->assertEquals('editor', $user['status']);
    }
}