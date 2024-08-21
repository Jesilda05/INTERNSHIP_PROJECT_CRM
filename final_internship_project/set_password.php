<?php
session_start();
include('mainconn/db_connect.php');

$err = $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = filter_var(trim($_POST['password']), FILTER_SANITIZE_STRING);

    if (empty($password)) {
        $err = 'Password is required.';
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Determine the table based on role
        $role = $_SESSION['temp_role'];
        $table = '';

        if ($role === 'Admin') {
            $table = 'admins';
        } elseif ($role === 'Customer') {
            $table = 'customers';
        } elseif ($role === 'SalesManager') {
            $table = 'salesmanagers';
        } elseif ($role === 'LeadManager') {
            $table = 'leadmanagers';
        }

        if (empty($table)) {
            $err = 'Invalid role.';
        } else {
            // Update the user with the hashed password
            $sql = "UPDATE $table SET password = ? WHERE email = ? AND name = ?";
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                $err = "Error preparing query: " . $conn->error;
            } else {
                $stmt->bind_param('sss', $hashed_password, $_SESSION['temp_email'], $_SESSION['temp_name']);
                if ($stmt->execute()) {
                    $success = 'Password set successfully! You can now log in.';
                    unset($_SESSION['temp_name'], $_SESSION['temp_email'], $_SESSION['temp_role']); // Clear session data
                    header('Location: login.php'); // Redirect to login
                    exit();
                } else {
                    $err = 'Error executing query: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
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
    <h2>SET PASSWORD</h2>

<?php if (!empty($err)): ?>
    <div class="error-message"><?php echo htmlspecialchars($err); ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form action="set_password.php" method="POST">
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required><br>

    <button type="submit">Set Password</button>
</form>
</body>
</html>
