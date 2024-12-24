<!-- resources/views/emails/user_created.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to the Platform</title>
</head>
<body>
    <h1>Welcome, {{ $name }}!</h1>
    <p>Thank you for registering with us. Your account has been created successfully.</p>
    <p>Your email address is: {{ $email }}</p>
</body>
</html>
