<?php
include('../mainconn/db_connect.php');


// Function to check if a user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Function to ensure user is authenticated and has the correct role
function checkRole($role) {
    if (!isLoggedIn()) {
        header('Location: ../login.php');
        exit();
    }

    if ($_SESSION['role'] !== $role) {
        // Redirect to a different page or display an error message
        header('Location: ../unauthorized.php');
        exit();
    }
}

// Function to log out a user
function logout() {
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit();
}

// Function to validate login credentials
function validateLogin($email, $password) {
    global $conn;
    $tables = ['admins', 'customers', 'sales_managers', 'lead_managers'];
    $roles = ['Admin', 'Customer', 'SalesManager', 'LeadManager'];

    foreach ($tables as $index => $table) {
        $sql = "SELECT id, email, password FROM $table WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $roles[$index];
                return true;
            }
        }
    }
    return false;
}

// Function to register a new user
function registerUser($name, $email, $password, $role) {
    global $conn;
    $password_hashed = password_hash($password, PASSWORD_BCRYPT);

    switch ($role) {
        case 'Admin':
            $sql = "INSERT INTO admins (name, email, password) VALUES (?, ?, ?)";
            break;
        case 'Customer':
            $sql = "INSERT INTO customers (name, email, password) VALUES (?, ?, ?)";
            break;
        case 'SalesManager':
            $sql = "INSERT INTO sales_managers (name, email, password) VALUES (?, ?, ?)";
            break;
        case 'LeadManager':
            $sql = "INSERT INTO lead_managers (name, email, password) VALUES (?, ?, ?)";
            break;
        default:
            return false;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $name, $email, $password_hashed);
    return $stmt->execute();
}
?>
