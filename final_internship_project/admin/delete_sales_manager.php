<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Ensure only Admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

// Function to log user activity
function logUserActivity($userId, $role, $action) {
    global $conn;

    error_log("Inside logUserActivity for user_id: $userId, role: $role, action: $action");

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
        error_log("Log entry created successfully for action: $action");
    }

    $stmt->close();
}

// Check if a valid ID is provided
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id = (int)$_GET['id'];

    // Prepare the DELETE statement
    $sql = "DELETE FROM salesmanagers WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('i', $id);

        // Execute the deletion
        if ($stmt->execute()) {
            // Log the deletion activity
            logUserActivity($_SESSION['user_id'], $_SESSION['role'], "Deleted Sales Manager with ID: $id");

            // Redirect with success message
            $_SESSION['message'] = "Sales Manager deleted successfully.";
        } else {
            // Handle execution error
            $_SESSION['message'] = "Error deleting Sales Manager: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        // Handle preparation error
        $_SESSION['message'] = "Error preparing the DELETE statement: " . $conn->error;
    }
} else {
    // Invalid ID error
    $_SESSION['message'] = "Invalid Sales Manager ID.";
}

// Redirect to the manage sales manager page
header('Location: manage_sales_manager.php');
exit();
?>
