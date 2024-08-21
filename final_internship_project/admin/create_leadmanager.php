<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

// Ensure only Admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if the email or name already exists
        $sql = "SELECT id FROM lead_managers WHERE email = ? OR name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $email, $name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Name or email already exists.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO lead_managers (name, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sss', $name, $email, $password_hash);

            if ($stmt->execute()) {
                $success = "Lead Manager created successfully.";
            } else {
                $error = "Error creating Lead Manager: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
?>

<?php include('header2.php'); ?>


<div class="container">
    <h3 class="form-heading">Create Leads Manager</h3>
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

<form action="create_lead_manager.php" method="POST">
    Name: <input type="text" name="name" required><br>
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Create Lead Manager</button>
</form>

<?php if (!empty($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
</div>


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


