<?php
session_start();
$loginError = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if username and password are set
    if (isset($_POST['username']) && isset($_POST['password'])) {
        include 'connection.php';

        // Sanitize input
        $inputUsername = mysqli_real_escape_string($conn, $_POST['username']);
        $inputPassword = mysqli_real_escape_string($conn, $_POST['password']);

        // SQL query to select matching username
        $sql = "SELECT username, password FROM admin_users WHERE username='$inputUsername'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            // Fetch the user data
            $row = $result->fetch_assoc();
            $hashedPassword = $row['password'];

            // Verify the password
            if (password_verify($inputPassword, $hashedPassword)) {
                // Successful login
                $_SESSION['username'] = $inputUsername;
                header("Location: admin.php");
                exit();
            } else {
                // Invalid credentials
                $loginError = "Invalid username or password.";
            }
        } else {
            // User does not exist
            $loginError = "Invalid username or password.";
        }

        $conn->close();
    } else {
        $loginError = "Please enter both username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banker Login</title>
    <style>
        body {
            font-family: "Lucida Bright";
            background-color: #f7ebeb;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background-color: #fff;
            padding: 20px 50px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(231, 7, 7, 0.1);
            width: 20%;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: xx-large;
            color: #333;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }

        .input-group input {
            width: 90%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background-color: #0bcf7a;
            border: none;
            color: white;
            font-size: 18px;
            font-family: "Lucida Bright";
            border-radius: 4px;
            cursor: pointer;
            margin-top: 15px;
        }

        .login-btn:hover {
            background-color: #10e0dc;
            transform: scale(1.05);
            color: rgb(9, 18, 1);
        }

        .error-message {
            color: red;
            text-align: center;
            margin-top: 15px;
        }

        .redirect-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #0bcf7a;
            text-decoration: none;
        }

        .redirect-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Banker Login</h2>
        <form action="" method="POST">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>
        <?php if ($loginError): ?>
            <div class="error-message"><?php echo $loginError; ?></div>
        <?php endif; ?>
        
        <!-- Link to redirect to index.php -->
        <a href="index.php" class="redirect-link">Go to Home</a>
    </div>

</body>
</html>

