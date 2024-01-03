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
        return integerContainsPermission(getUserWebAppPermissions($username), $permission_integer);
    }
    
    /**
     * Gets the name of all the specified permissions existent within the given permission integer.
     *
     * @param string $table The table to get the permissions from.
     * @param int    $permission_integer The permission integer to be checked.
     *
     * @return array The permissions that are contained within the given permission integer.
     */
    function getAllPermissionNamesForInteger(string $table, int $permission_integer) : array
    {
        $permissions = array();
        
        // Handles the special cases of "No Permissions" and "All Permissions"
        // We don't need to return any other permissions in these cases.
        if ($permission_integer == 0) return array("No Permissions");
        if ($permission_integer == -1) return array("All Permissions");
        
        // Get all permissions from the database
        $manager = getManagerFromConfig();
        $webapp_permissions = $manager->selectAllWithoutCondition($table);
        $manager->getConnector()->close();
        
        // Iterate through all permissions checking if the target contains it
        foreach ($webapp_permissions as $permission)
        {
            if (integerContainsPermission($permission_integer, $permission['permissions_integer']))
                $permissions[] = $permission['permission_name'];
        }
        
        return $permissions;
    }
    
    /**
     * Returns a list of role names with the given permission integer or lower.
     * "lower" is defined as containing fewer permissions than the given permission integer but
     * not any that are not contained within the given permission integer.
     * @param int $permission_integer The permission integer to be checked.
     *
     * @return array The roles that have the given permission integer or lower.
     */
    function getAllRolesWithPermissionWithin(int $permission_integer): array {
        
        $roles = array();
        
        // Get all roles from the database
        $manager = getManagerFromConfig();
        $webapp_roles = $manager->selectAllWithoutCondition('Role');
        $manager->getConnector()->close();
        
        // Iterate through all roles checking if the target contains it
        foreach ($webapp_roles as $role)
        {
            if ($role['permissions_integer'] == 0) {
                $roles[] = $role['role_name'];
                continue;
            }
            
            if (integerContainsPermission($permission_integer, $role['permissions_integer']))
                $roles[] = $role['role_name'];
        }
        
        return $roles;
    }
    
    /**
     * Checks if the given user is above the given role or equal to it.
     * @param string $username The username of the user to be checked.
     * @param string $role_name The name of the role to be checked.
     *
     * @return bool Whether the user is above the given role.
     */
    function userIsAboveOrEqualToRole(string $username, string $role_name): bool {
        
        if ($username == 'admin') return true;
        
        $user_permissions = getUserWebAppPermissions($username);
        $role_permissions = getPermissionIntegerForRole($role_name);

        // A binary comparison with 0 will return a wrong result, so we need to handle it separately
        if ($user_permissions != 0 && $role_permissions == 0) return true;
        
        return integerContainsPermission($user_permissions, $role_permissions);
    }
    
    /**
     * Checks if the given user is above the given role, but not equal to it.
     * @param string $username The username of the user to be checked.
     * @param string $role_name The name of the role to be checked.
     *
     * @return bool Whether the user is above the given role.
     */
    function userIsAboveRole(string $username, string $role_name): bool {
        
        $isEqualOrAbove = userIsAboveOrEqualToRole($username, $role_name);
        $user_permissions = getUserWebAppPermissions($username);
        
        return $isEqualOrAbove && getPermissionIntegerForRole($role_name) != $user_permissions;
    }
    
    /**
     * Accesses the database and matches the given role name to the corresponding permission integer.
     * @param string $role_name The name of the role to get the permission integer for.
     *
     * @return int The permission integer for the specified role.
     */
    function getPermissionIntegerForRole(string $role_name): int {
        $manager = getManagerFromConfig();
        $permission_integer = $manager->selectWithCondition(array('permissions_integer'), 'Role', "role_name = '$role_name'");
        $manager->getConnector()->close();
        
        if (count($permission_integer) == 0) return 0;
        
        return $permission_integer[0]['permissions_integer'];
    }
    
    /**
     * Accesses the database and gets the role for the given tag, and matches it
     * to the corresponding permission integer.
     * @param string $username The username of the user to get the permission integer for.
     *
     * @return int The permission integer for the specified user.
     */
    function getUserWebAppPermissions(string $username): int {
        $manager = getManagerFromConfig();
        $user_role = $manager->selectWithCondition(array('role_name'), 'User', "user_tag = '$username'")[0]['role_name'];
        $manager->getConnector()->close();
        
        return getPermissionIntegerForRole($user_role);
    }

