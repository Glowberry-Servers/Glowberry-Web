<?php
    
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/database_utils.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/permissions_handler.php';
    
    /**
     * The status of the server as an HTML string.
     * @param string $server_uuid The UUID of the server.
     *
     * @return string The status of the server as an HTML string.
     */
    function getStatusHtml(string $server_uuid) : string {
        
        $isRunning = isServerRunning($server_uuid);
        
        return $isRunning ?
            "<span style='color: var(--greens);'>Online</span>" :
            "<span style='color: var(--reds);'>Offline</span>";
    }
    
    /**
     * Gets a list of servers that the user has access to.
     * @param string $username The user_tag of the user.
     *
     * @return array The list of servers that the user has access to.
     */
    function getServersForUser(string $username) : array
    {
        $manager = getManagerFromConfig();
        
        if (!userHasWebPermission($username, 32)) {
            $condition = "user_tag = '$username'";
        }
        else $condition = "permissions_integer = -1";
        
        $all_results = $manager->selectAllWithCondition("ServerUser", $condition);
        $servers = array();
        
        // Adds the information from the database into the array.
        foreach ($all_results as $result) {
            $result['name'] = $manager->selectWithCondition(array('name'), "Server", "server_uuid = '{$result['server_uuid']}'")[0]['name'];
            
            // Adds the information from the server_info.xml file into the array.
            $result = array_merge($result, getServerInformation($result['server_uuid']));
            
            // Adds the icon url to the array.
            $result['icon'] = "https://raw.githubusercontent.com/Glowberry-Servers/Glowberry-Assets/master/images/"
                . explode(" ", $result["type"])[0] . ".png";
            
            $result['status-html'] = getStatusHtml($result['server_uuid']);
            
            $servers[] = $result;
        }
        
        
        $manager->getConnector()->close();
        return $servers;
    }
    
    /**
     * Returns the HTML string to be used in the dashboard to list all the servers that the user has access to.
     * @param array $servers The array of servers returned by getServersForUser().
     *
     * @return string The HTML string to be used in the dashboard to list all the servers that the user has access to.
     */
    function getHtmlForServerArray(array $servers) : string {
        
        $html = "";
        foreach ($servers as $server) {
            
            if (getServerBuildState($server['server_uuid']) != "success") continue;
            $server_ip_display = $server['server-ip'] != "" ? $server['server-ip'] : "Unresolved";
            $version_display = ucwords($server['type']) ." ". $server['version'];
            
            $html .= "
<div class='server-card' style='margin: 10px; background-color: #3e4d5b; color: #cfcfcf; box-shadow: 0 0 10px rgba(0, 0, 0, 0.25);'>
    <div class='server-card-left' style='display: flex; align-items: center; padding: 10px; box-sizing: border-box;'>
        <img src='" . $server['icon'] . "' alt='Server Icon' style='max-width: 70px; height: auto; margin-right: 15px;'>
        
        <div class='server-card-left-text' style='flex: 1;'>
            <h2 class='server-card-name' style='margin: 0; font-size: 1em;'>" . $server['name'] . "</h2>
            <p>Status: ". $server['status-html'] . "</p>
            <p>IP: " . $server_ip_display  . "</p>
            <p>Version: " . $version_display ."</p>
            <p>Owner: " . getServerOwner($server['server_uuid']) . "</p>
        </div>
        
        <div class='server-card-right-buttons' style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; '>
            <button class='server-card-right-button' style='padding: 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; border: none; background-color: #4caf50;'>
                Start<i class='bx bx-play' style='margin-left: 5px; font-size: 1.5em;'></i>
            </button>
            <button class='server-card-right-button' style='padding: 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; border: none; background-color: #ff7043;'>
                Stop<i class='bx bx-stop' style='margin-left: 5px; font-size: 1.5em;'></i>
            </button>
            <button class='server-card-right-button' style='padding: 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; border: none; background-color: #ffeb3b;'>
                Restart<i class='bx bx-reset' style='margin-left: 5px; font-size: 1.5em;'></i>
            </button>
            <button class='server-card-right-button' style='padding: 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; border: none; background-color: #adadad;'>
                Manage<i class='bx bx-dots-horizontal-rounded' style='margin-left: 5px; font-size: 1.5em;'></i>
            </button>
            <button class='server-card-right-button' style='padding: 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; border: none; background-color: #adadad;'>
                Console<i class='bx bx-terminal' style='margin-left: 5px; font-size: 1.5em;'></i>
            </button>
            <button class='server-card-right-button server-delete-button' style='padding: 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; border: none; background-color: #c7283a;'>
                Delete<i class='bx bx-x' style='margin-left: 5px; font-size: 1.5em;'></i>
            </button>
        </div>
    </div>
</div>
";
        }
        
        if ($html == "") {
            return "<h1 id='empty'>It's pretty empty in here...<br/> Should we create some servers?</h1>";
        }
        
        return $html;
    }
    
    /**
     * Returns the user tag of the owner of the server (The one with the -1 permission on it.)
     * @param string $server_uuid The UUID of the server.
     *
     * @return string The user tag of the owner of the server.
     */
    function getServerOwner(string $server_uuid) : string {
        
        $manager = getManagerFromConfig();
        $result = $manager->selectWithCondition(array('user_tag'), "ServerUser", "server_uuid = '$server_uuid' AND permissions_integer = -1")[0]['user_tag'];
        $manager->getConnector()->close();
        
        return $result;
    }
