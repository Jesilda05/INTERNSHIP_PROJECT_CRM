<?php
include('../mainconn/db_connect.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Manager Dashboard</title>
    <style>
      
.lead_sidebar {
    width: 250px;
    background-color: #000000; 
    padding: 1.5rem;
    box-shadow: 2px 0 4px rgba(112, 156, 232, 0.1); 
    overflow-y: auto;
    border-right: 2px solid #003366; 
    position: fixed; 
    top: 0;
    left: 0;
    bottom: 0; 
}

.lead_sidebar h1 {
    margin: 0;
    font-size: 1.5rem;
    color: white; 
}

nav ul {
    list-style: circle;
    padding: 0;
    margin: 0;
}

nav li {
    margin: 0.5rem 0;
}

nav a {
    display: block;
    color: white; 
    background-color: rgb(124, 106, 106); 
    text-decoration: none;
    font-weight: 500;
    font-size: 1.5rem;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    border: 2.5px solid #0a0f14; 
    transition: background-color 0.3s ease, border-color 0.3s ease;
}

nav a:hover {
    background-color: #cc5e61; 
}

main {
    background-color: white; 
    margin-left: 250px; 
    padding: 2rem;
    box-shadow: 0 4px 8px rgba(225, 146, 209, 0.1); 
    flex: 1;
    overflow-y: auto;
    min-height: 100vh;
}  </style>
</head>
<body>
    <header>
        <div class="lead_sidebar">
            <h1>Lead Manager Dashboard</h1>
            <nav>
                <ul>
                    <li><a href="dashboard2.php">Dashboard</a></li>
                    <li><a href="create_lead2.php">Create Lead</a></li>
                    <li><a href="manage_leads2.php">Manage Leads</a></li>
                    <li><a href="change_password.php">Change password</a></li>

                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>
    <main>
