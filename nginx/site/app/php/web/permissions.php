<?php
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/permissions_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/header.php';
    
    $manager = getManagerFromConfig();
    $session_id = $_COOKIE['session_id'];
    $user = getUserInfoFromSession($manager, $session_id);
    
    $selected_user_nickname = $_POST['target_user'];
    
    if ($selected_user_nickname == null) $selected_user = null;
    else $selected_user = $manager->selectAllWithCondition('users', "user_tag = '$selected_user_nickname'")[0];
    
    $selected_server = $_POST['target_server'];
    $manager->getConnector()->close();
    
    
    /**
     * Returns the target user's profile picture.
     * @param ?array $target The target user.
     *
     * @return string The target user's profile picture URL.
     */
    function getUserProfilePicture(?array $target) : string {
        
        return $target == null || $target['profile_picture'] == null
            ? 'https://raw.githubusercontent.com/Glowberry-Servers/Glowberry-Assets/master/images/user-default-profile-picture.png'
            : $target['profile_picture'];
    }
    
    /**
     * Returns the target user's display name (or "User not found" if the user is null).
     * @param ?array $target The target user.
     *
     * @return string The target user's display name.
     */
    function getUserDisplayName(?array $target) : string {
        return $target == null ? "User not found" : $target['display_name'];
    }
    
    /**
     * Returns the target user's web app permissions integer.
     * @param ?array $target The target user.
     *
     * @return string The permissions integer associated with the target user.
     */
    function getUserWebAppPermissionsInteger(?array $target) : string {
        return $target == null ? "Unknown" : $target['web_app_permissions_integer'];
    }
    
    /**
     * Returns the target's permissions for a specified server.
     *
     * @param ?array  $target The target user.
     * @param ?string $server_name The name of the server to get the permissions for.
     *
     * @return string The permissions integer for the target user on the specified server.
     */
    function getUserServerPermissionsInteger(?array $target, ?string $server_name) : string {
        
        if ($target == null) return "Server not found";
        
        $manager = getManagerFromConfig();
        $server = $manager->selectAllWithCondition('servers', "server_name = '$server_name'")[0]["server_uuid"];
        return $manager->selectAllWithCondition('ServerUser', "user_tag = '{$target['user_tag']}' AND server_uuid = '$server'")[0]["permissions_integer"];
    }
    
    /**
     * Returns all the web app permissions as a string of HTML containing named checkboxes with assigned values.
     * @return string The HTML containing all the web app permissions.
     */
    function getAllWebAppPermissionsHtml() : string {
        
        // Get all permissions from the database
        $manager = getManagerFromConfig();
        $all_permissions = $manager->selectAllWithoutCondition('WebAppPermission');
        $manager->getConnector()->close();
        
        $html = array();
        
        // Iterates through all permissions and displays them as a named checkbox with an assigned value
        foreach ($all_permissions as $permission) {
            $html[] = "
<div class='permission-checkbox' data-permission-integer='{$permission['permissions_integer']}'>
    <input type='checkbox' id='{$permission['permission_name']}'>
    <label for='{$permission['permission_name']}'>{$permission['permission_name']}</label>
</div>";
                    }
        
        return implode("", $html);
    }
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Glowberry - Permissions</title>

    <!-- Sets the favicon from glowberry's assets -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/app/css/permissions.css">
    <script type="module" src="/app/js/click_handlers.js"></script>
</head>

<body>
    
    <?php echo getHeaderFor($user); ?>
    
    <div id="permissions-user-selector">
        
        <div class="input-box">
            
            <form class="input-bar">
                <i class='bx bx-search-alt-2'></i>
                <input type="text" placeholder="Enter the user's tag (e.g @admin)">
                <button class="input-button">Search</button>
            </form>
            
            <div class="vertical-divider" id="search-divider"></div>
        
            <div class="user-info">
                <img id="user-profile-picture" src="<?php echo getUserProfilePicture($selected_user) ?>" alt="User Profile Picture">
                <p id="user-display-name"><?php echo getUserDisplayName($selected_user) ?></p>
            </div>

        </div>


    </div>
    
    <div id="permissions-table-container">
        
        <table id="permissions-table">
            
            <tr>
                <th>Role Permissions (Web App)</th>
                <th><p class="th-title">Server (Search)</p>
                    
                    <div class="input-box" style="margin: 5px">

                        <form class="input-bar" style="margin-left: 7.5%">
                            <i class='bx bx-search-alt-2'></i>
                            <input type="text" placeholder="Enter the user's tag (e.g @admin)">

                            <button class="input-button">Search</button>
                        </form>

                    </div>
                    
                </th>
            </tr>
            <tr>
                <td>
                    <?php
                        // Get the user's permissions integer
                        $user_permissions = getUserWebAppPermissionsInteger($selected_user);
                        
                        // If the user is null, display "Unknown" and return
                        if ($user_permissions != "Unknown") {
                            
                            // If it isn't, get the permission names and display them
                            $permission_names = getAllPermissionsForInteger('WebAppPermissions', $user_permissions);
                            
                            foreach ($permission_names as $permission_name) {
                                echo "<p>$permission_name</p>";
                            }
                        }
                        
                        echo $user_permissions;
                    ?>
                </td>
                <td>
                    <?php
                        // Get the user's permissions integer
                        $user_permissions = getUserServerPermissionsInteger($selected_user, $selected_server);
                        
                        // If the user is null, display "Server not found" and return
                        if ($user_permissions != "Server not found") {
                            
                            // If it isn't, get the permission names and display them
                            $permission_names = getAllPermissionsForInteger('ServerPermissions', $user_permissions);
                            
                            foreach ($permission_names as $permission_name) {
                                echo "<p>$permission_name</p>";
                            }
                        }
                        
                        echo $user_permissions;
                    ?>
                </td>
            </tr>
            
        </table>
    </div>
    
    <hr>
        
        <?php
            
            // If the user doesn't have the permission to manage roles, return
            if (!userHasWebPermission($user['user_tag'], 4))
                return;
            
            echo "

        <div id='admin-ops'>
    
            <div class='input-box-admin'>
                
                <form class='input-bar-admin'>
                    
                    <h3>Role Management</h3>
            
                    <input type='text' placeholder='Enter the role name' required>
                    <input type='number' placeholder='Enter the permission integer'>
                    
                    <button class='input-button-admin' id='set-role'>Set Role</button>
                    <button class='input-button-admin' id='delete-role'>Delete Role</button>
                </form>
            </div>
            
            <div class='integer-calculator'>
                
                <h3>Permissions Integer Calculator</h3>
                
                <div id='permissions-selector'>
                " . getAllWebAppPermissionsHtml() . "
                </div>
                
                <div id='result'>
                    <p>Result: <span id='result-value'>0</span></p>
                </div>
            </div>
        </div>
        ";
            ?>

</body>
</html>