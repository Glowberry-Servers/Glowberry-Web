<?php
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/server_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/permissions_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/header.php';
    
    $manager = getManagerFromConfig();
    
    $session_id = $_COOKIE['session_id'];
    $user = getUserInfoFromSession($manager, $session_id);
    
    $target_user_nick = $_POST['target_user'] ?? $user['user_tag'];
    $target_user = $manager->selectWithCondition(array("*"), "User", "user_tag = '$target_user_nick'")[0];
    
    $manager->getConnector()->close();
    
    /**
     * Gets the html for a custom button with the given name to be used on the id, the text to be displayed,
     * and the permission integer needed to access it.
     *
     * @param string      $button_name The name of the button, to be used on the id.
     * @param string      $button_text The text to be displayed on the button.
     * @param int         $min_permission_integer The permission integer needed to access the button.
     * @param string      $target_nick The user_tag of the user that will be checked for permissions.
     * @param string|null $button_colour The colour of the button. If null, defaults.
     * @param string|null $button_text_colour The colour of the button text. If null, defaults.
     * @param bool        $target_bypass Whether the target user can bypass the permissions check.
     *
     * @return string | null The generated html for the button or null if the user does not have the required permissions.
     */
    function getCustomButtonHtml(string $button_name, string $button_text, int $min_permission_integer, string $target_nick, string $button_colour = null, string $button_text_colour = null, bool $target_bypass = true) : ?string
    {
        $manager = getManagerFromConfig();
        $session_id = $_COOKIE['session_id'];
        $accessor = $manager->selectWithCondition(array('user_tag'), 'ApplicationSession', "session_id = '$session_id'")[0]['user_tag'];
        $manager->getConnector()->close();
        
        if (!($target_bypass && $accessor == $target_nick) &&! userHasWebPermission($accessor, $min_permission_integer))
            return null;
        
        $button_colour = $button_colour ?? "#fff";
        $button_text_colour = $button_text_colour == null ? "black" : "white";
        
        return "
<button class='user-action-buttons' id='$button_name-button' style='
    display: inline-block;
    padding-left: 10px;
    padding-right: 10px;
    width: 150px;
    height: 40px;
    font-size: 13px;
    border: none;
    background-color: $button_colour;
    color: $button_text_colour;
    cursor: pointer;
    border-radius: 5px;
'>$button_text</button>";
    }
    
    /**
     * Generates the entire HTML to be placed inside the user buttons div.
     *
     * @param string $target_nick The user_tag of the user that will be checked for permissions.
     *
     * @return string|null The generated HTML or null if there are no buttons to be displayed.
     */
    function getUserButtonsHtml(string $target_nick) : ?string {
        
        // Declare the buttons to be displayed.
        $buttons = [
            getCustomButtonHtml('change-password', 'Change Password', -1, $target_nick),
            getCustomButtonHtml('change-profile-picture', 'Change Profile Picture', 2, $target_nick),
            getCustomButtonHtml('change-wallpaper', 'Change Wallpaper', 2, $target_nick),
            getCustomButtonHtml('delete-account', 'Delete Account', -1, $target_nick, 'red', 'whitesmoke')
            ];
        
        // Removes the null values from the array and implodes it into a string.
        // If the array is empty, return null.
        if (sizeof(array_filter($buttons)) == 0) return null;
        $buttons_html = implode("", array_filter($buttons));
        
        return "
<div id='user-buttons' style='display: grid; grid-template-columns: repeat(4, 1fr);  gap: 10px; width: inherit; height: inherit; padding: 20px; text-align: center; place-items: center'>
    $buttons_html
</div>
";
    }
    
    /**
     * Generates the entire HTML to be placed inside the admin buttons div.
     * @param string $target_nick
     *
     * @return string|null
     */
    function getAdminButtonsHtml(string $target_nick) : ?string {
        
        // Declare the buttons to be displayed.
        $buttons = [
            getCustomButtonHtml('change-role', 'Change Role', 4, $target_nick, target_bypass: false),
            getCustomButtonHtml('allocate-resources', 'Allocate Resources', 8, $target_nick, target_bypass: false),
            getCustomButtonHtml('reset-password', 'Reset Password', 16, $target_nick, target_bypass: false),
        ];
        
        // Removes the null values from the array and implodes it into a string.
        // If the array is empty, return null.
        if (sizeof(array_filter($buttons)) == 0) return null;
        $buttons_html = implode("", array_filter($buttons));
        
        return "
<div id='admin-buttons' style='display: grid; grid-template-columns: 1fr; margin: auto; gap: 10px; width: inherit; height: inherit; padding: 20px; text-align: center;'>
    $buttons_html
</div>
        ";
        
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
    
    <?php echo getHeaderFor($user); ?>
    
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

            <p id='true-name'>@<?php echo $target_user['user_tag']?></p>
            
            <div class="identification">
                <h1 id="display-name" style="transform: translateY(80%);"><?php echo $target_user['display_name']?></h1>
                <p id="role-name" style="transform: translateY(100%);"><?php echo $target_user['role_name'] != "User" ? $target_user["role_name"] : ""?></p>
            </div>

            <?php
                if ($target_user_nick == $user['user_tag'])
                    echo '<i id="edit-display-name" class="bx bxs-edit"></i>'
            ?>
        </div>
        
        <div class="button-panels">

            <div id="user-buttons-container">
                <?php
                    $user_buttons = getUserButtonsHtml($target_user_nick);
                    if ($user_buttons != null) echo $user_buttons;
                ?>
            </div>
            
            
            <div id="admin-buttons-container">
                <?php
                    $user_buttons = getAdminButtonsHtml($target_user_nick);
                    if ($user_buttons != null) echo $user_buttons;
                ?>
            </div>
            
        </div>
        <hr>
        
    </div>
    
</body>
</html>

