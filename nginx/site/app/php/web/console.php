<?php
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/header.php';
    
    $manager = getManagerFromConfig();
    $session_id = $_COOKIE['session_id'];
    $user = getUserInfoFromSession($manager, $session_id);
    
    $server_id = $_POST['server_uuid'];
    $server_name = $manager->selectWithCondition(array("name"), "Server", "server_uuid = '$server_id'")[0]['name'];
    
    $manager->getConnector()->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Glowberry - <?php echo $server_name ?></title>
    <data-server-id><?php echo $server_id ?></data-server-id>
    <link rel="stylesheet" href="/app/css/console.css">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script type="module" src="/app/js/console_handler.js"></script>
</head>
<body>

    <?php echo getHeaderFor($user); ?>
    
    <div class="container">
        
        <div id="console">
        </div>
    
        <div id="input-container">
            <span id="input-prefix">></span>
            <input type="text" id="inputField" placeholder='Send a command to <?php echo 'Glowberry' ?>'>
            <button id="send-button">Send</button>
        </div>

    </div>

</body>
</html>