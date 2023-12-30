<?php
	// Checks if the method is POST.
	if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
		http_response_code(405);
		exit();
	}
	
	require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
	include $_SERVER["DOCUMENT_ROOT"] . '/app/php/database_utils.php';
	
	// Gets the database manager that will be used to interact with the database.
	$manager = getManagerFromConfig();
	
	// Gets the username and password from the POST request.
	$username = $_POST['username'];
	$password = $_POST['password'];
	
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
	echo json_encode(array('success' => "Logged in successfully.", 'method' => 'POST', 'href' => "php/app/dashboard.php"));
	
	$manager->getConnection()->close();
	exit();