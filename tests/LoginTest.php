<?php
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $this->conn = new mysqli("127.0.0.1", "root", "", "login");
        $this->conn->query("DELETE FROM users WHERE username IN ('ghost_user', 'valid_user')");
        $passwordHash = password_hash('correctPass', PASSWORD_BCRYPT);
        $this->conn->query("INSERT INTO users (name, email, phone, username, address, password, is_verified)
                            VALUES ('Test', 'test@example.com', '1234567890', 'valid_user', 'Address', '$passwordHash', 1)");
    }

    public function testUsernameNotFound()
    {
        $result = $this->login('ghost_user', 'anyPass123');
        $this->assertEquals('Username not found.', $result);
    }

    public function testIncorrectPassword()
    {
        $result = $this->login('valid_user', 'wrongPass');
        $this->assertEquals('Incorrect password.', $result);
    }

    public function testNotVerifiedAccount()
    {
        $this->conn->query("UPDATE users SET is_verified = 0 WHERE username = 'valid_user'");
        $result = $this->login('valid_user', 'correctPass');
        $this->assertEquals('Please verify your email address first.', $result);
    }

    public function testSuccessfulLogin()
    {
        $this->conn->query("UPDATE users SET is_verified = 1 WHERE username = 'valid_user'");
        $result = $this->login('valid_user', 'correctPass');
        $this->assertEquals('success', $result);
    }

    private function login($username, $password)
    {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) return "Username not found.";
        if (!password_verify($password, $user['password'])) return "Incorrect password.";
        if (!$user['is_verified']) return "Please verify your email address first.";
        return "success"; // Assume redirect
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM users WHERE username = 'valid_user'");
        $this->conn->close();
    }
}
?>