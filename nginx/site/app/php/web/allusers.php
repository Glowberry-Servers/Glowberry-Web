<?php
    require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/session_handler.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/session_header.php';
    include_once $_SERVER["DOCUMENT_ROOT"] . '/app/php/web/header.php';
    
    $manager = getManagerFromConfig();
    $session_id = $_COOKIE['session_id'];
    $user = getUserInfoFromSession($manager, $session_id);
    $manager->getConnector()->close();
    
    /**
     * Returns the HTML string to be used in the dashboard to list every user registered in the database.
     *
     * @return string The HTML string to be used in the dashboard to list all users.
     */
    function getHtmlForAllUsers() : string {
        
        // Get all users from the database.
        $manager = getManagerFromConfig();
        $all_users = $manager->selectAllWithoutCondition("User");
        $manager->getConnector()->close();
        
        // Create the HTML string.
        $html = "";
        
        foreach ($all_users as $user) {
            
            $user['profile_picture'] = $user['profile_picture'] == null
                ? "https://raw.githubusercontent.com/Glowberry-Servers/Glowberry-Assets/master/images/user-default-profile-picture.png"
                : $user['profile_picture'];
            
            $html .= "
<div class='user user-card' style='width: 98.5%; margin: 10px; background-color: #3e4d5b; color: #cfcfcf; box-shadow: 0 0 10px rgba(0, 0, 0, 0.25); cursor: pointer'>
    <div class='user-card-left' style='display: flex; align-items: center; padding: 10px; box-sizing: border-box;'>
        <img src='" . $user['profile_picture'] . "' alt='User Profile Picture' style='max-width: 70px; height: auto; margin-right: 15px; border-radius: 10%;'>
        
        <div class='user-card-left-text' style='flex: 1;'>
            <h2 style='margin: 0; font-size: 1.5em;'>" . $user['display_name'] . "
                <span class='target user-card-tag' style='font-size: 0.6em; color: #adadad;'>@" . $user['user_tag'] . "</span>
            </h2>
            <p>Role: " . $user['role_name'] . "</p>
        </div>
    </div>
</div>
";
        }
        
        if ($html == "") {
            return "<h1 id='empty'>You should never be seeing this...<br/> Did someone delete all accounts!?</h1>";
        }
        
        return $html;
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Glowberry - Users</title>

    <!-- Sets the favicon from glowberry's assets -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="/app/css/dashboard.css">
    <script type="module" src="/app/js/click_handlers.js"></script>
    <script src="/app/js/search_handlers.js"></script>
</head>

<body>
    
    <?php echo getHeaderFor($user) ?>
    
    <div class="content">
        
        <div class="pretty-listing-options">

            <form id="search-bar">
                <i class='bx bx-search-alt-2' style="transform: translate(270px, 8px);"></i>
                <input id='users-search-input' style="width:280px" type="text" placeholder="Find users by tag (e.g @admin)">
                <div class="vertical-divider" id="search-divider" style="height: 32px;"></div>
            </form>
            
        </div>
    
        <hr>
        <div class="pretty-list"><?php echo getHtmlForAllUsers() ?></div>
    </div>
    

</body>
</html>
