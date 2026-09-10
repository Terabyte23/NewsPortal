<?php
use PHPUnit\Framework\TestCase;

class RegisterTest extends TestCase {

    public function testValidationFailsWithMissingFields() {
        $_POST = [
            'name' => 'Mati',
            'email' => '',
            'login' => 'mati23',
            'parol' => ''
        ];

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $login = trim($_POST['login'] ?? '');
        $parol = trim($_POST['parol'] ?? '');

        $error = '';
        if (empty($name) || empty($login) || empty($parol) || empty($email)) {
            $error = 'Palun täida kõik nõutud väljad!';
        }

        $this->assertEquals('Palun täida kõik nõutud väljad!', $error);
    }
}