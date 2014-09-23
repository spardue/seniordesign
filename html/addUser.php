<?php
/*
   Adds a user to the user database 
 */
require_once('headlessheader.php');
require_once('../loginsystem/classes/Registration.php');
$_POST["register"] = 1;
$registration = new Registration();
if (!$registration->registration_successful) {
    echo "ERROR!!!";
    echo bootstrap_error_div($registration->errors);
}
unset($_POST["register"]);
?>
