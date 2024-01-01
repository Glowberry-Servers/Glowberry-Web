<?php
    
    include $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    
    /**
     * Performs a binary comparison between the given permission integer and the given permission value, and checks
     * if the target contains the value.
     * @param int $permission_target The permission integer to be checked against.
     * @param int $permission_value The permission integer that is to be checked against the target.
     *
     * @return bool Whether the target contains the value.
     */
    function integerContainsPermission(int $permission_target, int $permission_value) : bool
    {
        if ($permission_value == 0) return false;
        return ($permission_target & $permission_value) == $permission_value;
    }
    
    /**
     * Checks if the given user has the specified permission integer for the web application.
     * @param string $username           The username of the user to be checked.
     * @param int    $permission_integer The permission integer to be checked.
     *
     * @return bool Whether the user has the specified permission integer.
     */
    function userHasWebPermission(string $username, int $permission_integer) : bool
    {
        $manager = getManagerFromConfig();
        $user_permission = $manager->selectWithCondition(array('web_app_permissions_integer'), 'User', "nickname = '$username'")[0]['web_app_permissions_integer'];
        $manager->getConnector()->close();
        
        return integerContainsPermission($user_permission, $permission_integer);
    }
