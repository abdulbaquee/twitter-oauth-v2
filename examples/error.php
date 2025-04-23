<?php
$errorMessage = $_GET['message'] ?? 'An unknown error occurred';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitter Authentication Error</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            line-height: 1.6;
        }
        .error-message {
            background-color: #ffebee;
            border: 1px solid #e53935;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #1da1f2;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #1991db;
        }
    </style>
</head>
<body>
    <div class="error-message">
        <h1>⚠️ Authentication Error</h1>
        <p><?php echo htmlspecialchars($errorMessage); ?></p>
    </div>

    <div>
        <a href="/" class="btn">Return to Home</a>
        <a href="/auth.php" class="btn">Try Again</a>
    </div>
</body>
</html> 