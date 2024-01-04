<?php
    include $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/permissions_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/database_utils.php';
    
    $manager = getManagerFromConfig();
    $session_id = $_COOKIE['session_id'];
    
    $accessor = getUserInfoFromSession($manager, $session_id);
    $target_user = $manager->selectAllWithCondition('User', "user_tag = '{$_POST['target_user']}'");
    
    if (count($target_user) == 0)
    {
        http_response_code(200);
        echo json_encode(array("error" => "That user does not exist.", "element-name" => "server-permissions-update-error"));
        exit();
    }
    
    $target_user = $target_user[0];
    $server_name = $_POST['server_name'];
    $permissions_integer = $_POST['permissions_integer'];
    
    // Double check that the accessor has the correct permissions to do this
    if (!userHasServerPermission($accessor['user_tag'], $server_name, -1) && $accessor['user_tag'] == $target_user['user_tag'] && $accessor['user_tag'] != 'admin')
    {
        http_response_code(200);
        echo json_encode(array("error" => "You do not have permission to perform this action.", "element-name" => "server-permissions-update-error"));
        exit();
    }
    
    // Checks if the user is trying to change the admin's permissions
    if ($target_user['user_tag'] == 'admin')
    {
        http_response_code(200);
        echo json_encode(array("error" => "You are not allowed to change the admin's permissions.", "element-name" => "server-permissions-update-error"));
        exit();
    }
    
    // Checks if the permissions value is purely numeric
    if (!is_numeric($permissions_integer))
    {
        http_response_code(200);
        echo json_encode(array("error" => "The permissions value must be numeric.", "element-name" => "server-permissions-update-error"));
        exit();
    }
    
    // The only user that can manage permissions for a server is the owner, the only one with -1.
    // Because of this, they can't give owner permissions to anyone else.
    if ($permissions_integer == -1)
    {
        http_response_code(200);
        echo json_encode(array("error" => "You are not allowed to assign that permission.", "element-name" => "server-permissions-update-error"));
        exit();
    }
    
    // Checks if the server exists
    $server_uuid = getServerUUIDFromName($server_name);
    
    if ($server_uuid == null)
    {
        http_response_code(200);
        echo json_encode(array("error" => "That server does not exist.", "element-name" => "server-permissions-update-error"));
        exit();
    }
    
    // Checks if the user exists within the ServerUser table for the specified server
    $server_user = $manager->selectAllWithCondition('ServerUser', "server_uuid = '$server_uuid' AND user_tag = '{$target_user['user_tag']}'");
    
    if (count($server_user) == 0)
        
        // Add them to the table with the specified permissions if they don't exist
        $manager->insertWhole('ServerUser', array($server_uuid, $target_user['user_tag'], $permissions_integer));
    
    else $manager->update('ServerUser', 'permissions_integer', $permissions_integer, "server_uuid = '$server_uuid' AND user_tag = '{$target_user['user_tag']}'");
    
    $manager->getConnector()->close();
    
    http_response_code(200);
    echo json_encode(array("success" => "Successfully changed permissions.", "method" => "POST",
        "href" => "/app/php/web/permissions.php", "server_name" => $server_name));
    
    exit();
    
    
    
    
