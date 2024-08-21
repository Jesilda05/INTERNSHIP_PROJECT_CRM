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
if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id = (int)$_GET['id'];

    $sql = "DELETE FROM leadmanagers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo "Lead Manager deleted successfully.";
        logUserActivity($_SESSION['user_id'], $_SESSION['role'], "Deleted lead manager ");

    } else {
        echo "Error deleting Lead Manager: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Invalid ID.";
}

header('Location: manage_lead_manager.php');
exit();
?>
