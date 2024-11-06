<!DOCTYPE html>
<html>
<head>
    <title>Page Not Found</title>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        h1 {
            color: #444;
            font-size: 3em;
            margin-bottom: 20px;
        }

        p {
            color: #777;
            font-size: 1.2em;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The page you are looking for might have been removed or is temporarily unavailable.</p>
    <p><a href="<?php echo BASE_URL; ?>">Go back to the homepage</a></p> 
</body>
</html>
