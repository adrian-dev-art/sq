<?php

use PHPUnit\Framework\TestCase;

class VerifyTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new mysqli("127.0.0.1", "root", "", "login");
        $this->conn->query("DELETE FROM users WHERE username = 'verify_test'");

        $this->conn->query("INSERT INTO users (name, email, phone, username, address, password, verification_code, is_verified)
            VALUES ('Verify Tester', 'verify@example.com', '0000000000', 'verify_test', 'Test Address', '" . password_hash('pass123', PASSWORD_BCRYPT) . "', '123456', 0)");
    }

    public function testNoVerificationCode()
    {
        $_GET = [];
        ob_start();
        include 'verify.php';
        $output = ob_get_clean();
        $this->assertStringContainsString("No verification code provided.", $output);
    }

    public function testInvalidCode()
    {
        $_GET['code'] = 'wrongcode';
        $_POST['verification_code'] = 'wrongcode';
        $_SERVER["REQUEST_METHOD"] = "POST";
        ob_start();
        include 'verify.php';
        $output = ob_get_clean();
        $this->assertStringContainsString("Invalid verification code.", $output);
    }

    public function testVerificationUpdateFails()
    {
        // Simulasi query update gagal dengan menghapus user terlebih dahulu
        $this->conn->query("DELETE FROM users WHERE verification_code = '123456'");
        $this->conn->query("INSERT INTO users (name, email, phone, username, address, password, verification_code, is_verified)
            VALUES ('Verify Tester', 'verify@example.com', '0000000000', 'verify_test', 'Test Address', '" . password_hash('pass123', PASSWORD_BCRYPT) . "', '123456', 0)");

        // Lock the table to force update failure (opsional untuk simulasi lebih lanjut)
        $_GET['code'] = '123456';
        $_POST['verification_code'] = '123456';
        $_SERVER["REQUEST_METHOD"] = "POST";

        // Rename query to simulate failure (force error)
        $this->conn->query("RENAME TABLE users TO users_backup");

        ob_start();
        include 'verify.php';
        $output = ob_get_clean();

        $this->conn->query("RENAME TABLE users_backup TO users"); // restore

        $this->assertStringContainsString("Error verifying account.", $output);
    }

    public function testSuccessfulVerification()
    {
        $_GET['code'] = '123456';
        $_POST['verification_code'] = '123456';
        $_SERVER["REQUEST_METHOD"] = "POST";

        ob_start();
        include 'verify.php';
        $output = ob_get_clean();

        $userCheck = $this->conn->query("SELECT * FROM users WHERE verification_code = '123456'")->fetch_assoc();
        $this->assertEquals(1, $userCheck['is_verified']);
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM users WHERE username = 'verify_test'");
        $this->conn->close();
    }
}

?>