<?php
session_start();
include('mainconn/db_connect.php');

$err = $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $role = filter_var(trim($_POST['role']), FILTER_SANITIZE_STRING);

    if (empty($name) || empty($email) || empty($role)) {
        $err = 'All fields are required.';
    } elseif (!preg_match('/^[a-zA-Z\s.,!?]+$/', $name)) {
        $err = "Name can only contain letters, spaces, and basic punctuation.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid email format.";
    } else {
        if ($role === 'Admin') {
            $sql = "SELECT id, password FROM admins WHERE email = ? AND name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $email, $name);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($admin_id, $password);
                $stmt->fetch();
                
                if (!empty($password)) {
                    header('Location: login.php');
                    exit();
                } else {
                    $_SESSION['temp_name'] = $name;
                    $_SESSION['temp_email'] = $email;
                    $_SESSION['temp_role'] = $role;
                    header('Location: set_password.php');
                    exit();
                }
            } else {
                $sql = "INSERT INTO admins (name, email) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss', $name, $email);

                if ($stmt->execute()) {
                    $_SESSION['temp_name'] = $name;
                    $_SESSION['temp_email'] = $email;
                    $_SESSION['temp_role'] = $role;
                    header('Location: set_password.php');
                    exit();
                } else {
                    $err = 'Failed to register Admin.';
                }
            }
        } else {
            $tables = [
                'Customer' => 'customers',
                'SalesManager' => 'salesmanagers',
                'LeadManager' => 'leadmanagers'
            ];

            if (!array_key_exists($role, $tables)) {
                $err = 'Invalid role.';
            } else {
                $table = $tables[$role];

                $sql = "SELECT id, password FROM $table WHERE email = ? AND name = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss', $email, $name);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($user_id, $password);
                    $stmt->fetch();

                    if (!empty($password)) {
                        header('Location: login.php');
                        exit();
                    } else {
                        $_SESSION['temp_name'] = $name;
                        $_SESSION['temp_email'] = $email;
                        $_SESSION['temp_role'] = $role;
                        header('Location: set_password.php');
                        exit();
                    }
                } else {
                    $err = 'Name and email do not match any existing records.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>REGISTER</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>REGISTER</h2>
<?php if (!empty($err)): ?>
    <div class="error-message"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>

<form action="register.php" method="POST">
    <label for="name">Name:</label>
    <input type="text" name="name" id="name" required><br>

    <label for="email">Email:</label>
    <input type="email" name="email" id="email" required><br>

    <label for="role">Role:</label>
    <select name="role" id="role" required>
        <option value="Customer">Customer</option>
        <option value="SalesManager">Sales Manager</option>
        <option value="LeadManager">Lead Manager</option>
        <option value="Admin">Admin</option>
    </select><br>

    <button type="submit">Register</button>
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
