<?php
    // Checks if the method is POST.
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        http_response_code(405);
        exit();
    }
    
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/database_utils.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';

    // Gets the database manager that will be used to interact with the database.
    $manager = getManagerFromConfig();

    // Gets the username and password from the POST request.
    $username = htmlentities($_POST['username']);
    $password = htmlentities($_POST['password']);
    $persistent = filter_var($_POST['persistent'], FILTER_VALIDATE_BOOLEAN);

    // Checks the username and hashed password against the database.
    $results = $manager->selectWithCondition(array('nickname', 'password'), "User", "nickname = '$username'");

    // If there are no results, the username and password are incorrect.
    if (count($results) == 0 || !password_verify($password, $results[0]['password'])) {
        http_response_code(200);
        echo json_encode(array('error' => "Invalid username or password.", 'element-name' => "login-error"));
        exit();
    }

    // If there are results, the username and password are correct.
    http_response_code(200);

    // Creates a new session for the user and sets the session id cookie.
    $session_id = createNewSession($manager, $username, $results[0]['password']);

    // If the user wants to stay logged in, the cookie will last for 3 days, otherwise, until the session ends.
    $cookie_lifetime = $persistent ? time() + (86400 * 3) : 0;
    setcookie("session_id", $session_id, $cookie_lifetime, "/");
    
    echo json_encode(array('success' => "Logged in successfully.", 'method' => 'POST', 'href' => "/app/php/dashboard.php"));
    exit();