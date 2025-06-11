<?php
// Start the session to store user data after verification
session_start();

// Check if the verification code is passed via GET (the user is redirected here from the registration process)
if (isset($_GET['code'])) {
    $verification_code = $_GET['code'];
} else {
    // If no code is passed, show an error
    echo("No verification code provided.");
    return;
}

// Initialize error and success message variables
$alert_message = "";
$alert_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_code = $_POST['verification_code'];

    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "login");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if the entered verification code matches the one in the database
    $sql = "SELECT * FROM users WHERE verification_code = '$input_code' AND is_verified = FALSE";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Update the user's status to verified
        $update_sql = "UPDATE users SET is_verified = TRUE WHERE verification_code = '$input_code'";
        if ($conn->query($update_sql) === TRUE) {
            // Set session variable to indicate successful verification
            $_SESSION['verification_success'] = true;
            header('Location: login.php'); // Redirect to login page after successful verification
            exit();
        } else {
            $alert_message = "Error verifying account.";
            $alert_class = "alert-danger";
        }
    } else {
        $alert_message = "Invalid verification code.";
        $alert_class = "alert-danger";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        /* Global styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Verify container styles */
        .verify-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
            text-align: center;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        /* Input field styles */
        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            outline: none;
        }

        input[type="text"]:focus {
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

<div class="verify-container">
    <h2>Email Verification</h2>

    <!-- Display alert if there is an error or success message -->
    <?php if (!empty($alert_message)): ?>
        <div class="alert <?php echo $alert_class; ?>"><?php echo $alert_message; ?></div>
    <?php endif; ?>

    <!-- Form for user to input the verification code -->
    <form method="POST" action="verify.php?code=<?php echo $verification_code; ?>">
        <input type="text" name="verification_code" placeholder="Enter Verification Code" required><br>
        <button type="submit">Verify</button>
    </form>

    <p>Once your account is verified, you can <a href="login.php">login</a>.</p>
</div>

</body>
</html>
