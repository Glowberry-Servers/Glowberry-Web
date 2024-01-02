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
        if ($permission_value == 0 &&! $permission_target == 0) return false;
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
        $user_permission = $manager->selectWithCondition(array('web_app_permissions_integer'), 'User', "user_tag = '$username'")[0]['web_app_permissions_integer'];
        $manager->getConnector()->close();
        
        return integerContainsPermission($user_permission, $permission_integer);
    }
    
    /**
     * Gets the name of all the specified permissions existent within the given permission integer.
     *
     * @param string $table The table to get the permissions from.
     * @param int    $permission_integer The permission integer to be checked.
     *
     * @return array The permissions that are contained within the given permission integer.
     */
    function getAllPermissionsForInteger(string $table, int $permission_integer) : array
    {
        $permissions = array();
        
        // Get all permissions from the database
        $manager = getManagerFromConfig();
        $webapp_permissions = $manager->selectAllWithoutCondition($table);
        $manager->getConnector()->close();
        
        // Iterate through all permissions checking if the target contains it
        foreach ($webapp_permissions as $permission)
        {
            if (integerContainsPermission($permission_integer, $permission['permission_integer']))
                $permissions[] = $permission[''];
        }
        
        return $permissions;
    }

