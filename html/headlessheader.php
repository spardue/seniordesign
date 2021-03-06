<?php
/*
   Header file for scripts that don't require a UI.
   Just does basic authentication
*/

require_once('../loginsystem/config/config.php');
require_once('../loginsystem/translations/en.php');
require_once('../loginsystem/libraries/PHPMailer.php');
require_once('../loginsystem/classes/Login.php');
require_once('../DBInteraction.php');
require_once('../util.php');

if (!isset($login)) {
    $login = new Login();

    if (!$login->isUserLoggedIn() == true) {
        echo "Access Denied.";
        die();
    }
}

if (!isset($db)) {
    $db = new DBInteraction();
    $conn = $db->getConn();
}

?>
