<?php
require_once __DIR__ . '/DatabaseTestCase.php';

class UserManagementTest extends DatabaseTestCase {

    public function testUserRegistrationAndDuplicateCheck() {
        $login = "testuser_" . time();
        $email = "test@newsportal.ee";

        // Создаем пользователя
        $stmt = self::$db->prepare("INSERT INTO users (name, email, login, parol, status) VALUES ('Test', ?, ?, 'pass123', 'user')");
        $stmt->bind_param("ss", $email, $login);
        $this->assertTrue($stmt->execute());

        // Проверка дубликата
        $check = self::$db->query("SELECT id FROM users WHERE login = '$login'");
        $this->assertEquals(1, $check->num_rows);
    }

    public function testUpdateUserRole() {
        self::$db->query("INSERT INTO users (name, login, status) VALUES ('Promote Me', 'promuser', 'user')");
        $userId = self::$db->insert_id;

        self::$db->query("UPDATE users SET status = 'editor' WHERE id = $userId");

        $res = self::$db->query("SELECT status FROM users WHERE id = $userId");
        $user = $res->fetch_assoc();
        $this->assertEquals('editor', $user['status']);
    }
}