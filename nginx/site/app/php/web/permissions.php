<?php
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/permissions_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/header.php';
    
    $manager = getManagerFromConfig();
    $session_id = $_COOKIE['session_id'];
    $user = getUserInfoFromSession($manager, $session_id);
    
    // Properly format the data received from the form
    $selected_user_nickname = $_POST['target_user'] != "" ? $_POST['target_user'] : null;
    $selected_server = $_POST['target_server'] != "" ? $_POST['target_server'] : null;
    
    $results = $manager->selectAllWithCondition('User', "user_tag = '$selected_user_nickname'");
    $selected_user = count($results) > 0 ? $results[0] : null;
    
    // If we're due redirecting to the permissions page and we have a user, redirect with the arguments
    if ($selected_user != null && isset($_POST['redirect'])) {
        echo json_encode(array("success" => true, "method" => "POST", "href" => "/app/php/web/permissions.php",
            "target_user" => $selected_user_nickname, "target_server" => $selected_server));
        exit();
    }
    
    // If we're due redirecting to the permissions page but we don't have a user, redirect without the arguments
    else if (isset($_POST['redirect'])) {
        echo json_encode(array("method" => "POST", "href" => "/app/php/web/permissions.php", "no-redirect" => true));
        exit();
    }
    
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
        return $target == null ? "Unknown" : getUserWebAppPermissions($target['user_tag']);
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
        
        if ($target == null || $server_name == null) return "Server not found";
        
        $manager = getManagerFromConfig();
        $results = $manager->selectAllWithCondition('Server', "name = '$server_name'");
        
        if (count($results) == 0) return "Server not found";
        
        $server = $results[0]['server_uuid'];
        $results = $manager->selectAllWithCondition('ServerUser', "user_tag = '{$target['user_tag']}' AND server_uuid = '$server'");
        
        if (count($results) == 0) return "0";
        return $results[0]['permissions_integer'];
    }
    
    /**
     * Returns all the specified permissions as a string of HTML containing named checkboxes with assigned values.
     *
     * @param string $permission_type The type of permissions to get. Either "WebApp" or "Server".
     *
     * @return string The HTML containing all the web app permissions.
     */
    function getAllPermissionsOfTypeHtml(string $permission_type) : string {
        
        // Get all permissions from the database
        $manager = getManagerFromConfig();
        $all_permissions = $manager->selectAllWithoutCondition($permission_type.'Permission');
        $manager->getConnector()->close();
        $permission_type = strtolower($permission_type);
        
        $html = array();
        
        // Iterates through all permissions and displays them as a named checkbox with an assigned value
        foreach ($all_permissions as $permission) {
            $html[] = "
<div class='{$permission_type}-permission-checkbox' data-permission-integer='{$permission['permissions_integer']}'>
    <input type='checkbox' id='{$permission['permission_name']}'>
    <label for='{$permission['permission_name']}'>{$permission['permission_name']}</label>
</div>";
                    }
        
        return implode("", $html);
    }
    
    /**
     * Returns all the server roles mapped to their permissions as a string of HTML containing table
     * rows.
     * @return string The HTML containing all the server permissions.
     */
    function getRolesTableHtml() : string
    {
        
        // Get all roles from the database
        $manager = getManagerFromConfig();
        $all_roles = $manager->selectAllWithoutCondition('Role');
        $manager->getConnector()->close();
        
        $html = "";
        
        // Iterates through all roles and displays them separated by a comma in the permissions column
        foreach ($all_roles as $role) {
            
            $permission_names = implode(", ", getAllPermissionNamesForInteger('WebAppPermission', $role['permissions_integer']));
            $html .= "
                    <tr style='border: 1px solid var(--tertiary)'>
                        <td>{$role['role_name']}</td>
                        <td>{$permission_names}</td>
                        <td>{$role['permissions_integer']}</td>
                    </tr>";
        }
        
        return $html;
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
            
            <div class="simple-column" style="position: relative">
                <form class="input-bar">
                    <input type="text" placeholder="Enter the user's tag (e.g '@admin')" id="user-search-permissions" value="<?php echo $selected_user_nickname ?? "" ?>">
                    <input type="text" placeholder="Insert the server name (e.g 'CoolServer')" id="server-search-permissions" value="<?php echo $selected_server ?? ""?>">
                    <input type="text" placeholder="Insert the server permissions integer" id="server-permissions-integer">
                </form>
                
                <p style="position:absolute; white-space: nowrap; bottom: -25px; color: var(--reds); font-size: 12px; width: fit-content" id="server-permissions-update-error"></p>
            </div>
            

            <div class="simple-column">
                <button class="input-button" id="user-search-permissions-button">Search</button>
                <button class="input-button" id="user-server-permissions-update-button">Update</button>
            </div>
            
            <div class="vertical-divider" id="search-divider"></div>
        
            <div class="user-info">
                <img id="user-profile-picture" src="<?php echo getUserProfilePicture($selected_user) ?>" alt="User Profile Picture">
                <p id="user-display-name"><?php echo getUserDisplayName($selected_user) ?></p>
            </div>

        </div>
    </div>
    
    <div class="permissions-table-container">
        
        <table class="permissions-table">
            
            <tr>
                <th>Role Permissions</th>
                <th>Server Permissions</th>
            </tr>
            <tr>
                <td>
                    <?php
                        // Get the user's permissions integer
                        $user_permissions = getUserWebAppPermissionsInteger($selected_user);
                        
                        // If the user is null, display "Unknown" and return
                        if ($user_permissions != "Unknown") {
                            
                            // If it isn't, get the permission names and display them
                            $permission_names = getAllPermissionNamesForInteger('WebAppPermission', $user_permissions);
                            sort($permission_names);
                            
                            foreach ($permission_names as $permission_name) {
                                echo "<p>$permission_name</p>";
                            }
                        }
                        
                        else echo $user_permissions;
                    ?>
                </td>
                <td>
                    <?php
                        // Get the user's permissions integer
                        $user_permissions = getUserServerPermissionsInteger($selected_user, $selected_server);
                        
                        // If the user is null, display "Server not found" and return
                        if ($user_permissions != "Server not found") {
                            
                            // If it isn't, get the permission names and display them
                            $permission_names = getAllPermissionNamesForInteger('ServerPermissions', $user_permissions);
                            
                            foreach ($permission_names as $permission_name) {
                                echo "<p>$permission_name</p>";
                            }
                        }
                        
                        else echo $user_permissions;
                    ?>
                </td>
            </tr>
            
        </table>
    </div>

    <div class='integer-calculator' style="width: 70%; margin: auto">

        <h3>Server Permissions Integer Calculator</h3>

        <div class='permissions-selector'>
            <?php echo getAllPermissionsOfTypeHtml('Server') ?>
        </div>

        <p>Result: <span>0</span></p>
    </div>
    
    <?php
        
        // If the user doesn't have the permission to manage roles, return
        if (!userHasWebPermission($user['user_tag'], 4))
            return;
        
        echo "

    <hr style='margin-top: 25px'>

    <div id='admin-ops'>

        <div class='input-box-admin'>
            
            <form class='input-bar-admin'>
                
                <h3>Role Management</h3>
        
                <input type='text' id='role-name' placeholder='Enter the role name'>
                <input type='text' id='role-permission-integer' placeholder='Enter the permission integer'>
                
                <hr style='margin-top: 4px; margin-bottom: 4px'>
                
                <button class='input-button-admin' id='set-role'>Set Role</button>
                <button class='input-button-admin' id='delete-role'>Delete Role</button>
                
                <p style='color: var(--reds); font-size: 14px' id='role-error'></p>
            </form>
        </div>
        
        <div class='integer-calculator'>
            
            <h3>Web App Permissions Integer Calculator</h3>
            
            <div class='permissions-selector'>
            " . getAllPermissionsOfTypeHtml('WebApp') . "
            </div>
            
            <p>Result: <span>0</span></p>
        </div>
    </div>
    
    <hr>
    
    <div class='permissions-table-container''>
        
        <table class='permissions-table'>
        
            <tr style='border: 1px solid var(--secondary)'>
                <th>Role Name</th>
                <th>Permissions</th>
                <th>Integer</th>
            </tr>". getRolesTableHtml() ."</table>
            
    </div>";
        ?>

</body>
</html>