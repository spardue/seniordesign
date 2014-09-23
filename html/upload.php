<?php
/*
   Uploads a document to the database
  */
require('headlessheader.php');
try {

    //if no task to be associated with the doucment, insert it without it
    if (!isset($_POST["taskID"]) || $_POST["taskID"] === "" || $_POST["taskID"] == null || $_POST["taskID"] === "(None)") {
        $status = $db->interact(
            'INSERT INTO `Document` (`Name`, `CaseID`, `Data`, `Type`, `Size`, `UploadedBy`) VALUES (?, ?, ?, ?, ?, ?)',
            array(
                $_FILES["file"]["name"],
                $_POST["caseID"],
                file_get_contents($_FILES["file"]["tmp_name"], 'rb'),
                $_FILES["file"]["type"],
                $_FILES["file"]["size"],
                $_SESSION["user_name"]
            ));
    } else {
        //if a task is to be associated with the document, insert it with the task id
        $status = $db->interact(
            'INSERT INTO `Document` (`Name`, `CaseID`, `TaskID`, `Data`, `Type`, `Size`, `UploadedBy`) VALUES (?, ?, ?, ?, ?, ?, ?)',
            array(
                $_FILES["file"]["name"],
                $_POST["caseID"],
                $_POST["taskID"],
                file_get_contents($_FILES["file"]["tmp_name"], 'rb'),
                $_FILES["file"]["type"],
                $_FILES["file"]["size"],
                $_SESSION["user_name"]
            ));
    }
    if (!$status) {
        echo "Error, please return to viewCase.php.";
    } else {
        header("Location: viewCase.php?id=" . $_POST["caseID"] . "#documentsTab");
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    echo "Error, please return to viewCase.php.";
}
?>
</body>
</html>
