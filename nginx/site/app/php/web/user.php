<?php
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/server_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/permissions_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/modals_creator.php';
    
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
        
        // If the accessor does not have the required permissions, return null.
        // If they are the target user and the bypass is enabled, they don't need the permissions.
        if (!($target_bypass && $accessor == $target_nick) &&! userHasWebPermission($accessor, $min_permission_integer) )
            return null;
        
        // No one can change the admin account's info or else things will get messy.
        if ($accessor!= "admin" && $target_nick == "admin")
            return null;
        
        $button_colour = $button_colour ?? "#fff";
        $button_text_colour = $button_text_colour == null ? "black" : "white";
        
        return "<button class='user-action-button' id='$button_name-button' style='
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
            getCustomButtonHtml('change-password', 'Change Password', -999, $target_nick),
            getCustomButtonHtml('change-profile-picture', 'Change Profile Picture', 2, $target_nick),
            getCustomButtonHtml('change-wallpaper', 'Change Wallpaper', 2, $target_nick),
            getCustomButtonHtml('delete-account', 'Delete Account', -1, $target_nick, 'var(--reds)', 'whitesmoke')
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
    
    /**
     * Accesses the available roles for the given user and returns the HTML code for the options.
     * @param array $user The user array to be used to get the current permissions.
     *
     * @return string The HTML code for the options or a message if there are no available roles.
     */
    function getHTMLForAvailableRoles(array $user) : string {

        // Gets the user's permissions and the roles that are lower than the user's.
        $user_permissions = getUserWebAppPermissions($user['user_tag']);
        $available_roles = getAllRolesWithPermissionWithin($user_permissions);
        
        // Iterates through the roles and creates an option for each one.
        $html = null;
        
        foreach ($available_roles as $role)
            $html .= "<option value='{$role}'>{$role}</option>";
        
        return $html ?? "There are no other roles lower than yours.";
    }
    
    /**
     * Gets the content to be used in the modal form used to change the display name.
     * @param array $target The user array to be used to get the information needed
     *
     * @return string The HTML code for the modal content.
     */
    function getContentForChangeDisplayNameModal(array $target) : string {
        return "
<input type='text' id='display-name-input' placeholder='Display Name' value='{$target['display_name']}'>
";
    }
    
    /**
     * Gets the content to be used in the modal form used to change the password.
     * @param array $target The user array to be used to get the information needed
     *
     * @return string The HTML code for the modal content.
     */
    function getContentForChangePasswordModal() : string {
        return "
<input style='margin-top:5px' type='password' id='current-password-input' placeholder='Current Password'>
<input type='password' id='new-password-input' placeholder='New Password'>
<input type='password' id='confirm-password-input' placeholder='Confirm Password'>
";
    }
    
    /**
     * Gets the content to be used in the modal form used to change the profile picture.
     * @param array $target The user array to be used to get the current profile picture.
     *
     * @return string The HTML code for the modal content.
     */
    function getContentForChangeProfilePictureModal(array $target) : string {
        return "
<p>Only '.png' images are allowed.</p>
<input type='text' id='profile-picture-input' placeholder='Profile Picture URL' value='{$target['profile_picture']}'>
";
    }
    
    /**
     * Gets the content to be used in the modal form used to change the wallpaper.
     * @param array $target The user array to be used to get the current wallpaper.
     *
     * @return string The HTML code for the modal content.
     */
    function getContentForChangeWallpaperModal(array $target) : string {
        return "
<p>Only '.png' images are allowed.</p>
<input type='text' id='wallpaper-input' placeholder='Wallpaper URL' value='{$target['wallpaper']}'>
";
    }
    
    /**
     * Gets the content to be used in the modal form used to delete the account.
     * @param array $target The user array to be used to get the information needed
     *
     * @return string The HTML code for the modal content.
     */
    function getContentForDeleteAccountModal(array $target) : string {
        return "
<p>Type in your password in order to delete your account.</p>
<input type='password' id='password-input' placeholder='Password'>
";
    }
    
    /**
     * Gets the content to be used in the modal form used to change the role.
     * @param array $target The user array to be used to get the permissions.
     *
     * @return string The HTML code for the modal content.
     */
    function getContentForChangeRoleModal(array $target) : string {
        return "
<select id='role-input'>". getHTMLForAvailableRoles($target) ."</select>
";
    }
    
    /**
     * Gets the content to be used in the modal form used to allocate resources.
     * @param array $target The user array to be used to get the information needed
     *
     * @return string The HTML code for the modal content.
     */
    function getContentForAllocateResourcesModal(array $target) : string {
        return "
<label for='ram-input'>Max RAM</label>
<input type='text' id='ram-input' placeholder='RAM Value' value='{$target['max_ram']}'>
";
    }
    
    /**
     * Gets the content to be used in the modal form used to reset the password.
     * @param array $target The user array to be used to get the information needed
     *
     * @return string The HTML code for the modal content.
     */
    function getContentForResetPasswordModal(array $target) : string {
        return "
<p>Are you sure you want to reset this user's password? Their one-time usage password will be the following:</p>

<div class='one-time-password-container'>
    <p>".uniqid()."</p>
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
    <link rel="stylesheet" href="/app/css/modal.css">
    <link rel="stylesheet" href="/app/css/user_profile.css">
    <script type="module" src="/app/js/click_handlers.js"></script>
    <script type="module" src="/app/js/modal_handlers.js"></script>
</head>

<body>
    
    <?php echo getHeaderFor($user); ?>
    
    <?php echo createModal('edit-display-name-modal', 'Change Display Name', getContentForChangeDisplayNameModal($target_user), 'column', '400px'); ?>
    <?php echo createModal('change-password-modal', 'Change Password', getContentForChangePasswordModal($target_user), 'column', '400px'); ?>
    <?php echo createModal('change-profile-picture-modal', 'Change Profile Picture', getContentForChangeProfilePictureModal($target_user), 'column', '400px'); ?>
    <?php echo createModal('change-wallpaper-modal', 'Change Wallpaper', getContentForChangeWallpaperModal($target_user), 'column', '400px'); ?>
    <?php echo createModal('delete-account-modal', 'Delete Account', getContentForDeleteAccountModal($target_user), 'column', '400px'); ?>
    <?php echo createModal('change-role-modal', 'Change Role', getContentForChangeRoleModal($user), 'single', '400px'); ?>
    <?php echo createModal('allocate-resources-modal', 'Allocate Resources', getContentForAllocateResourcesModal($target_user), 'column', '400px'); ?>
    <?php echo createModal('reset-password-modal', 'Reset Password', getContentForResetPasswordModal($target_user), 'column', '400px'); ?>
    
    <div class="information">
       
        <div class="wallpaper-container">
            <?php
               $wallpaper = $target_user['wallpaper'];
               
               echo $wallpaper != null && checkIfImageUrlIsValid($wallpaper)
                   ? "<img id='wallpaper' src=$wallpaper alt='Wallpaper'>"
                   : "<img id='wallpaper' src=https://raw.githubusercontent.com/Glowberry-Servers/Glowberry-Assets/master/images/user-default-profile-wallpaper.jpg alt='Wallpaper'\">";
    
            ?>
        </div>
        
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
                if ($target_user_nick == $user['user_tag'] || userHasWebPermission($user['user_tag'], 2) && $target_user['user_tag'] != "admin")
                    echo '<i id="edit-display-name-button" class="bx bxs-edit"></i>'
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

