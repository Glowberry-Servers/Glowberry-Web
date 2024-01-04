<?php
    include $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/requests.php';
    
    $server_type = $_POST['server_type'];
    $html = "";
    
    foreach (getVersionsForType($server_type) as $version)
        $html .= "<option value='$version'>$version</option>";
    
    http_response_code(200);
    echo $html;
    exit();
