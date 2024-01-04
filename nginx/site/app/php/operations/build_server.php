<?php
    include $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/requests.php';
    
    $manager = getManagerFromConfig();
    $session_id = $_COOKIE['session_id'];
    $user = getUserInfoFromSession($manager, $session_id);
    
    // Gets the server information from the POST request.
    $server_name = $_POST['server_name'];
    $server_id = $user['user_tag'].".".$_POST['server_name'];
    $server_type = $_POST['server_type'];
    $server_version = $_POST['server_version'];
    $server_java_version = $_POST['java_version'];
    $modal_id = $_POST['modal_id'];
    
    // Checks if the server name is valid.
    if (!preg_match("/^[a-zA-Z0-9_]+$/", $server_name)) {
        http_response_code(200);
        echo json_encode(array("error" => "Invalid server name.", "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    // Checks if the server name is already taken.
    $results = $manager->selectWithCondition(array("server_uuid"), "Server", "server_uuid = '$server_id'");
    if (count($results) > 0) {
        http_response_code(200);
        echo json_encode(array("error" => "A server with this name already exists.", "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    // Checks if the glowberry webserver is online.
    if (!pingServer()) {
        http_response_code(200);
        echo json_encode(array("error" => "The Glowberry WebServer is offline at this moment.", "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    // Actually creates the server.
    $result = buildServer($server_id, $server_type, $server_version, $server_java_version);
    
    // Checks if the server was created successfully.
    if (!$result) {
        http_response_code(200);
        echo json_encode(array("error" => 'Could not create the server. Please check your parameters and try again.', "element-name" => "{$modal_id}-error"));
        exit();
    }
    
    $manager->insertWhole("Server", array($server_id, $server_name));
    $manager->insertWhole('ServerUser', array($server_id, $user['user_tag'], -1));
    
    $manager->getConnector()->close();
    http_response_code(200);
    
    echo json_encode(array("success" => "Successfully created server.", "method" => "POST",
        "href" => "/app/php/web/console.php", "server_uuid" => $server_id));
    
    exit();