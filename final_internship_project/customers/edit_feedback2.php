<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Check user authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
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
    }

    $stmt->close();
}

$cust_id = (int)$_SESSION['user_id'];
$error = $success = '';

// Check if updating an existing feedback
if (isset($_GET['id'])) {
    if (filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        $id = (int)$_GET['id'];

        $query = "SELECT * FROM feedback WHERE id = ? AND customer_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $id, $cust_id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $feedback = $result->fetch_assoc();

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $text = filter_var(trim($_POST['feedback']), FILTER_SANITIZE_STRING);

                    if (empty($text)) {
                        $error = "Feedback cannot be empty.";
                    } elseif (!preg_match('/^[a-zA-Z\s.,!?]+$/', $text)) {
                        $error = "Feedback can only contain letters, spaces, and basic punctuation.";
                    } else {
                        $upd_sql = "UPDATE feedback SET feedback = ? WHERE id = ? AND customer_id = ?";
                        $stmt = $conn->prepare($upd_sql);
                        $stmt->bind_param('sii', $text, $id, $cust_id);

                        if ($stmt->execute()) {
                            $success = 'Feedback updated successfully.';
                            logUserActivity($cust_id, $_SESSION['role'], 'Updated Feedback');

                        } else {
                            $error = 'Error updating feedback: ' . $stmt->error;
                        }
                    }
                }
            } else {
                $error = 'Feedback not found.';
            }
            $stmt->close();
        } else {
            error_log("Error executing query: " . $stmt->error);
        }
    } else {
        $error = "Invalid ID.";
    }
}

// Handle creating a new feedback
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_GET['id'])) {
    $text = filter_var(trim($_POST['feedback']), FILTER_SANITIZE_STRING);

    if (empty($text)) {
        $error = "Feedback cannot be empty.";
    } elseif (!preg_match('/^[a-zA-Z\s.,!?]+$/', $text)) {
        $error = "Feedback can only contain letters, spaces, and basic punctuation.";
    } else {
        $sql = "INSERT INTO feedback (customer_id, feedback, created_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $cust_id, $text);

        if ($stmt->execute()) {
            $success = 'Feedback created successfully.';
        } else {
            $error = 'Error creating feedback: ' . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<?php include('header2.php'); ?>

<h3><?php echo isset($id) ? 'Update Feedback' : 'Create Feedback'; ?></h3>

<?php if (!empty($error)) : ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>
<?php if (!empty($success)) : ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<form action="<?php echo isset($id) ? $_SERVER['PHP_SELF'] . '?id=' . $id : $_SERVER['PHP_SELF']; ?>" method="POST">
    <?php if (isset($id)) : ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
    <?php endif; ?>
    <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($cust_id); ?>">
    
    <label for="feedback">Feedback:</label>
    <textarea name="feedback" id="feedback" required><?php echo isset($feedback['feedback']) ? htmlspecialchars($feedback['feedback']) : ''; ?></textarea>
    
    <button type="submit"><?php echo isset($id) ? 'Update' : 'Submit'; ?></button>
</form>

<?php include('footer.php'); ?>
