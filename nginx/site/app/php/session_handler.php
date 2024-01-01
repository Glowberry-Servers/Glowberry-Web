<?php
    require_once $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/database_utils.php';
    
    use LaminariaCore\MySQLDatabaseManager;
    
    /**
     * Returns the entirety of the user's information from the database based on their session id.
     *
     * @param MySQLDatabaseManager $manager The database manager that will be used to interact with the database.
     * @param string               $session_id The session id of the user that will be used to get their information.
     *
     * @return array The information present on the User table for the user.
     */
    function getUserInfoFromSession(MySQLDatabaseManager $manager, string $session_id): array
    {
        
        $username = $manager->selectWithCondition(array('nickname'), "ApplicationSession", "session_id = '$session_id'")[0]['nickname'];
        $results = $manager->selectWithCondition(array("*"), "User", "nickname = '$username'");
        
        return $results[0];
    }
    
    /**
     * Ensures that the user is logged in and has a valid session.
     *
     * @param MySQLDatabaseManager $manager The database manager that will be used to interact with the database.
     * @param string               $cookie The session id cookie that will be used to check if the user is logged in.
     *
     * @return bool True if the user is logged in and has a valid session, false otherwise.
     */
    function sessionCheck(MySQLDatabaseManager $manager, string $cookie): bool
    {
        
        // If the user is not logged in, their session is not valid.
        if ($cookie == null) return false;
        
        $results = $manager->selectWithCondition(array('nickname'), "ApplicationSession", "session_id = '$cookie'");
        
        // If the user is logged in but does not have a session, their session is not valid.
        if (count($results) == 0) return false;
        $username = $results[0]['nickname'];
        
        // If the user is logged in, has a session, but the password does not match, their session is not valid.
        if (!isSessionPasswordValid($manager, $username)) return false;
        
        return true;
    }
    
    /**
     * Creates a new application session and binds it to the user in the database.
     *
     * @param MySQLDatabaseManager $manager The database manager that will be used to interact with the database.
     * @param string               $username The username of the user that will be bound to the session.
     * @param string               $password The password of the user that will be bound to the session.
     *
     * @return string The session id that was created.
     */
    function createNewSession(MySQLDatabaseManager $manager, string $username, string $password): string
    {
        $username = strtolower($username);
        
        // Deletes the user's current session if they have one.
        if (getCurrentSessionIdFor($manager, $username) != null) {
            $manager->deleteFrom("ApplicationSession", "nickname = '$username'");
        }
        
        // Creates a unique session id, checking if it is already in use to avoid that 0.0000001% chance of a collision.
        do {
            $session_id = generateSessionID(128);
            $results = $manager->selectWithCondition(array('session_id'), "ApplicationSession", "session_id = '$session_id'");
        } while (count($results) != 0);
        
        // Inserts the session id into the database.
        $manager->insertWhole("ApplicationSession", array($session_id, $username, $password));
        
        return $session_id;
    }
    
    /**
     * Creates a random session id with a given length.
     * @param int $length The length of the session id.
     *
     * @return string
     */
    function generateSessionID(int $length) : string {
        
        $session_id = '';
        
        while (strlen($session_id) < $length) {
            
            // Generates a random character, either a number or a lowercase letter, appending it to the session id.
            for ($i = 0; $i < 16; $i++)
                $session_id .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
            
            // Encodes the current session id in base64.
            $session_id = base64_encode($session_id);
        }
        
        // Trims the session id to the desired length, just in case it went over.
        return substr($session_id, 0, $length);
    }
    
    /**
     * Checks if the session ID applies to the user's current username and password.
     *
     * @param MySQLDatabaseManager $manager The database manager that will be used to interact with the database.
     * @param string               $username The username of the user that will be checked.
     *
     * @return bool True if the session is valid, false otherwise.
     */
    function isSessionPasswordValid(MySQLDatabaseManager $manager, string $username): bool
    {
        
        // Checks if the user has a session at all.
        $session_id = getCurrentSessionIdFor($manager, $username);
        if ($session_id == null) return false;
        
        // Checks if the session applies to the user's current username and password.
        $session_password = $manager->selectWithCondition(array('session_password'), "ApplicationSession", "nickname = '$username'")[0]["session_password"];
        $results = $manager->selectWithCondition(array('nickname'), "User", "nickname = '$username' AND password = '$session_password'");
        
        return count($results) != 0;
    }
    
    /**
     * Gets the current session id for the given user.
     *
     * @param MySQLDatabaseManager $manager The database manager that will be used to interact with the database.
     * @param string               $username The username of the user that will be checked.
     *
     * @return string|null The session id of the user or null if the user does not have a session.
     */
    function getCurrentSessionIdFor(MySQLDatabaseManager $manager, string $username): ?string
    {
        $results = $manager->selectWithCondition(array('session_id'), "ApplicationSession", "nickname = '$username'");
        return $results[0]['session_id'] ?? null;
    }
