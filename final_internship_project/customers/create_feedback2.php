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

 //filter_var used for validating and sanitizing input

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $feed = filter_var(trim($_POST['feedback']), FILTER_SANITIZE_STRING);
    $cust_id = (int)$_SESSION['user_id'];
    // validating inputs

    if (empty($feed)) {
        $err = 'PLEASE FILL IN ALL FIELDS';
    } elseif (!preg_match('/^[a-zA-Z\s.,!?]+$/', $feed)) {
        $err = "Feedback can only contain letters, spaces, and basic punctuation.";
    } else {
        // validating inputs

        $sql = "INSERT INTO feedback (customer_id, feedback, created_at) VALUES (?, ?, NOW())";
        $prestmt = $conn->prepare($sql);
        $prestmt->bind_param('is', $cust_id, $feed);

        if ($prestmt->execute()) {
            $success = 'Your Feedback has been submitted successfully!';
        } else {
            error_log("The following error occurred while submitting feedback: " . $prestmt->error);
        }

        $prestmt->close();
    }
}
?>
<?php include('header2.php'); ?>

<h2>Create Feedback</h2>

<?php if (!empty($err)): ?>
    <div class="error-message"><?php echo $err; ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="success-message"><?php echo $success; ?></div>
<?php endif; ?>

<form action="create_feedback2.php" method="POST">
    <strong>Feedback:</strong>
    <textarea name="feedback" id="feedback" required></textarea><br>

    <button type="submit">Submit Feedback</button>
</form>

<?php include('footer.php'); ?>
