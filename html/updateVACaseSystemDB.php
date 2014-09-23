<?php
require('headlessheader.php');

// Ensure the given table name is valid
$tableName = $db->sanitizeTableName($_POST["TableName"]);

// Ensure each given column name is valid and format the column names and values
// as separate arrays for the later update statement
$colString = "";
$valArray = [];
foreach ($_POST as $key => $value) {
    $newValue;

    // Do not treat the table name parameter as a column
    if ($key != "TableName") {
        if ($value == "") {
            $newValue = null;
        } else {
            $newValue = $value;
        }
        $colString .= ",`" . $db->sanitizeColumnName($key) . "`=?";
        array_push($valArray, $newValue);
    }
}
$colString = substr($colString, 1);
array_push($valArray, $_POST["id"]);

// Try to update the given columns in the given table
try {
    // Create and execute the update statement. Use the interact() function in
    // DBInteraction.php to insert the column values into the update statement.
    // The interact() function performs parameter substitution in a way that
    // deters SQL injection; it then executes the prepared statement.
    $status = $db->interact(
        'UPDATE `' . $tableName . '` SET ' . $colString . ' WHERE `ID` = ?',
        $valArray);

    // If the update statement was executed successfully, echo the primary key
    // of data that was just updated for later use
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
