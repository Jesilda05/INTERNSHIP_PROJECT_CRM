<?php
include('../mainconn/db_connect.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sidebar</title>
</head>
<body>
    <header>
        <div class="sidebar">
            <h1>Admin Panel</h1>
            <nav>
                <ul>
                    <li><a href="dashboard2.php">Dashboard</a></li>
                    <li><a href="create_customer.php">Create Customer</a></li>
                    <li><a href="manage_customer.php">Manage Customers</a></li>
                    <li><a href="create_sales_manager.php">Create Sales Manager</a></li>
                    <li><a href="manage_sales_manager.php">Manage Sales Managers</a></li>
                    <li><a href="create_lead_manager.php">Create Lead Manager</a></li>
                    <li><a href="manage_lead_manager.php">Manage Lead Managers</a></li>
                    <li><a href="view_feedback.php">View Feedback</a></li>
                    <li><a href="view_quotations_admin.php">View Quotations</a></li>
                    <li><a href="view_tickets_admin.php">View Tickets</a></li>
                    <li><a href="view_sales.php">View Sales</a></li>

                    <li><a href="view_leads.php">View Leads</a></li>

                    <li><a href="view_logs.php">View Logs</a></li>
                    <li><a href="change_password.php">Change password</a></li>

                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        <div class="main-content">
        </div>
    </header>
</body>
</html>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    background-color: white;
    color: #100f0f;
    display: flex;
}

.sidebar {
    width: 250px;
    background-color: #000000;
    padding: 1rem;
    box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
    border-right: 2px solid #003366; }
.sidebar h1 {
    margin: 0;
    font-size: 1.5rem;
    color:white;
}

nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

nav li {
    margin: 0.5rem 0;
}

nav a {
    display: block;
    color:white ; 
    background-color: rgb(124, 106, 106); 
    text-decoration: none;
    font-weight: 500;
    font-size: 1rem;
    padding: 0.75rem 1rem;
    border-radius: 4px;
    border: 2.5px solid black; /
    transition: background-color 0.3s ease, border-color 0.3s ease;
}

nav a:hover {
    background-color: #cc5e61; 
    border-color: #002244; 
}

.main-content {
    flex: 1;
    padding: 2rem;
    background-color: #ffffff;
    box-shadow: 0 4px 8px rgba(225, 146, 209, 0.1);
    overflow-y: auto;
}

.dashboard {
    max-width: 100%;
    margin: 0;
    padding: 2rem;
    background-color: #ffffff;
}

.dashboard h1 {
    margin-top: 0;
    font-size: 2rem;
    color: #0b0b0b;
    border-bottom: 2px solid #c36262; 
    padding-bottom: 0.5rem;
}

.dashboard-links {
    margin-bottom: 2rem;
}

.dashboard-links a {
    display: inline-block;
    margin: 0.5rem 1rem;
    padding: 0.75rem 1.25rem;
    background-color: #cc5e61;
    color: #101112;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 500;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.dashboard-links a:hover {
    background-color: grey;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.dashboard-stats {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.dashboard-stat-card {
    background-color: #cc5e61; 
    padding: 1.5rem;
    border-radius: 8px;
    border: 2px solid #101112;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    flex: 1 1 calc(50% - 1rem); 
    min-width: 250px; 
}

.dashboard-stat-card h3 {
    margin-top: 0;
    font-size: 1.25rem;
    color: black;
}

.dashboard-stat-card p {
    margin: 0.5rem 0;
    font-size: 1.1rem;
    color: #333;
}

    </style>