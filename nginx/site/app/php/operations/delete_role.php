<?php
    include $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/permissions_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/database_utils.php';
    
    $session_id = $_COOKIE['session_id'];
    $manager = getManagerFromConfig();
    $user = getUserInfoFromSession($manager, $session_id);
    
    $role_name = $_POST['role_name'];
    
    // Double check that the user has the correct permissions to even access this page
    if (!userHasWebPermission($user['user_tag'], 4))
    {
        http_response_code(200);
        echo json_encode(array("error" => "You do not have permission to perform this action.", "element-name" => "role-error"));
        exit();
    }
    
    $role_exists = count($manager->selectAllWithCondition('Role', "role_name = '$role_name'")) > 0;
    
    if (!$role_exists) {
        echo json_encode(array("error" => "That role does not exist.", "element-name" => "role-error"));
        exit();
    }
    
    // Prevents the user from deleting the default 'User' role
    if ($role_name == 'User')
    {
        http_response_code(200);
        echo json_encode(array("error" => "You cannot delete the default role.", "element-name" => "role-error"));
        exit();
    }
    
    // Prevents the user from deleting the admin role
    if ($role_name == 'Administrator')
    {
        http_response_code(200);
        echo json_encode(array("error" => "You cannot delete the admin role.", "element-name" => "role-error"));
        exit();
    }
    
    // Checks if the role is above the user's role, and if so, denies the request
    if (!userIsAboveRole($user['user_tag'], $role_name) && $user['user_tag'] != 'admin')
    {
        http_response_code(200);
        echo json_encode(array("error" => "You cannot delete a role above or equal to your own.", "element-name" => "role-error"));
        exit();
    }
    
    // Changes every user with the role to the default role 'user'
    $manager->update('User', "role_name", "User", "role_name = '$role_name'");
    $manager->deleteFrom('Role', "role_name = '$role_name'");
    $manager->getConnector()->close();
    
    echo json_encode(array("success" => true, "method" => "POST", "href" => "/app/php/web/permissions.php"));
    exit();
    

