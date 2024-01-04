<?php
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/server_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/requests.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/modals_creator.php';
    require $_SERVER["DOCUMENT_ROOT"] . '/app/php/operations/server_cleanup.php';
    
    $manager = getManagerFromConfig();
    $session_id = $_COOKIE['session_id'];
    $user = getUserInfoFromSession($manager, $session_id);
    $manager->getConnector()->close();
    
    /**
     * Builds the HTML options string to house the server types.
     * @return string The HTML options string.
     */
    function getHTMLForServerTypes() : string {
        
        $html = "";
        
        foreach (getSupportedServerTypes() as $type)
            $html .= "<option value='$type'>".ucwords($type)."</option>";
        
        return $html;
    }
    
    /**
     * Builds the HTML options string to house the installed java versions.
     * @return string The HTML options string.
     */
    function getHTMLForInstalledJavaVersions() : string {
        
        $html = "";
        
        foreach (getInstalledJavaVersions() as $version)
            $html .= "<option value='$version'>$version</option>";
        
        return $html;
    }
    
    /**
     * Gets the HTML options string to house the server versions for a given type.
     * @param string $server_type The server type to get the versions for.
     * @return string The HTML options string.
     */
    function getHTMLForServerOfType(string $server_type) : string {
        
        $html = "";
        
        foreach (getVersionsForType($server_type) as $version)
            $html .= "<option value='$version'>$version</option>";
        
        return $html;
    }
    
    /**
     * Gets the modal used to create servers, containing the server name, the selection for the server
     * type, the server version and the java runtime version.
     * @return string The HTML for the modal.
     */
    function getModalContentForServerCreation() : string {
        
        return "
<label for='server-name-input'>Server Name</label>
<input type='text' id='server-name-input'>
<label for='server-type-input'>Server Type</label>
<select id='server-type-input'>".
            getHTMLForServerTypes()."
</select>
<label for='server-version-input'>Server Version</label>
<select id='server-version-input'>".
            getHTMLForServerOfType('vanilla') ."
</select>
<label for='java-version-input'>Java Version</label>
<select id='java-version-input'>".
            getHTMLForInstalledJavaVersions()."
</select>
";
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Glowberry - Dashboard</title>

    <!-- Sets the favicon from glowberry's assets -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/app/css/dashboard.css">
    <link rel="stylesheet" href="/app/css/modal.css">
    <script type="module" src="/app/js/click_handlers.js"></script>
    <script type="module" src="/app/js/modal_handlers.js"></script>
    <script src="/app/js/search_handlers.js"></script>
    <script src='https://code.jquery.com/jquery-3.6.4.min.js'></script>
</head>

<body>
    
    <?php echo getHeaderFor($user) ?>
    <?php echo createModal('new-server-modal', 'Create Server', getModalContentForServerCreation(), 'column') ?>
    
    <div class="content">
        
        <div class="pretty-listing-options">

            <form id="search-bar">
                <i class='bx bx-search-alt-2'></i>
                <input id="servers-search-input" type="text" placeholder="Find servers by name">
                <div class="vertical-divider" id="search-divider"></div>
            </form>
            
            <button id="new-server-button" class="option-button">
                <i class='bx bx-folder-plus' ></i>
                New Server
            </button>
        </div>
        <hr>
        
        <div class="pretty-list">
            
            <?php
                $servers = getServersForUser($user['user_tag']);
                echo getHtmlForServerArray($servers)
            ?>
            
        </div>
    </div>
    

</body>
</html>
