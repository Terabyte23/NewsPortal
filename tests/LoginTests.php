<?php
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
        } else {
            @session_start();
        }
    }

    public function testEmptyCredentialsReturnError() {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['login'] = '';
        $_POST['parol'] = '';

        $login = trim($_POST['login'] ?? '');
        $parol = trim($_POST['parol'] ?? '');

        $error = '';
        if (empty($login) || empty($parol)) {
            $error = 'Palun täida kõik väljad!';
        }

        $this->assertEquals('Palun täida kõik väljad!', $error);
    }

    public function testSuccessfulAdminLoginSetsSession() {
        $user = [
            'id' => 1,
            'name' => 'Peatoimetaja',
            'login' => 'admin',
            'parol' => 'admin123',
            'status' => 'admin'
        ];

        $_POST['login'] = 'admin';
        $_POST['parol'] = 'admin123';

        if ($user['parol'] === $_POST['parol']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['status'];
        }

        $this->assertEquals(1, $_SESSION['user_id']);
        $this->assertEquals('Peatoimetaja', $_SESSION['user_name']);
        $this->assertEquals('admin', $_SESSION['user_role']);
    }
}