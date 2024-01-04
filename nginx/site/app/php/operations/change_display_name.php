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
    $display_name = $_POST['display_name'];
    
    // Double check that the accessor has the correct permissions to do this
    if (!userHasWebPermission($accessor['user_tag'], 2) && $accessor['user_tag'] != $target_user['user_tag'])
    {
        http_response_code(200);
        echo json_encode(array("error" => "You do not have permission to perform this action.", "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    // Checks if the display name is alphanumeric and between 3 and 32 characters, including spaces
    if (!preg_match("/^(?=.{3,32}$)[a-zA-Z0-9 ]+$/", $display_name))
    {
        http_response_code(200);
        echo json_encode(array("error" => "Keep display names between 3 and 32 alphanumeric chars.", "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    // In case the user is trying to change the display name of the admin account, check if they are the admin
    if ($target_user['user_tag'] == 'admin' && $accessor['user_tag'] != 'admin')
    {
        http_response_code(200);
        echo json_encode(array("error" => "You cannot change the display name of the admin account.", "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    // Actually changes the display name
    $manager->update('User', 'display_name', $display_name, "user_tag = '{$target_user['user_tag']}'");
    $manager->getConnector()->close();
    
    http_response_code(200);
    echo json_encode(array("success" => "Successfully changed display name.", "method" => "POST",
        "href" => "/app/php/web/user.php", "target_user" => $target_user['user_tag']));
    
    exit();