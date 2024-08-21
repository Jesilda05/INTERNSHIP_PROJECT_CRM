<?php
session_start(); 
include('../mainconn/db_connect.php'); 
include('../mainconn/authentication.php'); 

// Authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header('Location: ../login.php');
    exit();
}

$err = "";
$success = "";

// Function to log user activity
function logUserActivity($userId, $role, $action) {
    global $conn;

    $sql = "INSERT INTO user_logs (user_id, role, action, timestamp) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    // Check if the statement was successfully prepared
    if (!$stmt) {
        error_log("Error preparing statement for logging user activity: " . $conn->error);
        return false; // Return false to indicate failure
    }

    $stmt->bind_param('iss', $userId, $role, $action);
    $stmt->execute();

    // Check if the statement executed successfully
    if ($stmt->error) {
        error_log("Error executing statement for logging user activity: " . $stmt->error);
        return false; // Return false to indicate failure
    }

    $stmt->close();
    return true; // Return true to indicate success
}

// Handle form submission
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

    // Validating inputs
    if (empty($product) || empty($details)) {
        $err = 'PLEASE FILL IN ALL FIELDS';
    } elseif (!preg_match('/^[a-zA-Z0-9\s.,!?]+$/', $details)) {
        $err = "Details can only contain letters, numbers, spaces, and basic punctuation.";
    } elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', $product)) {
        $err = "Product can only contain letters, numbers, and spaces.";
    } else {
        // Insert quotation into the database
        $sql = "INSERT INTO quotations (customer_id, product, details, created_at) VALUES (?, ?, ?, NOW())";
        $prestmt = $conn->prepare($sql);
        $prestmt->bind_param('iss', $cust_id, $product, $details);

        if ($prestmt->execute()) {
            $success = 'Your Quotation has been created successfully!';
            header("Location:manage_quotations2.php");


            // Attempt to log user activity
            if (!logUserActivity($cust_id, $_SESSION['role'], 'Create Quotation')) {
                error_log("Failed to log the user activity for quotation creation.");

            }

        } else {
            $err = "Error: " . $prestmt->error;
            error_log("Error inserting quotation: " . $prestmt->error);
        }

        $prestmt->close();
    }
}
?>

<?php include('header2.php'); ?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
    <div class="cust_container">
        <?php if (!empty($err)): ?>
            <div class="error-message"><?php echo $err; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="create_quotation2.php" method="POST">
            <label for="product"><strong>Product:</strong></label>
            <input type="text" name="product" id="product" required><br>
            
            <label for="details"><strong>Details:</strong></label>
            <textarea name="details" id="details" required></textarea><br>

            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>


<style>
   body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;

    }
    .cust_container {
        max-width: 900px; /* Increased width */
        margin: 40px auto; /* Adjusted margins */
        padding: 40px; /* Increased padding */
        text-align: center;
        background-color: #cc5e61;
        border:4px solid black;

        border-radius: 12px; /* Increased border radius */
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15); /* Increased box shadow */
    }

    form {
        border: 4px solid black; /* Increased border thickness */
        padding: 30px; /* Increased padding */
        border-radius: 12px;
        background-color: white;
    }

    input[type="text"], input[type="email"] {
        width: calc(100% - 48px); /* Increased width */
        padding: 15px; /* Increased padding */
        margin-bottom: 20px;
        border: 2px solid black; /* Increased border */
        border-radius: 6px;
    }

    textarea {
        width: calc(100% - 48px); /* Adjusted to match inputs */
        padding: 15px; /* Increased padding */
        margin-bottom: 20px;
        border: 2px solid black; /* Increased border */
        border-radius: 6px;
        height: 150px; /* Increased height */
    }

    button {
        background-color: #cc5e61;
        color: black;
        padding: 15px; /* Increased padding */
        border: 2px solid black;
        border-radius: 6px;
        cursor: pointer;
        font-size: 18px; /* Increased font size */
        width: 100%;
    }

    button:hover {
        background-color: #e63c3c;
    }

    .error {
        color: black;
        margin-bottom: 20px;
    }

    .success {
        color: green;
        margin-top: 20px;
    }
</style>
