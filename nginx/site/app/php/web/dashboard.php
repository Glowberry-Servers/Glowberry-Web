<?php
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/server_handler.php';
    
    $manager = getManagerFromConfig();
    $session_id = $_COOKIE['session_id'];
    $user = getUserInfoFromSession($manager, $session_id);
    $manager->getConnector()->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Glowberry</title>

    <!-- Sets the favicon from glowberry's assets -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/app/css/dashboard.css">
    <script type="module" src="/app/js/click_handlers.js"></script>
</head>

<body>
    
    <div class="menu-header">

        <div class="vertical-divider" id="invisible"></div>

        <div class="menu-header-left">
            <img id="logo" src="https://raw.githubusercontent.com/Glowberry-Servers/Glowberry-Assets/master/main/logo-webserver.png" alt="Glowberry Logo">
            <h1>Glowberry Web</h1>
        </div>
        
        <div class="menu-header-right">
            
            <i class='bx bxs-log-in' id="logout"><span>Log Out</span></i>
            
            <div class="vertical-divider" id="profile-divider"></div>
            <p><?php echo $user['display_name']?></p>
            
            <img id="profile-picture"
                 src="
                <?php
                    echo $user['profile_picture'] == null ? "https://raw.githubusercontent.com/Glowberry-Servers/Glowberry-Assets/master/images/user-default-profile-picture.png"
                        : $user['profile_picture']
            
                ?>"
                 alt="Profile Picture"
            >
        </div>
    </div>
    
    <div class="content">
        
        <div class="server-list-options">

            <form id="search-bar">
                <i class='bx bx-search-alt-2'></i>
                <input type="text" placeholder="Search for a server">
                <div class="vertical-divider" id="servers-divider"></div>
            </form>
            
            <button id="new-server" class="option-button">New Server</button>
        </div>
        <hr>
        
        <div class="server-list">
            
            <?php
                $servers = getServersForUser($user['nickname']);
                echo getHtmlForServerArray($servers)
            ?>
            
        </div>
    </div>
    

</body>
</html>
