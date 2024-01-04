<?php
    
    // Refuses to load if the method is not a POST request with the security code.
    if (!isset($_POST["security_code"])) {
        http_response_code(500);
        exit();
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Glowberry - Dashboard</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/reception/css/security_code.css">
    <script src="/reception/js/userops.js" type="module"></script>
</head>
<body>

    <div class="wrapper">

        <div id="title" class="content">
            <i class='bx bxs-detail'></i>
            <h1>Wait a sec!</h1>
            <i class='bx bxs-detail'></i>
        </div>

        <div class="content">
            <p style="font-size: 20px">This is your account's security code.</p>
            <p style="padding-top: 10px; color: darkgrey">We'll use this to recover your account if you ever lose access
                to it.</p>
            <p id="security-code"><b><?php echo $_POST["security_code"] ?></b></p>
        </div>

        <div class="content">
            <p style="margin-top: 10px; margin-bottom: 10px; color: darkgrey"> Don't share this code with anyone.</p>
        </div>

        <button id="back">I've saved my security code, let's log in.</button>
        <p class="content once-notice" style="font-weight: 500">*This will only be shown once.</p>
    </div>

</body>
</html>