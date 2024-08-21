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

// Ensure that the ID is valid and exists
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id = (int)$_GET['id'];

    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Delete dependent records from quotations
        $sql = "DELETE FROM quotations WHERE customer_id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Error preparing DELETE statement for quotations: " . $conn->error);
        }

        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing DELETE for quotations: " . $stmt->error);
        }
        $stmt->close();
        
        // Now delete the customer
        $sql = "DELETE FROM customers WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Error preparing DELETE statement for customers: " . $conn->error);
        }

        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) {
            throw new Exception("Error executing DELETE for customers: " . $stmt->error);
        }

        // Check if any rows were affected
        if ($stmt->affected_rows > 0) {
            $success = "Customer deleted successfully.";
            logUserActivity($_SESSION['user_id'], $_SESSION['role'], "Deleted customer ID: $id");
        } else {
            $error = "No customer found with ID $id.";
        }

        // Commit transaction
        $conn->commit();
    } catch (Exception $e) {
        // Rollback transaction if there is an error
        $conn->rollback();
        $error = "Error deleting customer: " . $e->getMessage();
        error_log($error);  // Log the specific error
    }

    $stmt->close();
} else {
    $error = "Invalid ID.";
}

// Redirect to the manage_customer.php page with a message
header('Location: manage_customer.php?' . http_build_query([
    'message' => isset($success) ? urlencode($success) : urlencode($error)
]));
exit();
?>
