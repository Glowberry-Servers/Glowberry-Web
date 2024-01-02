<?php
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/server_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/header.php';
    
    $manager = getManagerFromConfig();
    $session_id = $_COOKIE['session_id'];
    $user = getUserInfoFromSession($manager, $session_id);
    $manager->getConnector()->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Glowberry - Dashboard</title>

    <!-- Sets the favicon from glowberry's assets -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/app/css/dashboard.css">
    <script type="module" src="/app/js/click_handlers.js"></script>
</head>

<body>
    
    <?php echo getHeaderFor($user) ?>
    
    <div class="content">
        
        <div class="pretty-listing-options">

            <form id="search-bar">
                <i class='bx bx-search-alt-2'></i>
                <input type="text" placeholder="Find servers by name">
                <div class="vertical-divider" id="search-divider"></div>
            </form>
            
            <button id="new-server" class="option-button">New Server</button>
        </div>
        <hr>
        
        <div class="pretty-list">
            
            <?php
                $servers = getServersForUser($user['user_tag']);
                echo getHtmlForServerArray($servers)
            ?>
            
        </div>
    </div>
    

</body>
</html>
