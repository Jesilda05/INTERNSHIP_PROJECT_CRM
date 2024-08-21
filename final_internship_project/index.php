<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Dashboard</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('assets/images/background.png'); /* Path to your image */
            background-size: cover; /* Ensures the image covers the entire screen */
            background-position: center; /* Centers the background image */
            background-attachment: fixed; /* Keeps the image fixed on scroll */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Dashboard Container */
        .dashboard {
            background-color: rgba(255, 255, 255, 0.9); /* Semi-transparent white background */
            border-radius: 12px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
            border: 5px solid black;
            text-align: center;
            padding: 40px;
            max-width: 450px;
            width: 100%;
        }

        h1 {
            font-size: 28px;
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: 600;
        }

        p {
            font-size: 18px;
            color: black;
            margin-bottom: 35px;
            line-height: 1.6;
        }

        /* Button Container */
        .buttons {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        /* Button Styles */
        .buttons a {
            display: inline-block;
            padding: 12px 24px;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            color: white;
            border-radius: 6px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .register-btn {
            background-color: #2980b9;
        }

        .login-btn {
            background-color: #2980b9;
        }

        .register-btn:hover {
            background-color: #218c53;
        }

        .login-btn:hover {
            background-color: #1f6391;
        }

        /* Responsive Design */
        @media (max-width: 500px) {
            .dashboard {
                padding: 20px;
            }

            h1 {
                font-size: 22px;
            }

            p {
                font-size: 16px;
            }

            .buttons {
                flex-direction: column;
                gap: 10px;
            }

            .buttons a {
                width: 100%;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Welcome to CRM</h1>
        <p>New here? Register now. Already a user? Please log in to continue managing your CRM account.</p>
        <div class="buttons">
            <a href="register.php" class="register-btn">Register</a>
            <a href="login.php" class="login-btn">Login</a>
        </div>
    </div>
</body>
</html>
