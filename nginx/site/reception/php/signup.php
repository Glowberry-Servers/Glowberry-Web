<?php
    // Checks if the method is POST.
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        http_response_code(405);
        exit();
    }
    
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/database_utils.php';

    // Gets the database manager from the config file and the passed parameters from the POST request.
    $manager = getManagerFromConfig();
    $username = htmlentities(strtolower($_POST["username"]));
    $password = htmlentities($_POST["password"]);
    $confirm = htmlentities($_POST["confirm_password"]);

    // Checks if the username is already taken.
    $results = $manager->selectWithCondition(array('user_tag'), "User", "user_tag = '$username'");
    
    if (count($results) != 0) {
        http_response_code(200);
        echo json_encode(array('error' => "This username has already been taken.", 'element-name' => "invalid-name-error"));
        exit();
    }

    // Checks if the username is 3 to 12 characters long, alphanumeric, and doesn't contain spaces.
    if (strlen($username) < 3 || strlen($username) > 12 || !ctype_alnum($username) || str_contains($username, ' ')) {
        http_response_code(200);
        echo json_encode(array('error' => "The username must be 3 to 15 characters long and alphanumeric.", 'element-name' => "invalid-name-error"));
        exit();
    }

    // Checks if the username is a valid windows directory name.
    $dirname = $_SERVER["DOCUMENT_ROOT"] . "/tmp/" . strtolower($username);
    mkdir($dirname, recursive: true);
    $handle = opendir($dirname);
    
    if (!$handle) {
        http_response_code(200);
        echo json_encode(array('error' => "This username cannot be used.", 'element-name' => "invalid-name-error"));
        exit();
    }
    
    closedir($handle);
    rmdir($dirname);
    rmdir(dirname($dirname));

    // Checks if the password and confirmation match.
    if ($password != $confirm) {
        http_response_code(200);
        echo json_encode(array('error' => "The passwords do not match.", 'element-name' => "passwords-no-match-error"));
        exit();
    }

    // Checks if the password is at least 6 characters long, and contains at least one number.
    if (strlen($password) < 6 || !preg_match('/[0-9]/', $password)) {
        http_response_code(200);
        echo json_encode(array('error' => "The password must be at least 6 characters long, and have at least one number.", 'element-name' => "invalid-password-error"));
        exit();
    }

    // If all checks pass, the user is added to the database.
    $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 10]);

    // Since the security code is special and needs to be unique, we keep generating it until we get one that doesn't exist in the database.
    do {
        $security_code = substr(base64_encode(mt_rand()), 0, 20);
        $existence_check = $manager->selectWithCondition(array('security_code'), "User", "security_code = '$security_code'");
    } while (count($existence_check) != 0);
    
    $manager->insertWhole("User", array($username, $hashed_password, $username, NULL, NULL, date('Y-m-d H:i:s'), "User", 5120, $security_code));
    
    http_response_code(200);
    echo json_encode(array('success' => "Signed up successfully.", 'method' => 'POST', 'href' => "/reception/php/security_code.php", 'security_code' => $security_code));
    
    $manager->getConnector()->close();
    exit();
