<?php
session_start(); 
include('../mainconn/db_connect.php'); 
include('../mainconn/authentication.php'); 
//authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header('Location: ../login.php');
    exit();
}

$err = "";
$success = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //filter_var used for validating and sanitizing input

    $sub = filter_var(trim($_POST['subject']), FILTER_SANITIZE_STRING);
    $desc = filter_var(trim($_POST['description']), FILTER_SANITIZE_STRING);
    // Check if customer exists

    $cust_id = (int)$_SESSION['user_id'];
    // validating inputs

    if (empty($sub) || empty($desc)) {
        $err = "Please fill in all fields.";
    } elseif (!preg_match('/^[a-zA-Z0-9\s.,!?]+$/', $desc)) {
        $err = "Description can only contain letters, numbers, spaces, and basic punctuation.";
    } elseif (!preg_match('/^[a-zA-Z\s.,!?]+$/', $sub)) {
        $err = "Subject can only contain letters, spaces, and basic punctuation.";
    } else {
                // insert query

        $sql = "INSERT INTO tickets (customer_id, subject, description, created_at) VALUES (?, ?, ?, NOW())";
        $prestmt = $conn->prepare($sql);

        if ($prestmt) {
            $prestmt->bind_param('iss', $cust_id, $sub, $desc);

            if ($prestmt->execute()) {
                $success = 'Your ticket has been created successfully!';
            } else {
                error_log("Error occurred while creating ticket: " . $prestmt->error);
            }

            $prestmt->close();
        } else {
            error_log("The statement could not be prepared due to the following error: " . $conn->error);
        }
    }
}
?>

<?php include('header2.php'); ?>

<h3>Create Ticket</h3>

<?php if (!empty($err)): ?>
    <div class="error-message"><?php echo $err; ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="success-message"><?php echo $success; ?></div>
<?php endif; ?>

<form action="create_ticket2.php" method="POST">
    Subject:
    <input type="text" name="subject" id="subject" required><br>

    Description:
    <textarea name="description" id="description" required></textarea><br>

    <button type="submit">Submit Ticket</button>
</form>

<?php include('footer.php'); ?>
