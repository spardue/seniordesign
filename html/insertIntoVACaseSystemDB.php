<?php
require('headlessheader.php');

// Ensure the given table name is valid
$tableName = $db->sanitizeTableName($_POST["TableName"]);

$colString = "";
$valString = "";
$valArray = [];

// Ensure each given column name is valid and format the column names and values
// as separate arrays for the later insert statement
foreach ($_POST as $key => $value) {
    $newValue;

    // Do not treat the table name parameter as a column
    if ($key != "TableName") {
        if ($value == "") {
            $newValue = null;
        } else {
            $newValue = $value;
        }
        $colString .= ",`" . $db->sanitizeColumnName($key) . "`";
        $valString .= ", ?";
        array_push($valArray, $newValue);
    }
}
$colString = substr($colString, 1);
$valString = substr($valString, 1);

// Try to insert the given values into the given table
try {
    // Create and execute the insert statement. Use the interact() function in
    // DBInteraction.php to insert the column values into the insert statement.
    // The interact() function performs parameter substitution in a way that
    // deters SQL injection; it then executes the prepared statement.
    $status = $db->interact(
        'INSERT INTO `' . $tableName . '` (' . $colString . ') VALUES (' . $valString . ')',
        $valArray);

    // If the insert statement was executed successfully, encode the data that
    // was just inserted in JSON format and echo it for later use
    if ($status) {
        $data = $db->interact("SELECT * FROM `" . $tableName . "` WHERE `ID` = :id",
            array("id" => $conn->lastInsertId()))->fetch();
        echo json_encode($data);
    } else {
        echo "ERROR!!!";
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    echo "ERROR!!!";
}
?>
