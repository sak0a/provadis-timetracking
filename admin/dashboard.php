<?php 
if (strpos($_SERVER['REQUEST_URI'], 'admin/dashboard.php') !== false) {
    echo $_SERVER['REQUEST_URI'];
    header("Location: ../admin");
    exit();
}?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>