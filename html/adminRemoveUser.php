<?php
/*
   Deletes a user

   */
include("headlessheader.php");

if ($_SESSION["user_name"] === "admin" && $_POST["userID"] !== -4191992) { //can't delete the admin
    try {
        $stmt = $db->interact("DELETE FROM login.users WHERE user_id = :user_id", array(":user_id" => $_POST["userID"]));
        $stmt->execute();
    } catch (Exception $e) {
        echo "ERROR!!!";
    }
}
?>
