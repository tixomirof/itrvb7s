<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Практика №1</title>
</head>
<body>
    <?php
    ob_start();
    $user = new User('login', '123');
    if ($user->connected) {
        $user->showProfile();
    }
    ?>
</body>
</html>