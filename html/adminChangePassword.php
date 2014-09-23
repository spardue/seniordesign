<?php
/*
   Changes the admin password
*/
require_once('headlessheader.php');
if ($_SESSION['user_name'] !== "admin") {
    die();
}

if (count($login->errors) > 0) {
    echo "ERROR!!!";
    echo bootstrap_error_div($login->errors);
}


?>
