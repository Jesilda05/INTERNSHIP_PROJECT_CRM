<?php
session_start();
include('mainconn/db_connect.php');

$err = $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = filter_var(trim($_POST['password']), FILTER_SANITIZE_STRING);

    if (empty($email) || empty($password)) {
        $err = 'Both fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid email format.";
    } else {
        $tables = [
            'admins' => 'Admin',
            'customers' => 'Customer',
            'salesmanagers' => 'SalesManager',
            'leadmanagers' => 'LeadManager'
        ];
        $userFound = false;

        foreach ($tables as $table => $role) {
            $sql = "SELECT id, name, email, password FROM $table WHERE email = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                error_log("Error preparing statement for table $table: " . $conn->error);
                $err = 'Error preparing the database query. Please try again.';
                break;
            }

            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $role;
                    $_SESSION['name'] = $user['name'];
                    
                    logUserActivity($_SESSION['user_id'],$_SESSION['role'], 'Login');

                    if ($role === 'Admin') {
                        header('Location: admin/dashboard2.php');
                    } elseif ($role === 'Customer') {
                        header('Location: customers/cust_dashboard2.php');
                    } elseif ($role === 'SalesManager') {
                        header('Location: sales/sales_dashboard2.php');
                    } elseif ($role === 'LeadManager') {
                        header('Location: leads/dashboard2.php');
                    }
                    exit();
                } else {
                    $err = 'Incorrect password.';
                }
                $userFound = true;
                break; 
            }
        }

        if (!$userFound) {
            $err = 'No account found with that email.';
        }
    }
}

function logUserActivity($userId, $role, $activity) {
    global $conn;
    $sql = "INSERT INTO user_logs (user_id, role, action, timestamp) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Error preparing statement for logging user activity: " . $conn->error);
        return;
    }

    $stmt->bind_param('iss', $userId, $role, $activity);
    $stmt->execute();

    if ($stmt->error) {
        error_log("Error executing statement for logging user activity: " . $stmt->error);
    } else {
        error_log("User activity logged successfully.");
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>LOGIN</h2>

    <?php if (!empty($err)): ?>
        <div class="error-message"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
<style>

body {
    font-family: Georgia, serif;
    margin: 20px;
    background-color: #cc5e61;
}
h1{
    text-align: center;
    font-size: 30px;
    margin-bottom: 20px;
    color:black;
}

h2 {
    text-align: center;
    font-size: 30px;
    margin-bottom: 50px;
    color:black;
}

form {
    display: flex;
    flex-direction: column;
    align-items: center;
    background-color: white;
    padding: 20px;
    border: 5px solid black; 

    border-radius: 5px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
}

label {
    font-weight: bold;
    margin-bottom: 5px;
}

input, select, button {
    padding: 10px;
    border: 2px solid black; 
    border-radius: 3px;
    margin-bottom: 10px;
}

button {
    background-color: #4CAF50;
    color: white;
    cursor: pointer;
}

.error-message {
    color: black;
    text-align: center;
    margin-bottom: 10px;
}
    </style>
