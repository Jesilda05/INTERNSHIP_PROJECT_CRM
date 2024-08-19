<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Ensure only lead managers can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'LeadManager') {
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


$lead_manager_id = (int)$_SESSION['user_id'];
$error = $success = '';

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id = (int)$_GET['id'];

    // Fetch the lead data
    $sql = "SELECT * FROM leads WHERE id = ? AND lead_manager_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $id, $lead_manager_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $lead = $result->fetch_assoc();

        // Update the lead if POST request is made
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
            $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
            $phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
            $status = filter_var(trim($_POST['status']), FILTER_SANITIZE_STRING);

            if (empty($name) || empty($email) || empty($phone) || empty($status)) {
                $error = "Please fill in all fields.";
            } elseif (!preg_match('/^[a-zA-Z\s.,!?]+$/', $name)) {
                $error = "Name can only contain letters, spaces, and basic punctuation.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } elseif (!preg_match("/^\d{10}$/", $phone)) {
                $error = "Phone number must be 10 digits.";
            } else {
                $update_sql = "UPDATE leads SET name = ?, email = ?, phone = ?, status = ? WHERE id = ? AND lead_manager_id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param('sssiii', $name, $email, $phone, $status, $id, $lead_manager_id);

                if ($stmt->execute()) {
                    $success = "Lead updated successfully.";
                    logUserActivity($lead_manager_id, $_SESSION['role'], 'Updated lead');

                } else {
                    $error = "Error updating lead: " . $stmt->error;
                }
            }
        }
    } else {
        $error = "Lead not found.";
    }

    $stmt->close();
} else {
    header('Location: manage_leads2.php');
    exit();
}

$conn->close();
?>

<!-- Include header -->
<?php include('header2.php'); ?>
<div class="container">
<h3 class="form-heading">Edit Lead</h3>

<!-- Display success/error messages -->
<?php if (!empty($error)): ?>
    <div class="error"><?php echo $error; ?></div>
<?php endif; ?>


<!-- Edit form -->
<form action="edit_lead2.php?id=<?php echo $id; ?>" method="POST">
    Name: <input type="text" name="name" value="<?php echo htmlspecialchars($lead['name']); ?>" required><br>
    Email: <input type="email" name="email" value="<?php echo htmlspecialchars($lead['email']); ?>" required><br>
    Phone: <input type="text" name="phone" value="<?php echo htmlspecialchars($lead['phone']); ?>" required><br>
    Status:
    <select name="status" id="status" required>
        <option value="">Select a status</option>
        <option value="new" <?php echo ($lead['status'] === 'new') ? 'selected' : ''; ?>>NEW</option>
        <option value="in_progress" <?php echo ($lead['status'] === 'in_progress') ? 'selected' : ''; ?>>IN_PROGRESS</option>
        <option value="closed" <?php echo ($lead['status'] === 'closed') ? 'selected' : ''; ?>>CLOSED</option>
    </select><br>
    <button type="submit">Update Lead</button>
</form>
</div>
<?php if (!empty($success)): ?>
    <div class="success"><?php echo $success; ?></div>
<?php endif; ?>
<?php include('footer.php'); ?>

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

