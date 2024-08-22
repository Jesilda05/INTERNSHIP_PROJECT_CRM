<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Ensure only Admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}
function logUserActivity($userId, $role, $action) {
    global $conn;
    $sql = "INSERT INTO user_logs (user_id, role, action, timestamp) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Error preparing statement for logging user activity: " . $conn->error);
        return;
    }

    $stmt->bind_param('iss', $userId, $role, $action);
    $stmt->execute();

    if ($stmt->error) {
        error_log("Error executing statement for logging user activity: " . $stmt->error);
    } else {
        error_log("User activity logged successfully");
    }

    $stmt->close();
}
$err = $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    if (empty($name) || empty($email)) {
        $err = "Name and email are required.";
    } elseif (!preg_match('/^[a-zA-Z\s.]+$/', $name)) {
        $err = "Name can only contain letters, spaces, and basic punctuation.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = "Invalid email format.";
    } else {
        // Check if the email or name already exists
        $sql = "SELECT id FROM leadmanagers WHERE email = ? OR name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $email, $name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Name or email already exists.";
        } else {
            $sql = "INSERT INTO leadmanagers (name, email) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $name, $email);

            if ($stmt->execute()) {
                $success = "Lead Manager created successfully. Please set a password.";
                logUserActivity($_SESSION['user_id'], $_SESSION['role'], 'Create leadmanager');
                header("Location:manage_lead_manager.php");


            } else {
                $err = "Error creating Lead Manager: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
?>

<?php include('header2.php'); ?>


<div class="admin_container">
    <h3 class="form-heading">Create Lead Manager</h3>
    <?php if (!empty($err)): ?>
        <div class="error"><?php echo $err; ?></div>
    <?php endif; ?>




<form action="create_lead_manager.php" method="POST">
    Name: <input type="text" name="name" required><br>
    Email: <input type="email" name="email" required><br>
    <button type="submit">Create Lead Manager</button>
</form>
<?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
</div>


<style>
    body {
        margin: 0;
        padding: 0;
        font-family:  Tahoma, Geneva, sans-serif;
         
    }

    .admin_container {
        max-width: 600px;
        margin: 20px auto; 
        padding: 20px;
        text-align: center;
        background-color: #cc5e61;
        border-radius: 8px;
        border:4px solid black;

        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    h3 {
        margin-bottom: 20px;
        font-size: 30px;
        color: black;
    }

    form {
        border: 4px solid black;
        padding: 20px;
        border-radius: 8px;
        background-color: white;
    }

    input[type="text"], input[type="email"] {
        width: calc(100% - 24px);
        padding: 10px;
        margin-bottom: 15px;
        border: 2px solid black;
        border-radius: 4px;
    }

    select {
        width: calc(100% - 24px);
        padding: 10px;
        margin-bottom: 15px;
        border: 2px solid black;
        border-radius: 4px;
    }

    button {
        background-color:#cc5e61 ;
        color: black;
        padding: 10px;
        border: 2px solid black;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
    }

    button:hover {
        background-color: #e63c3c;
    }

    .error {
        color: black;
        margin-bottom: 20px;
    }

    .success {
        color: green;
        margin-top: 20px;
    }
</style>