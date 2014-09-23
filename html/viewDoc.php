<?php
include("header.php");


/*gets the document row, case row and approvate task row */
$doc = $db->get1("SELECT * FROM `Document` WHERE `ID` = ?", array($_GET["id"]));
$case = $db->get1("SELECT * FROM `Case` WHERE `ID` = ?", array($doc["CaseID"]));
$task = $db->get1("SELECT * FROM `Tasks` WHERE `ID` = ?", array($doc["TaskID"]));



?>


<div class="container">
    <div class="page-header">
        <h2>Document: <strong> <?php echo $doc["Name"]; ?> </strong></h2>
    </div>
    <div class="container">
        <button type="button" class="btn btn-primary btn-lg"
                onclick="document.location ='download.php?id=<?php echo $doc["ID"] ?>'">Download
        </button>
    </div>
    <br>

    <div class="container">
        <table class="table table-bordered table-striped">
            <tr>
                <td><strong>Name:</strong></td>
                <td><?php echo $doc["Name"]; ?></td>
            </tr>
            <tr>
                <td><strong>Case:</strong></td>
                <td>
                    <?php
                    //provies a link to the Case associated with this document
                    echo "<a href='viewCase.php?id=" . $case["ID"] . "#documentsTab'>" . $case["Name"] . "</a>";
                    ?>
                </td>
            </tr>
            <tr>
                <td><strong>Task:</strong></td>
                <td>
                    <?php
                    //provies a link to the associated Task if it exists
                    if ($task) {
                        echo "<a href='viewTask.php?taskID=" . $task["ID"] . "'>" . $task["Name"] . "</a>";
                    } else {
                        echo "No Associated Task";
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td><strong>Uploading User:</strong></td>
                <td>
                    <?php
                    echo $doc["UploadedBy"];
                    ?>
                </td>
            </tr>
            <tr>
                <td><strong>Upload TimeStamp:</strong></td>
                <td>
                    <?php
                    echo $doc["UploadTimeStamp"];
                    ?>
                </td>
            </tr>
        </table>
    </div>
    <div class="container">
        <?php
        //ouputs the appropiate comments
        $comments = new Comments("doc-" . $doc["ID"], $db);
        $comments->show();
        ?>
    </div>
</div>

</body>
</html>
