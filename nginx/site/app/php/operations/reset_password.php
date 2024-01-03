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
    $one_time_code = $_POST['one_time_code'];
    
    // Double check that the accessor has the correct permissions to do this
    if (!userHasWebPermission($accessor['user_tag'], 16) && $accessor['user_tag'] != $target_user['user_tag'])
    {
        http_response_code(200);
        echo json_encode(array("error" => "You do not have permission to perform this action.", "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    // In case the user is trying to reset the password of the admin account, check if they are the admin
    if ($target_user['user_tag'] == 'admin')
    {
        http_response_code(200);
        echo json_encode(array("error" => "You cannot reset the password of the admin account.", "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    $hashed_password = password_hash($one_time_code, PASSWORD_DEFAULT, ['cost' => 10]);
    
    // Actually resets the password to the one time code
    $manager->update('User', 'password', $hashed_password, "user_tag = '{$target_user['user_tag']}'");
    $manager->getConnector()->close();
    
    http_response_code(200);
    echo json_encode(array("success" => "Successfully reset password.", "method" => "POST",
        "href" => "/reception/index.php", "target_user" => $target_user['user_tag']));
    
    exit();
