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
    $product = filter_var(trim($_POST['product']), FILTER_SANITIZE_STRING);
    $details = filter_var(trim($_POST['details']), FILTER_SANITIZE_STRING);
    $cust_id = (int)$_SESSION['user_id'];

    // Check if customer exists
    $query = "SELECT id FROM customers WHERE id = ?";
    $pre_stmt = $conn->prepare($query);
    $pre_stmt->bind_param('i', $cust_id);
    $pre_stmt->execute();
    $pre_stmt->store_result();

    if ($pre_stmt->num_rows === 0) {
        die('Error: Customer ID does not exist.');
    }
    $pre_stmt->close();

    // validating inputs
    if (empty($product) || empty($details)) {
        $err = 'PLEASE FILL IN ALL FIELDS';
    } elseif (!preg_match('/^[a-zA-Z0-9\s.,!?]+$/', $details)) {
        $err = "Details can only contain letters, numbers, spaces, and basic punctuation.";
    } elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', $product)) {
        $err = "Product can only contain letters, numbers, and spaces.";
    } else {
        // insert query
        $sql = "INSERT INTO quotations (customer_id, product, details, created_at) VALUES (?, ?, ?, NOW())";
        $prestmt = $conn->prepare($sql);
        $prestmt->bind_param('iss', $cust_id, $product, $details);

        if ($prestmt->execute()) {
            $success = 'Your Quotation has been created successfully!';
        } else {
            echo "Error: " . $prestmt->error;
            error_log("The following error occurred while inserting the quotation: " . $prestmt->error);
        }

        $prestmt->close();
    }
}
?>
<?php include('header2.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../assets/css/cust_style.css">
</head>
<body>
    <h3>Create Quotation</h3>
    <div class="form-container">
        <?php if (!empty($err)): ?>
            <div class="error-message"><?php echo $err; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="create_quotation2.php" method="POST">
            <label for="product"><strong>PRODUCT:</strong></label>
            <input type="text" name="product" id="product" required><br>
            
            <label for="details"><strong>Details:</strong></label>
            <textarea name="details" id="details" required></textarea><br>

            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
<?php include('footer.php'); ?>
