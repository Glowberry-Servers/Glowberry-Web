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
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Double check that the accessor has the correct permissions to do this
    if ($accessor['user_tag'] != $target_user['user_tag'] && $accessor['user_tag'] != 'admin')
    {
        http_response_code(200);
        echo json_encode(array("error" => "You do not have permission to perform this action.", "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    // Checks if the password and confirmation match.
    if ($new_password != $confirm_password) {
        http_response_code(200);
        echo json_encode(array('error' => "The passwords do not match.", "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    // Checks if the password is at least 6 characters long, and contains at least one number.
    if (strlen($new_password) < 6 || !preg_match('/[0-9]/', $new_password)) {
        http_response_code(200);
        echo json_encode(array('error' => "The password must be at least 6 characters long, and have at least one number.", "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    // Checks if the old password is correct
    if (!password_verify($old_password, $target_user['password']))
    {
        http_response_code(200);
        echo json_encode(array("error" => "The old password is incorrect.", "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    // Hashes the password for storage
    $new_password = password_hash($new_password, PASSWORD_DEFAULT, ['cost' => 10]);
    
    // Actually changes the password
    $manager->update('User', 'password', $new_password, "user_tag = '{$target_user['user_tag']}'");
    $manager->getConnector()->close();
    
    http_response_code(200);
    echo json_encode(array("success" => "Successfully changed password.", "method" => "POST",
        "href" => "/reception/index.php", "target_user" => $target_user['user_tag']));
    
    exit();
