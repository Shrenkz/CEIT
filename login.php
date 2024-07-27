<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #e8f5e9; /* Light green background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background-color: #ffffff; /* White background for the form */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            border: 2px solid #4caf50; /* Green border */
        }

        h2 {
            color: #4caf50; /* Green color for the header */
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            color: #4caf50; /* Green color for labels */
            margin-bottom: 5px;
            text-align: left;
        }

        input[type="email"],
        input[type="password"] {
            width: calc(100% - 22px); /* Adjust width to fit within the container */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #4caf50; /* Green border */
            border-radius: 5px;
            font-size: 14px;
            color: #333; /* Dark text for better readability */
        }

        button {
            background-color: #4caf50; /* Green background */
            color: #ffffff; /* White text */
            border: none;
            padding: 12px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #388e3c; /* Darker green for hover effect */
        }

        .forgot-password {
            margin-top: 10px;
            font-size: 12px;
            color: #4caf50; /* Green color */
        }

        .forgot-password a {
            color: #4caf50; /* Green color for links */
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form method="POST" action="./actions/login_process.php">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Login</button>
            <div class="forgot-password">
                <a href="#">Forgot your password?</a>
            </div>
        </form>
    </div>
</body>
</html>
