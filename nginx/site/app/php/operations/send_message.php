<?php
    
    include $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    
    $server_id = $_POST['server_uuid'];
    $message = $_POST['message'];
    
    sendMessageToServer($server_id, $message);
