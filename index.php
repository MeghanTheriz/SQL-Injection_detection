<?php
session_start();
require "db_connection.php";

function detect_sql_injection($input) {
    $blacklist = ["'", "\"", ";", "--", "#", "/*", "*/", "UNION", "SELECT", "INSERT", "DELETE", "UPDATE", "DROP", " OR ", " AND "];

    foreach ($blacklist as $word) {
        if (stripos($input, $word) !== false) {
            return true; 
        }
    }
    return false; 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (detect_sql_injection($username) || detect_sql_injection($password)) {
        $error = "<p class='warning'>⚠️ Warning: SQL Injection attempt detected!</p>";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashed_password);
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                $_SESSION["user"] = $username;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "<p class='error'>Invalid username or password.</p>";
            }
        } else {
            $error = "<p class='error'>Invalid username or password.</p>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(to right, #3a1c71, #d76d77, #ffaf7b);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 350px;
            text-align: center;
        }

        h2 {
            margin-bottom: 1rem;
            color: #333;
        }

        .input-box {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #3a1c71;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
        }

        .btn:hover {
            background: #d76d77;
        }

        .error, .warning {
            background: #ff4c4c;
            color: white;
            padding: 10px;
            margin: 10px 0;
            border-radius: 6px;
        }

        p {
            margin-top: 12px;
            font-size: 14px;
        }

        a {
            color: #3a1c71;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error)) echo $error; ?>
        <form method="POST">
            <input type="text" class="input-box" name="username" placeholder="Username" required><br>
            <input type="password" class="input-box" name="password" placeholder="Password" required><br>
            <button type="submit" class="btn">Login</button>
        </form>
        <p>No account? <a href="register.php">Register</a></p>
    </div>
</body>
</html>
