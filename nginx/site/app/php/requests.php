<?php
    include $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    
    /**
     * Gets the URL of the web server API from the config file.
     * @return string The URL of the web server API.
     */
    function getWebServerApiUrlFromConfig() : string {
        
        $config_path = $_SERVER["DOCUMENT_ROOT"] . "/app_config.json";
        $config = json_decode(file_get_contents($config_path), true);
        
        return $config['glowberry_webserver_api'];
    }
    
    /**
     * Sends a GET request to the web server API to get the supported server types.
     * @return array The supported server types.
     */
    function getSupportedServerTypes() : array {
        
        $url = getWebServerApiUrlFromConfig() . "/server/types";
        $response = file_get_contents($url);
        
        if ($response === false)
            return array("Could not fetch server types from the web server API.");
        
        return json_decode($response, true)['types'];
    }
    
    /**
     * Sends a GET request to the web server API to get the supported server versions for a given type.
     *
     * @param string $server_type The server type to get the supported versions for.
     *
     * @return array The supported server versions for the given type.
     */
    function getVersionsForType(string $server_type) : array {
        
        $url = getWebServerApiUrlFromConfig() . "/server/versions";
        $query = http_build_query(array("type" => strtolower($server_type)));
        $response = file_get_contents($url . "?" . $query);
        
        if ($response === false)
            return array("Could not fetch server versions from the web server API.");
        
        return json_decode($response, true)['type-versions'];
    }
    
    /**
     * Sends many GET requests to the web server API to get the supported server versions for all types.
     * @return array The supported server versions for all types.
     */
    function getAllVersions() : array {
        
        $all_versions = array();
        
        foreach (getSupportedServerTypes() as $type) {
            $versions = getVersionsForType($type);
            $all_versions[$type] = $versions;
        }
        
        return $all_versions;
    }
    
    /**
     * Sends a GET request to the web server API to get the supported java versions.
     * @return array The supported java versions.
     */
    function getInstalledJavaVersions() : array {
        
        $url = getWebServerApiUrlFromConfig() . "/java-versions";
        $response = file_get_contents($url);
        
        if ($response === false)
            return array("Could not fetch installed java versions from the web server API.");
        
        return json_decode($response, true)['java-versions'];
    }
    
    /**
     * Sends a simple ping request to the web server API to check if it is online.
     *
     * @return bool
     */
    function pingServer() : bool {
        
        $url = getWebServerApiUrlFromConfig() . "/ping";
        $response = file_get_contents($url);
        
        if ($response === false)
            return false;
        
        return true;
    }
    
    /**
     * Sends a POST request to the web server API to build a server.
     *
     * @param string $server_uuid The name of the server to build.
     * @param string $server_type The type of the server to build.
     * @param string $server_version The version of the server to build.
     * @param string $server_java_version The java version to use for the server.
     *
     * @return array The response from the web server API.
     */
    function buildServer(string $server_uuid, string $server_type, string $server_version, string $server_java_version) : bool
    {
        
        $url = getWebServerApiUrlFromConfig() . "/server/build";
        $data = array(
            
            "server_id" => $server_uuid,
            "type" => $server_type,
            "version" => $server_version,
            "java" => $server_java_version
        );
        
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        return $response !== false;
    }
    
    /**
     * Sends a POST request to the web server API to start a server.
     *
     * @param string $server_uuid The name of the server to start.
     *
     * @return array The response from the web server API.
     */
    function startServer(string $server_uuid) : bool {
        
        $url = getWebServerApiUrlFromConfig() . "/server/start";
        $data = array(
            "server_id" => $server_uuid
        );
        
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        return $response !== false;
    }
    
    /**
     * Sends a POST request to the web server API to delete a server.
     * @param string $server_uuid The name of the server to delete.
     *
     * @return bool Whether the server was deleted successfully.
     */
    function deleteServer(string $server_uuid) : bool {
        
        $url = getWebServerApiUrlFromConfig() . "/server/delete";
        $data = array(
            "server_id" => $server_uuid
        );
        
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        return $response !== false;
    }
    
    /**
     * Sends a POST request to the web server API to send a message to a server.
     * @param string $server_uuid The name of the server to send the message to.
     * @param string $message The message to send to the server.
     *
     * @return bool
     */
    function sendMessageToServer(string $server_uuid, string $message) : bool {
        
        $url = getWebServerApiUrlFromConfig() . "/server/send-message";
        $data = array(
            "server_id" => $server_uuid,
            "message" => $message
        );
        
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        return $response !== false;
    }
    
    
    /**
     * Sends a GET request to the web server API to get the console for a server.
     * @param string $server_uuid The name of the server to get the console for.
     * @param int    $limit      The maximum number of lines to get from the console.
     *
     * @return array The console for the server.
     */
    function getConsoleForServer(string $server_uuid, int $limit) : array {
        
        $url = getWebServerApiUrlFromConfig() . "/server/output";
        $query = http_build_query(array(
            "server_id" => $server_uuid,
            "lines" => $limit
        ));
        
        $response = file_get_contents($url . "?" . $query);
        
        if ($response === false)
            return array("Could not fetch console from the web server API.");
        
        return json_decode($response, true)['output'];
    }
    
    /**
     * Clears the output buffer in the API so that we don't get overlapping messages.
     * @param string $server_uuid The name of the server to clear the console for.
     *
     * @return bool Whether the console was cleared successfully.
     */
    function clearOutputForServer(string $server_uuid) : bool {
        
        $url = getWebServerApiUrlFromConfig() . "/server/clear-output";
        $data = array(
            "server_id" => $server_uuid
        );
        
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        return $response !== false;
    }
    
    
    /**
     * Sends a GET request to the web server API to get the build state for a server.
     * @param string $server_uuid The name of the server to get the build state for.
     *
     * @return string The build state for the server.
     */
    function getServerBuildState(string $server_uuid) : string {
        
        $url = getWebServerApiUrlFromConfig() . "/check/build";
        $query = http_build_query(array(
            "server_id" => $server_uuid
        ));
        
        $response = file_get_contents($url . "?" . $query);
        
        if ($response === false)
            return false;
        
        return json_decode($response, true)['state'];
    }
    
    /**
     * Gets the server information from the web server API.
     *
     * @param string $server_uuid The name of the server to get the information for.
     *
     * @return array|false| mixed The server information or false if the request failed.
     */
    function getServerInformation(string $server_uuid)  {
        
        $url = getWebServerApiUrlFromConfig() . "/server/info";
        $query = http_build_query(array(
            "server_id" => $server_uuid
        ));
        
        $response = file_get_contents($url . "?" . $query);
        
        if ($response === false)
            return false;
        
        return json_decode($response, true);
    }
    
    /**
     * Checks if the specified server is running.
     * @param string $server_uuid The name of the server to check.
     *
     * @return bool Whether the server is running.
     */
    function isServerRunning(string $server_uuid) : bool {
        
        $url = getWebServerApiUrlFromConfig() . "/check/running";
        $query = http_build_query(array(
            "server_id" => $server_uuid
        ));
        
        $response = file_get_contents($url . "?" . $query);
        
        if ($response === false)
            return false;
        
        return json_decode($response, true)['running'];
    }
    
    
    
    