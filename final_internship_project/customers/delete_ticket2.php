<?php

session_start();
include("../mainconn/db_connect.php");
include("../mainconn/authentication.php");

// Check for user authentication
if (!isset($_SESSION["user_id"]) || $_SESSION['role'] !== 'Customer') {
    header("Location: ../login.php");
    exit();
}
//checking if the id exists


if (isset($_GET['id'])) {
    if (filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        $id = (int)$_GET['id'];
        $cust_id = (int)$_SESSION['user_id'];
              //delete query

        $sql = "DELETE FROM tickets WHERE id = ? AND customer_id = ?";
        $prestmt = $conn->prepare($sql);

        if ($prestmt) {
            $prestmt->bind_param('ii', $id, $cust_id);

            if ($prestmt->execute()) {
                echo 'Your ticket has been deleted successfully!';
            } else {
                error_log("Error deleting ticket: " . $prestmt->error);
                echo "Error deleting ticket. Please try again.";
            }

            $prestmt->close();
        } else {
            error_log("Error preparing statement: " . $conn->error);
            echo "Error preparing statement. Please try again.";
        }
    } else {
        echo "Invalid ID.";
    }
} else {
    echo "ID not set.";
}

// Redirect to manage_tickets page
header("Location: manage_tickets2.php");
exit();
?>
