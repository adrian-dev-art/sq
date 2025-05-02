<?php
session_start(); // Start the session

// Check if the verification success session is set and display a success message
if (isset($_SESSION['verification_success']) && $_SESSION['verification_success'] == true) {
    $alert_message = "Your account has been successfully verified! You can now login.";
    $alert_class = "alert-success";
    unset($_SESSION['verification_success']); // Unset the session after showing the success message
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "login");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch user data based on username
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify if password matches
        if (password_verify($password, $row['password'])) {
            // Check if account is verified
            if ($row['is_verified']) {
                // Store user data in session after successful login
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['name'] = $row['name'];

                // Redirect user to the dashboard or a different page
                header('Location: dashboard.php');
                exit();
            } else {
                $alert_message = "Please verify your email address first.";
                $alert_class = "alert-warning";
            }
        } else {
            $alert_message = "Incorrect password.";
            $alert_class = "alert-danger";
        }
    } else {
        $alert_message = "Username not found.";
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
    <title>Login</title>
    <style>
        /* Global styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Form container styles */
        .login-container {
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
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            outline: none;
        }

        input[type="text"]:focus, input[type="password"]:focus {
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

<div class="login-container">
    <h2>Login</h2>
    
    <!-- Show alert if there is an error or success message -->
    <?php if (isset($alert_message)): ?>
        <div class="alert <?php echo $alert_class; ?>"><?php echo $alert_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button><br><br>

        <div class="create-account">
            <p>Don't have an account? <a href="register.php">Create one here</a></p>
        </div>
    </form>
</div>

</body>
</html>
