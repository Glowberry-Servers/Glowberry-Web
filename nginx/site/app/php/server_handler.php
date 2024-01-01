<?php
    
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/database_utils.php';
    include $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    
    /**
     * Gets a list of servers that the user has access to.
     * @param string $username The nickname of the user.
     *
     * @return array The list of servers that the user has access to.
     */
    function getServersForUser(string $username) : array
    {
        $manager = getManagerFromConfig();
        $all_results = $manager->selectAllWithCondition("ServerUser", "nickname = '$username'");
        
        $servers = array();
        foreach ($all_results as $result) {
            $result['name'] = $manager->selectWithCondition(array('name'), "Server", "id = " . $result['server_id'])[0]['name'];
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
            $html .= "
<div class='server-card' style='width: 98.5%; margin: 10px; background-color: #3e4d5b; color: #cfcfcf; box-shadow: 0 0 10px rgba(0, 0, 0, 0.25);'>
    <div class='server-card-left' style='display: flex; align-items: center; padding: 10px; box-sizing: border-box;'>
        <img src='" . $server['icon'] . "' alt='Server Icon' style='max-width: 70px; height: auto; margin-right: 15px;'>
        
        <div class='server-card-left-text' style='flex: 1;'>
            <h2 style='margin: 0; font-size: 1em;'>" . $server['name'] . "</h2>
            <p>Status: ". $server['status-html'] . "</p>
            <p>IP: " . $server['server-ip'] . "</p>
        </div>
        
        <div class='server-card-right-buttons' style='display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;'>
            <button id='". $server['server_id'] ."-start' class='server-card-right-button' style='padding: 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; border: none; background-color: #4caf50;'>
                Start<i class='bx bx-play' style='margin-left: 5px; font-size: 1.5em;'></i>
            </button>
            <button id='". $server['server_id'] ."-stop' class='server-card-right-button' style='padding: 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; border: none; background-color: #ff7043;'>
                Stop<i class='bx bx-stop' style='margin-left: 5px; font-size: 1.5em;'></i>
            </button>
            <button id='". $server['server_id'] ."-restart' class='server-card-right-button' style='padding: 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; border: none; background-color: #ffeb3b;'>
                Restart<i class='bx bx-reset' style='margin-left: 5px; font-size: 1.5em;'></i>
            </button>
            <button id='". $server['server_id'] ."-settings' class='server-card-right-button' style='padding: 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; border: none; background-color: #adadad;'>
                Manage<i class='bx bx-dots-horizontal-rounded' style='margin-left: 5px; font-size: 1.5em;'></i>
            </button>
            <button id='". $server['server_id'] ."-console' class='server-card-right-button' style='padding: 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; border: none; background-color: #adadad;'>
                Console<i class='bx bx-terminal' style='margin-left: 5px; font-size: 1.5em;'></i>
            </button>
            <button id='". $server['server_id'] ."-kill' class='server-card-right-button' style='padding: 8px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; border: none; background-color: #c7283a;'>
                Kill<i class='bx bx-x' style='margin-left: 5px; font-size: 1.5em;'></i>
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
