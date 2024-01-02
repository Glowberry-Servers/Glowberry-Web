<?php
    require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
    
    /**
     * Gets the HTML string for the header of the pages associated with the given user.
     * @param array $user The user to get the header for.
     *
     * @return string The HTML string for the header of the page.
     */
    function getHeaderFor(array $user) : string {
       
        $display_name = $user['display_name'];
        $profile_picture = $user['profile_picture'] == null
            ? 'https://raw.githubusercontent.com/Glowberry-Servers/Glowberry-Assets/master/images/user-default-profile-picture.png'
            : $user['profile_picture'];
        
        return "
<div class='menu-header'>

    <div class='vertical-divider' id='invisible'></div>

    <div class='menu-header-left'>
    
        <a id='logo-name-container' href='/app/php/web/dashboard.php'>
            <img id='logo' src='https://raw.githubusercontent.com/Glowberry-Servers/Glowberry-Assets/master/main/logo-webserver.png' alt='Glowberry Logo'>
            <h1>Glowberry Web</h1>
        </a>
        
    </div>
    
    <div class='menu-header-center'>
    
        <div class='menu-header-nav-button'>
            <i class='bx bxs-dashboard'></i>
            <a href='/app/php/web/dashboard.php'>Dashboard</a>
        </div>
        
        <div class='menu-header-nav-button'>
            <i class='bx bxs-user-detail' ></i>
            <a href='/app/php/web/allusers.php'>Users</a>
        </div>
        
        <div class='menu-header-nav-button'>
            <i class='bx bxs-check-shield' ></i>
            <a href='/app/php/web/permissions.php'>Permissions</a>
        </div>
    </div>
    
    <div class='menu-header-right'>
        
        
        <div class='menu-header-right-nav-button'>
            <i class='bx bxs-log-in' id='logout'><span>Log Out</span></i>
        </div>

        <div class='vertical-divider' id='profile-divider'></div>
        
        <a class='menu-header-right-nav-button' href='/app/php/web/user.php'>
            <p>$display_name</p>
            <img id='profile-picture' src='$profile_picture' alt='Profile Picture'>
        </a>
    </div>
    
</div>";
    }
