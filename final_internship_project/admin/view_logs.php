<?php
session_start();
include('../mainconn/db_connect.php');
include('../mainconn/authentication.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: login.php');
    exit();
}

$logs = [];
$error = '';

try {
   
    $sql = "SELECT ul.id, u.name AS user_name, ul.action, ul.timestamp
            FROM user_logs ul
            JOIN (
                SELECT id, name FROM admins
                UNION
                SELECT id, name FROM customers
                UNION
                SELECT id, name FROM salesmanagers
                UNION
                SELECT id, name FROM leadmanagers
            ) u ON ul.user_id = u.id
            ORDER BY ul.timestamp DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
} catch (Exception $e) {
    $error = 'Failed to retrieve logs: ' . $e->getMessage();
}
?>
<?php include('header2.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity Logs</title>
    <link rel="stylesheet" href="assets/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        .admin_container {
            max-width: 800px; 
            margin: 40px auto; 
            padding: 20px; 
            text-align: center; 
            border: 5px solid black; 
            border-radius: 8px; 
            background-color: #cc5e61; 
        }

        h3 {
            margin-bottom: 20px; 
            font-size: 24px;
            color: white; 
        }

        table {
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }

        th, td {
            border: 4px solid black; 
            padding: 10px; 
            text-align: left; 
            background-color: white; 
        }

        th {
            background-color: #e63c3c; 
            color: white; 
        }

        tr:nth-child(even) {
            background-color: #f16f6f; 
        }

        a.edit-link, a.delete-link {
            color: grey; 
            text-decoration: none; 
        }

        a.edit-link:hover, a.delete-link:hover {
            text-decoration: underline; 
        }

        a.delete-link {
            color: #d9534f; 
        }

        .error-message {
            color: #d9534f;
            font-size: 18px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="admin_container">
        <h3>User Activity Logs</h3>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($logs) > 0): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['id']); ?></td>
                            <td><?php echo htmlspecialchars($log['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                            <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No logs available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
