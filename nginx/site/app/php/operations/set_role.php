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
    $role_permissions = $_POST['permissions_integer'];
    
    // Double check that the user has the correct permissions to even access this page
    if (!userHasWebPermission($user['user_tag'], 4))
    {
        http_response_code(200);
        echo json_encode(array("error" => "You do not have permission to perform this action.", "element-name" => "role-error"));
        exit();
    }
    
    // Checks if the permissions value is purely numeric
    if (!is_numeric($role_permissions))
    {
        http_response_code(200);
        echo json_encode(array("error" => "The permissions value must be numeric.", "element-name" => "role-error"));
        exit();
    }
    
    // Prevents the user from changing the default 'User' role
    if ($role_name == 'Administrator')
    {
        http_response_code(200);
        echo json_encode(array("error" => "You cannot change the admin role's permissions.", "element-name" => "role-error"));
        exit();
    }
    
    // Checks if the role name is alphanumeric and between 3 and 15 characters
    if (!preg_match('/^[a-zA-Z0-9]{3,15}$/', $role_name))
    {
        http_response_code(200);
        echo json_encode(array("error" => "Keep role names between 3 and 15 alphanumeric chars.", "element-name" => "role-error"));
        exit();
    }
    
    // If the role exists, update it, otherwise create it
    $role_exists = count($manager->selectAllWithCondition('Role', "role_name = '$role_name'")) > 0;
    
    // Checks if the role is above the user's role, if it exists.
    if ($role_exists && !userIsAboveOrEqualToRole($user['user_tag'], $role_name) && $user['user_tag'] != 'admin')
    {
        http_response_code(200);
        echo json_encode(array("error" => "You cannot manage a role above your own.", "element-name" => "role-error"));
        exit();
    }
    
    // Checks if the permission to assign is not within the user's permissions
    if ($user['user_tag'] != 'admin' && !integerContainsPermission(getUserWebAppPermissions($user['user_tag']), $role_permissions))
    {
        http_response_code(200);
        echo json_encode(array("error" => "You cannot assign roles with a permissions that you do not have.", "element-name" => "role-error"));
        exit();
    }
    
    if ($role_exists) {
        $manager->update('Role', "permissions_integer", $role_permissions, "role_name = '$role_name'");
        echo json_encode(array("success" => true, "method" => "POST", "href" => "/app/php/web/permissions.php"));
        exit();
    }
    
    $manager->insertWhole('Role', array($role_name, $role_permissions));
    $manager->getConnector()->close();
    echo json_encode(array("success" => true, "method" => "POST", "href" => "/app/php/web/permissions.php"));
    exit();
    
    
    
    
    