<?php
require('headlessheader.php');

// Ensure the given table name is valid
$tableName = $db->sanitizeTableName($_POST["TableName"]);

// Try to delete the given values from the given table
try {
    // Delete the object with the given ID from the given table
    $status = $db->interact(
        'DELETE FROM `' . $tableName . '` WHERE `ID` = :id',
        array('id' => $_POST["id"]));

    // If the delete statement was executed successfully, echo the primary key
    // of data that was just deleted for later use
    if ($status) {
        echo $conn->lastInsertId();
    } else {
        echo "ERROR!!!";
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo $e->getMessage();
    echo "ERROR!!!";
}
?>
