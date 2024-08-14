<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Check user authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header('Location: ../login.php');
    exit();
}

// User ID type casted to integer
$cust_id = (int)$_SESSION['user_id'];
$error = $success = '';

// Check if id exists
if (isset($_GET['id'])) {
    if (filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        $id = (int)$_GET['id'];

        // select query
        $query = "SELECT * FROM tickets WHERE id = ? AND customer_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $id, $cust_id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $ticket = $result->fetch_assoc();

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $sub = filter_var(trim($_POST['subject']), FILTER_SANITIZE_STRING);
                    $desc = filter_var(trim($_POST['description']), FILTER_SANITIZE_STRING);

                    if (empty($sub) || empty($desc)) {
                        $error = "All fields are required.";
                    } elseif (!preg_match('/^[a-zA-Z\s.,!?]+$/', $sub)) {
                        $error = "Subject can only contain letters, spaces, and basic punctuation.";
                    } elseif (!preg_match('/^[a-zA-Z0-9\s.,!?]+$/', $desc)) {
                        $error = "Description can only contain letters, numbers, spaces, and basic punctuation.";
                    } else {
                        $upd_sql = "UPDATE tickets SET subject = ?, description = ? WHERE id = ? AND customer_id = ?";
                        $stmt = $conn->prepare($upd_sql);
                        $stmt->bind_param('ssii', $sub, $desc, $id, $cust_id);

                        if ($stmt->execute()) {
                            $success = 'Ticket updated successfully.';
                        } else {
                            $error = 'Error updating ticket: ' . $stmt->error;
                        }
                    }
                }
            } else {
                $error = 'Ticket not found.';
            }
            $stmt->close();
        } else {
            error_log("Error executing query: " . $stmt->error);
        }
    } else {
        $error = "Invalid ID.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_GET['id'])) {
    $sub = filter_var(trim($_POST['subject']), FILTER_SANITIZE_STRING);
    $desc = filter_var(trim($_POST['description']), FILTER_SANITIZE_STRING);

    if (empty($sub) || empty($desc)) {
        $error = "All fields are required.";
    } elseif (!preg_match('/^[a-zA-Z\s.,!?]+$/', $sub)) {
        $error = "Subject can only contain letters, spaces, and basic punctuation.";
    } elseif (!preg_match('/^[a-zA-Z0-9\s.,!?]+$/', $desc)) {
        $error = "Description can only contain letters, numbers, spaces, and basic punctuation.";
    } else {
        $sql = "INSERT INTO tickets (customer_id, subject, description, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iss', $cust_id, $sub, $desc);

        if ($stmt->execute()) {
            $success = 'Ticket created successfully.';
        } else {
            $error = 'Error creating ticket: ' . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<?php include('header2.php'); ?>

<h3><?php echo isset($id) ? 'Update Ticket' : 'Create Ticket'; ?></h3>

<?php if (!empty($error)) : ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>
<?php if (!empty($success)) : ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<form action="<?php echo isset($id) ? $_SERVER['PHP_SELF'] . '?id=' . $id : 'create_ticket2.php'; ?>" method="POST">
    <?php if (isset($id)) : ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
    <?php endif; ?>
    <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($cust_id); ?>">
    
    <label for="subject">Subject:</label>
    <input type="text" name="subject" id="subject" value="<?php echo isset($ticket['subject']) ? htmlspecialchars($ticket['subject']) : ''; ?>" required><br>

    <label for="description">Description:</label>
    <textarea name="description" id="description" required><?php echo isset($ticket['description']) ? htmlspecialchars($ticket['description']) : ''; ?></textarea><br>
    
    <button type="submit"><?php echo isset($id) ? 'Update' : 'Submit'; ?></button>
</form>

<?php include('footer.php'); ?>
