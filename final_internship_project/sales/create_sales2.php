<?php
session_start(); 
include('../mainconn/db_connect.php'); 
include('../mainconn/authentication.php'); 

// Check if the user is a Sales Manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'SalesManager') {
    header('Location: ../login.php');
    exit();
}

$err = "";
$success = "";
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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $quotation_id = filter_var(trim($_POST['quotation_id']), FILTER_SANITIZE_NUMBER_INT);
    $amount = filter_var(trim($_POST['amount']), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    $sales_manager_id = (int)$_SESSION['user_id'];

    // Validation
    if (empty($quotation_id) || empty($amount)) {
        $err = "Please fill in all fields.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $err = "Amount must be a positive number.";
    } else {
        $sql = "INSERT INTO sales (quotation_id, amount, sales_manager_id, created_at) VALUES (?, ?, ?, NOW())";
        $prestmt = $conn->prepare($sql);

        if ($prestmt) {
            $prestmt->bind_param('idi', $quotation_id, $amount, $sales_manager_id);

            if ($prestmt->execute()) {
                $success = 'Sales record has been created successfully!';
                logUserActivity($sales_manager_id, $_SESSION['role'], 'Create Sales'); 

            } else {
                error_log("Error occurred while creating sales record: " . $prestmt->error);
            }

            $prestmt->close();
        } else {
            error_log("The statement could not be prepared due to the following error: " . $conn->error);
        }
    }
}
?>

<?php include('header2.php'); ?>

<div class="container">
    <h3 class="form-heading">Create Sales</h3>

    <?php if (!empty($err)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form action="create_sales2.php" method="POST" class="form-sales">
        <div class="form-group">
            <label for="quotation_id">Quotation ID:</label>
            <input type="number" name="quotation_id" id="quotation_id" required class="form-control">
        </div>

        <div class="form-group">
            <label for="amount">Amount:</label>
            <input type="number" step="0.01" name="amount" id="amount" required class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Submit Sales Record</button>
    </form>
</div>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f9;
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 600px;
        margin: 50px auto;
        padding: 30px;
        background-color: lightskyblue;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    h3.form-heading {
        font-size: 28px;
        color: #343a40;
        margin-bottom: 30px;
        text-align: center;
        font-weight: 600;
    }

    .form-group {
        margin-bottom: 20px;
        text-align: left;
    }

    label {
        font-size: 16px;
        font-weight: 500;
        color: #343a40;
    }

    .form-control {
        width: 100%;
        padding: 12px;
        margin-top: 8px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .btn {
        background-color: #007BFF;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
    }

    .btn:hover {
        background-color: #0056b3;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }
</style>
