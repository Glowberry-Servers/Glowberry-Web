<?php

    // Checks if the method is POST.
    if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
        http_response_code(405);
        exit();
    }

    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/database_utils.php';

    // Gets the database manager from the config file and the passed parameters from the POST request.
    $manager = getManagerFromConfig();

    // Gets the security code and the new password from the POST request.
    $security_code = $_POST["security_code"];
    $password = $_POST["new_password"];

    // Checks if the security code is valid.
    $results = $manager->selectWithCondition(array('nickname'), "User", "security_code = '$security_code'");

    if (count($results) == 0) {
        http_response_code(200);
        echo json_encode(array('error' => "Invalid security code.", 'element-name' => "security-code-error"));
        exit();
    }

    // Checks if the password is at least 6 characters long, and contains at least one number.
    if (strlen($password) < 6 ||  !preg_match('/[0-9]/', $password)) {
        http_response_code(200);
        echo json_encode(array('error' => "The password must be at least 6 characters long, and have at least one number.", 'element-name' => "new-password-error"));
        exit();
    }

    // If the security code is valid, the password is updated.
    $password_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);
    $manager->update("User", "password", $password_hash, "security_code = '$security_code'");

    http_response_code(200);
    echo json_encode(array('success' => "Password updated successfully.", 'method' => 'GET', 'href' => "/reception/welcome"));
    exit();


