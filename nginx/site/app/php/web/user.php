<?php
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/server_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/permissions_handler.php';
    
    $manager = getManagerFromConfig();
    
    $session_id = $_COOKIE['session_id'];
    $user = getUserInfoFromSession($manager, $session_id);
    
    $target_user_nick = $_POST['target_user'] ?? $user['nickname'];
    $target_user = $manager->selectWithCondition(array("*"), "User", "nickname = '$target_user_nick'")[0];
    
    $manager->getConnector()->close();
    
    /**
     * Gets the html for a custom button with the given name to be used on the id, the text to be displayed,
     * and the permission integer needed to access it.
     *
     * @param string $button_name The name of the button, to be used on the id.
     * @param string $button_text The text to be displayed on the button.
     * @param int    $min_permission_integer The permission integer needed to access the button.
     * @param string $target_nick The nickname of the user that will be checked for permissions.
     *
     * @return string The generated html for the button.
     */
    function getCustomButtonHtml(string $button_name, string $button_text, int $min_permission_integer, string $target_nick) : string
    {
        $manager = getManagerFromConfig();
        $session_id = $_COOKIE['session_id'];
        $accessor = $manager->selectWithCondition(array('nickname'), 'ApplicationSession', "session_id = '$session_id'")[0]['nickname'];
        $manager->getConnector()->close();
        
        if ($accessor != $target_nick && !userHasWebPermission($accessor, $min_permission_integer))
            return "";
        
        return "<button id='$button_name-button' value='$button_text'></button>";
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Glowberry</title>
    
    <!-- Sets the favicon from glowberry's assets -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/app/css/user_profile.css">
    <script type="module" src="/app/js/click_handlers.js"></script>
</head>

<body>
    
    <div class="menu-header">
        
        <div class="vertical-divider" id="invisible"></div>
        
        <div class="menu-header-left">
            <img id="logo"  src="https://raw.githubusercontent.com/Glowberry-Servers/Glowberry-Assets/master/main/logo-webserver.png" alt="Glowberry Logo">
            <h1>Glowberry Web</h1>
        </div>
        
        <div class="menu-header-right">
            
            <i class='bx bxs-log-in' id="logout"><span>Log Out</span></i>
            
            <div class="vertical-divider" id="profile-divider"></div>
            <p><?php echo $target_user['display_name']?></p>
            
            <img id="profile-picture"
                 src="
                <?php
                 echo $target_user['profile_picture'] == null ? "https://raw.githubusercontent.com/Glowberry-Servers/Glowberry-Assets/master/images/user-default-profile-picture.png"
                     : $target_user['profile_picture']
                 ?>"
                 alt="Profile Picture"
            >
        </div>
    </div>
    
    <div class="information">
       
        <?php
           $wallpaper = $target_user['wallpaper'];
           
           echo $wallpaper == null
               ? "<div id='wallpaper' style=\"background: url('https://raw.githubusercontent.com/Glowberry-Servers/Glowberry-Assets/master/images/user-default-profile-wallpaper.jpg') center no-repeat; background-size: cover; alt='Wallpaper'\"></div>"
               : "<div id='wallpaper' style=\"background:url('$wallpaper'); alt='Wallpaper'></div>";
        ?>
        
        <div class="in-row">
            <?php
                $profile_picture = $target_user['profile_picture'];
                
                echo $profile_picture == null
                    ? "<img id='profile-picture-big' src='https://raw.githubusercontent.com/Glowberry-Servers/Glowberry-Assets/master/images/user-default-profile-picture.png' alt='Profile Picture'>"
                    : "<img id='profile-picture-big' src='$profile_picture' alt='Profile Picture'>";
            ?>
            
            <div class="identification">
                <h1 id="display-name" style="transform: translateY(80%);"><?php echo $target_user['display_name']?>andre Silva</h1>
                <p id="role-name" style="transform: translateY(100%);"><?php echo $target_user['role_name'] != "User" ? $target_user["role_name"] : ""?>test</p>
                <p id="true-name"><?php echo $target_user['nickname']?></p>
            </div>

            <i id="edit-display-name" class='bx bxs-edit'></i>
        </div>
        
        <div class="button-panels">
            <div id="left-button-spacer" class="invisible-spacer"></div>

            <div id="user-buttons">
                <?php
                    echo getCustomButtonHtml('change-password', 'Change Password', -1, $target_user_nick )
                    .
                    getCustomButtonHtml('change-display-name', 'Change Display Name', -1, $target_user_nick)
                ?>
            </div>
            
            <div class="invisible-spacer"></div>
            
            <div id="admin-buttons">
            </div>
            
            <div id="right-button-spacer" class="invisible-spacer"></div>
        </div>
        
    </div>
    
</body>
</html>

