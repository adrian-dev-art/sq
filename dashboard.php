<?php
// Start the session to check if the user is logged in
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header('Location: login.php');
    exit();
}

// Connect to the database to check if the user is verified
$conn = new mysqli("localhost", "root", "", "login");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user data based on the session user ID
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = '$user_id' AND is_verified = TRUE";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // If the user is not verified, redirect them to the verification page
    header('Location: login.php');
    exit();
}

// Fetch the user information
$row = $result->fetch_assoc();
$username = $row['username'];
$email = $row['email'];
$name = $row['name'];
$address = $row['address'];
$phone = $row['phone']; // Assuming phone is stored in the database

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        .profile-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .profile-card h2 {
            text-align: center;
            color: #333;
        }
        .profile-card table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .profile-card table, th, td {
            border: 1px solid #ddd;
        }
        .profile-card th, td {
            padding: 12px;
            text-align: left;
        }
        .profile-card th {
            background-color: #f4f7fc;
        }
        .profile-card td {
            background-color: #fff;
        }
        .logout-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #f44336;
            color: white;
            text-align: center;
            border: none;
            border-radius: 4px;
            margin-top: 20px;
            cursor: pointer;
        }
        .logout-btn:hover {
            background-color: #e53935;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="profile-card">
        <h2>User Profile</h2>

        <table>
            <tr>
                <th>Name</th>
                <td><?php echo $name; ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo $email; ?></td>
            </tr>
            <tr>
                <th>Username</th>
                <td><?php echo $username; ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?php echo $phone ? $phone : 'Not Available'; ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?php echo $address; ?></td>
            </tr>
        </table>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

</body>
</html>
