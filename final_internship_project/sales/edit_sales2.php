<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Check user authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'SalesManager') {
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

// User ID type casted to int
$sales_manager_id = (int)$_SESSION['user_id'];
$error = $success = '';

// Check if updating an existing sales record
if (isset($_GET['id'])) {
    if (filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
        $id = (int)$_GET['id'];

        // Corrected SQL query to select all columns
        $query = "SELECT * FROM sales WHERE id = ? AND sales_manager_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ii', $id, $sales_manager_id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $sales = $result->fetch_assoc();

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $amount = filter_var(trim($_POST['amount']), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                    if (empty($amount)) {
                        $error = "Amount is required.";
                    } elseif (!is_numeric($amount) || $amount <= 0) {
                        $error = "Invalid amount.";
                    } else {
                        $upd_sql = "UPDATE sales SET amount = ? WHERE id = ? AND sales_manager_id = ?";
                        $stmt = $conn->prepare($upd_sql);
                        $stmt->bind_param('dii', $amount, $id, $sales_manager_id);

                        if ($stmt->execute()) {
                            $success = 'Sales record updated successfully.';
                            logUserActivity($sales_manager_id, $_SESSION['role'], 'Updated sales');

                        } else {
                            $error = 'Error updating sales record: ' . $stmt->error;
                        }
                    }
                }
            } else {
                $error = 'Sales record not found.';
            }
            $stmt->close();
        } else {
            error_log("Error executing query: " . $stmt->error);
        }
    } else {
        $error = "Invalid ID.";
    }
}

// Handle creating a new sales record
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_GET['id'])) {
    $amount = filter_var(trim($_POST['amount']), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    if (empty($amount)) {
        $error = "Amount is required.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Invalid amount.";
    } else {
        $sql = "INSERT INTO sales (sales_manager_id, amount, created_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('id', $sales_manager_id, $amount);

        if ($stmt->execute()) {
            $success = 'Sales record created successfully.';
        } else {
            $error = 'Error creating sales record: ' . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<?php include('header2.php'); ?>
<div class="container">
<h3 class="form-heading">Edit Sales</h3>

<h3><?php echo isset($id) ? 'Update Sales Record' : 'Create Sales Record'; ?></h3>

<?php if (!empty($error)) : ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>


<form action="<?php echo isset($id) ? $_SERVER['PHP_SELF'] . '?id=' . $id : 'create_sales.php'; ?>" method="POST">
    <?php if (isset($id)) : ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
    <?php endif; ?>
    <input type="hidden" name="sales_manager_id" value="<?php echo htmlspecialchars($sales_manager_id); ?>">
    
    <label for="amount">Amount:</label>
    <input type="number" step="0.01" name="amount" id="amount" value="<?php echo isset($sales['amount']) ? htmlspecialchars($sales['amount']) : ''; ?>" required><br>
    
    <button type="submit"><?php echo isset($id) ? 'Update' : 'Submit'; ?></button>
</form>
    </div>
    <?php if (!empty($success)) : ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>

<style>
    .container {
        max-width: 600px; /* Limit container width */
        margin: 40px auto 20px auto; /* Move container up with top margin, center horizontally, add bottom margin */
        padding: 20px; /* Add padding around the container */
        text-align: center; /* Center-align text within the container */
    }

    .form-heading {
        margin-bottom: 20px; /* Space between heading and form */
        font-size: 24px;
        color: #007BFF; /* Color to match form border */
    }

    form {
        border: 2px solid #007BFF; /* Blue border for form */
        padding: 40px;
        border-radius: 8px;
        background-color: #f9f9f9;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
        box-sizing: border-box; /* Ensure padding is included in width calculation */
    }

    input[type="text"], input[type="email"] {
        width: calc(100% - 24px); /* Adjust width for padding and border */
        padding: 15px; /* Increased padding */
        margin-bottom: 20px; /* Increased margin */
        border: 1px solid #007BFF; /* Blue border for inputs */
        border-radius: 4px;
        box-sizing: border-box; /* Ensure padding is included in width calculation */
    }

    button {
        background-color: #007BFF; /* Blue button background */
        color: white;
        padding: 15px; /* Increased padding */
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 18px;
        width: 100%; /* Full-width button */
    }

    button:hover {
        background-color: #0056b3; /* Darker blue on hover */
    }

    .error {
        color: red;
        margin-bottom: 20px;
    }

    .success {
        color: green;
        margin-top: 20px; /* Space above success message */
    }
</style>


