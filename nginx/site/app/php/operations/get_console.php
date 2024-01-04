<?php
    
    include $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/requests.php';
    
    $server_id = $_POST['server_uuid'];
    $limit = $_POST['limit'];
    $html = "";
    
    foreach(getConsoleForServer($server_id, $limit) as $line) {
        
        if ($line == "") continue;
        $html .= '<p>' . $line . '</p>';
    }
    
    clearOutputForServer($server_id);
    echo $html;
