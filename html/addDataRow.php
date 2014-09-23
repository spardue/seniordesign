<?php
require('headlessheader.php');


try { 
    $db->interact('INSERT INTO `'. $_POST["TableName"] .'`(`PersonID`) VALUES(?);', array($_POST["PersonID"]));
} catch (PDOException $e) {
    error_log($e->getMessage());
}

