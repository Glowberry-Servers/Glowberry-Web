<?php
    
    include $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/permissions_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/database_utils.php';
    
    $manager = getManagerFromConfig();
    $session_id = $_COOKIE['session_id'];
    
    $accessor = getUserInfoFromSession($manager, $session_id);
    $target_user = $manager->selectAllWithCondition('User', "user_tag = '{$_POST['target_user']}'")[0];
    $modal_id = $_POST['modal_id'];
    $old_password = $_POST['password'];
    
    // Double check that the accessor has the correct permissions to do this
    if (!userHasWebPermission($accessor['user_tag'], -1) && $accessor['user_tag'] != $target_user['user_tag'])
    {
        http_response_code(200);
        echo json_encode(array("error" => "You do not have permission to perform this action.", "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    // Checks if the password is correct
    if (!password_verify($old_password, $target_user['password']))
    {
        http_response_code(200);
        echo json_encode(array("error" => "The password is incorrect.", "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    // Checks if the user is trying to delete the admin account
    if ($target_user['user_tag'] == 'admin')
    {
        http_response_code(200);
        echo json_encode(array("error" => "You cannot delete the admin account.", "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    // Deletes all the servers the user owns
    $server_uuids = $manager->selectAllWithCondition('ServerUser', "user_tag = '{$target_user['user_tag']}' AND permissions_integer = -1");
    
    foreach ($server_uuids as $server_uuid) {
        $manager->deleteFrom('Server', "server_uuid = '{$server_uuid['server_uuid']}'");
        // TODO: Add an API call to delete the server from the server host
    }
    
    // Deletes the user's current application session
    $manager->deleteFrom('ApplicationSession', "user_tag = '{$target_user['user_tag']}'");
    
    // Deletes the user from the database
    $manager->deleteFrom('User', "user_tag = '{$target_user['user_tag']}'");
    $manager->getConnector()->close();
    
    http_response_code(200);
    echo json_encode(array("success" => "Successfully deleted account.", "method" => "POST", "href" => "/reception/index.php"));
    exit();
    