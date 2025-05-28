<?php
use PHPUnit\Framework\TestCase;

class RegisterModule
{
  public function connectDatabase()
  {
    $conn = new mysqli("127.0.0.1", "root", "", "login");
    if ($conn->connect_error) {
      throw new Exception("DB connection failed");
    }
    return $conn;
  }

  public function isEmailRegistered($conn, $email)
  {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
  }

  public function generateVerificationCode()
  {
    return bin2hex(random_bytes(16));
  }

  public function insertUser($conn, $data, $code)
  {
    $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check->bind_param("s", $data['email']);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Hapus jika sudah ada
        $delete = $conn->prepare("DELETE FROM users WHERE email = ?");
        $delete->bind_param("s", $data['email']);
        $delete->execute();
    }

    $stmt = $conn->prepare("INSERT INTO users (name, email, phone, username, address, password, verification_code, is_verified) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("sssssss", $data['name'], $data['email'], $data['phone'], $data['username'], $data['address'], $data['password'], $code);
    return $stmt->execute();
  }

  public function sendVerificationEmail($email, $name, $phone, $username, $code)
  {
    $mail = new PHPMailer\PHPMailer\PHPMailer();

    $mail->isSMTP();
    $mail->Host = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth = true;
    $mail->Username = '75b15c3696f227';
    $mail->Password = '3d6486452457cf';
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('from@example.com', 'Mailer');
    $mail->addAddress($email);
    $mail->Subject = 'Registration Details';
    $mail->Body = "Welcome $name. Your code is $code.";

    return $mail->send();
  }
}

class RegisterTest extends TestCase
{
  private $module;

  protected function setUp(): void
  {
    $this->module = new RegisterModule();
  }

  public function testDatabaseConnectionSuccess()
  {
    $conn = $this->module->connectDatabase();
    $this->assertInstanceOf(mysqli::class, $conn);
  }

  public function testEmailAlreadyExists()
  {
    $conn = $this->module->connectDatabase();
    $email = "existing@example.com";
    $this->assertFalse($this->module->isEmailRegistered($conn, $email));
  }

  public function testInsertUserSuccess()
  {
    $conn = $this->module->connectDatabase();
    $data = [
      'name' => 'Test User',
      'email' => 'unit@example.com',
      'phone' => '123456',
      'username' => 'unituser',
      'address' => 'Test Address',
      'password' => password_hash('1234', PASSWORD_BCRYPT),
    ];
    $code = $this->module->generateVerificationCode();
    $result = $this->module->insertUser($conn, $data, $code);
    $this->assertTrue($result);
  }

}