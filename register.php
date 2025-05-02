<?php
// Include PHPMailer classes for email verification
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

$alert_message = "";
$alert_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "login");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if email already exists
    $email_check = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($email_check);

    if ($result->num_rows > 0) {
        $alert_message = "Email already registered. Please use a different email.";
        $alert_class = "alert-danger";
    } else {
        // Generate verification code
        $verification_code = bin2hex(random_bytes(16));

        // Insert user data into the database
        $sql = "INSERT INTO users (name, email, phone, username, password, verification_code, is_verified)
                VALUES ('$name', '$email', '$phone', '$username', '$password', '$verification_code', FALSE)";

        if ($conn->query($sql) === TRUE) {
            // Send email with registration details and verification code
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            try {
                $mail->isSMTP();
                $mail->Host = 'sandbox.smtp.mailtrap.io';  // Mailtrap SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = '75b15c3696f227';  // Mailtrap username
                $mail->Password = '3d6486452457cf';  // Mailtrap password
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('from@example.com', 'Mailer');  // Update the "from" address
                $mail->addAddress($email);  // User's email address

                $mail->Subject = 'Registration Details and Email Verification';

                // HTML body with registration details and a link to input the verification code
                $mail->Body    = "
                    <html>
                    <head>
                        <title>Registration Details</title>
                    </head>
                    <body>
                        <h2>Welcome, $name!</h2>
                        <p>Thank you for registering. Here are your registration details:</p>
                        <h3>Your Registration Information:</h3>
                        <table border='1'>
                            <tr><td><strong>Name:</strong></td><td>$name</td></tr>
                            <tr><td><strong>Email:</strong></td><td>$email</td></tr>
                            <tr><td><strong>Phone:</strong></td><td>$phone</td></tr>
                            <tr><td><strong>Username:</strong></td><td>$username</td></tr>
                        </table>
                        <p>Click <a href='http://localhost/sq-login/verify.php?code=$verification_code'>here</a> to verify your email address.</p>
                        <p>If the link doesn't work, visit <strong>http://localhost/sq-login/verify.php</strong> and manually enter the verification code:</p>
                        <p><strong>Verification Code: $verification_code</strong></p>
                    </body>
                    </html>";

                // Send the email
                $mail->send();

                // Redirect to the verification page
                $_SESSION['verification_success'] = true;
                header('Location: verify.php?code=' . $verification_code);
                exit();

            } catch (Exception $e) {
                $alert_message = "Email could not be sent. Error: {$mail->ErrorInfo}";
                $alert_class = "alert-danger";
            }
        } else {
            $alert_message = "Error: " . $conn->error;
            $alert_class = "alert-danger";
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .register-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        /* Input field styles */
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            outline: none;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus {
            border-color: #4CAF50;
        }

        /* Button styles */
        button {
            width: 100%;
            padding: 14px;
            background-color: #4CAF50;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Link styles */
        .create-account {
            margin-top: 20px;
            font-size: 14px;
        }

        .create-account a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }

        .create-account a:hover {
            text-decoration: underline;
        }

        /* Alert styles */
        .alert {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
            text-align: center;
        }

        .alert-warning {
            background-color: #ffcc00;
            color: #fff;
        }

        .alert-danger {
            background-color: #f44336;
            color: #fff;
        }

        .alert-success {
            background-color: #4CAF50;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="register-container">
    <h2>Create Account</h2>

    <!-- Show alert if there is an error or success message -->
    <?php if (!empty($alert_message)): ?>
        <div class="alert <?php echo $alert_class; ?>"><?php echo $alert_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <input type="text" name="name" placeholder="Full Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="text" name="phone" placeholder="Phone Number" required><br>
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Register</button>
    </form>

    <div class="create-account">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

</body>
</html>
