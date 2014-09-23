<?php
require('header.php');

// Retrieve the case with the given ID as its primary key; in the rest of the
// comments, this case will be occasionally referred to as "this case"
$case = $db->interact("SELECT * FROM `Case` WHERE `ID` = :id",
    array('id' => $_GET["id"]))->fetch();

// Retrieve the claimant information associated with this case
$claimant = $db->interact("SELECT * FROM `Person` WHERE `ID` = :id",
    array('id' => $case["ClaimantID"]))->fetch();

// Retrieve the tasks associated with this case
$caseTasks = $db->interact("SELECT * FROM `Tasks` WHERE `CaseID` = :id",
    array('id' => $_GET["id"]))->fetchAll(PDO::FETCH_ASSOC);

// Determine the initial text and color to display in the status dropdown that
// indicated the status of this case
$caseStatus;
$caseStatusButtonStyle;
switch ($case["Status"]) {
    case "Active":
        $caseStatus = htmlspecialchars($case["Status"]);
        $caseStatusButtonStyle = "btn-success";
        break;
    case "Inactive":
        $caseStatus = htmlspecialchars($case["Status"]);
        $caseStatusButtonStyle = "label-default";
        break;
    case "Complete":
        $caseStatus = htmlspecialchars($case["Status"]);
        $caseStatusButtonStyle = "btn-default";
        break;
    default:
        $caseStatus = "Unknown";
        $caseStatusButtonStyle = "btn-warning";
}

// Determine the text to display in the (un)archive button and dialog. If the
// case is currently archived, the admin should be able to unarchive it;
// otherwise, assume the case is not currently archived and that the admin
// should be able to archive it
$archiveButtonText;
$archiveModalBody;
if ($case["Archived"] == 1) {
    $archiveButtonText = "Unarchive";
    $archiveModalBody = "Are you SURE you want to unarchive this case? If you click \"Unarchive Case\" below, this case will be accessible from the case list on the main page of this website.";
} else {
    $archiveButtonText = "Archive";
    $archiveModalBody = "Are you SURE you want to archive this case? If you click \"Archive Case\" below, this case will no longer be accessible from the case list on the main page of this website.";
}

/* A convenience method that will enclose the given string in the HTML tags for
 * bold
 *
 * $innerHTML - a string containing HTML
 */
function bold($innerHTML)
{
    return "<b>" . $innerHTML . "</b>";
}

/* A convenience method that will enclose the given string in the HTML tags for
 * a table column
 *
 * $innerHTML - a string containing HTML
 */
function newTableColumn($innerHTML)
{
    return "<td>" . $innerHTML . "</td>";
}

/* A convenience method that will enclose the given string in the HTML tags for
 * a table column and insert the given attributes into the opening tag
 *
 * $innerHTML - a string containing HTML
 * $attributes - an associative array of attribute names that identify
 *              non-quoted attribute values
 */
function newTableColumnWithAttributes($innerHTML, $attributes)
{
    $attributeString = "";

    foreach ($attributes as $key => $value) {
        $attributeString .= " $key=\"$value\"";
    }
    return "<td $attributeString>" . $innerHTML . "</td>";
}

/* A convenience method that will enclose the given string in the HTML tags for
 * a table row
 *
 * $innerHTML - a string containing HTML
 */
function newTableRow($innerHTML)
{
    return "
        <tr>
          " . $innerHTML . "
        </tr>";
}

/* A convenience method that will enclose the given string in the HTML tags for
 * a table row and insert the given attributes into the opening tag
 *
 * $innerHTML - a string containing HTML
 * $attributes - an associative array of attribute names that identify
 *              non-quoted attribute values
 */
function newTableRowWithAttributes($innerHTML, $attributes)
{
    $attributeString = "";

    foreach ($attributes as $key => $value) {
        $attributeString .= " $key=\"$value\"";
    }
    return "<tr $attributeString>" . $innerHTML . "</tr>";
}

/* A convenience method that creates and returns a table row containing the
 * given label followed by ": " in the first column and data, the given HTML
 * string, as a second column with the id attribute set as below. The table row
 * is intended to be displayed in the claimant contact information panel of the
 * overview tab on the page for this case.
 *
 * $label - a string to be used to indentify the data column and that will be
 *          displayed in the first column
 * $data - a string containing HTML
 */
function newContactInfoTableRow($label, $data)
{
    return newTableRow(
        newTableColumn($label . ": ")
        . newTableColumnWithAttributes($data,
            array("id" => "overview_" . str_replace(" ", "", $label))));
}

/* A convenience method that creates and returns a table row containing the
 * given label in the first column and a text input as a second column with
 * several attribute set as below. The table row is intended to be displayed in
 * the claimant information panel of the view/edit info tab on the page for this
 * case.
 *
 * $label - a string that will be displayed in the first column and indicates to
 *          the user what type of data is expected in the second column
 * $columnName - the identifier used internally to represent the text input in
 *          the second table column and set the input's initial value
 * $size - the length in characters of the input box
 */
function newClaimantDataRow($label, $columnName, $size=20)
{
    global $claimant;

    return newTableRow(
        newTableColumn($label)
        . newTableColumn("<input type=\"text\" class=\"PersonInput form-control\" "
            . "size=\"$size\""
            . "id=\"$columnName\" value=\""
            . htmlspecialchars($claimant[$columnName]) . "\" "
            . "disabled=\"disabled\">"));
}

function newDataRow($label, $tableName, $data, $columnName) {
    return newTableRow(
        newTableColumn($label)
        . newTableColumn("<input type=\"text\" class=\"$tableName"."Input form-control "
            . $tableName.$data['ID']."\""
            . "id=\"$columnName\" value=\""
            . htmlspecialchars($data[$columnName]) . "\" "
            . "disabled=\"disabled\">"),
            array("name" => $tableName . $data["ID"]));
}

function newDataRowDatePicker($label, $tableName, $data, $columnName)
{

    $date = htmlspecialchars($data[$columnName]); 
    return newTableRow(
        newTableColumn($label)
        . newTableColumn( 
            "<div class=\"input-group date\"" 
              ."data-date=\"$date\" data-date-format=\"yyyy-mm-dd\">"
              ."<input id=\"$columnName\" disabled='disabled'"
              ."class=\"$tableName"."Input $tableName" . $data['ID'] . " form-control\""
              ."size=\"16\" type=\"text\" value=\"$date\" readonly/>"
              ."<span class=\"input-group-addon\"><i class=\"glyphicon glyphicon-th\"></i></span></div>"),
            array("name" => $tableName . $data["ID"]));
} 

function newDataRowCheckbox($label, $tableName, $data, $columnName)
{

    $checked = ($data[$columnName] == 1) ? "checked" : "";

    return newTableRow(
        newTableColumn($label)
        . newTableColumn("<input type=\"checkbox\""
            . "class=\"$tableName"."Input $tableName".$data['ID']. "\""
            . "id=\"$columnName\" value=\""
            . htmlspecialchars($data[$columnName]) . "\" "
            . "disabled=\"disabled\" onClick=\"toggleClaimantCheckbox('$columnName')\" "
            . "$checked>"));
}

/* A convenience method that creates and returns a table row containing the
 * given label in the first column and a checkbox input as a second column with
 * several attribute set as below. The table row is intended to be displayed in
 * the claimant information panel of the view/edit info tab on the page for this
 * case.
 *
 * $label - a string that will be displayed in the first column and indicates to
 *          the user what type of data is expected in the second column
 * $columnName - the identifier used internally to represent the checkbox in
 *          the second table column and set the input's initial value
 */
function newClaimantDataRowCheckbox($label, $columnName)
{
    global $claimant;

    $checked = ($claimant[$columnName] == 1) ? "checked" : "";

    return newTableRow(
        newTableColumn($label)
        . newTableColumn("<input type=\"checkbox\" class=\"PersonInput \" "
            . "id=\"$columnName\" value=\""
            . htmlspecialchars($claimant[$columnName]) . "\" "
            . "disabled=\"disabled\" onClick=\"toggleClaimantCheckbox('$columnName')\" "
            . "$checked>"));
}

function newClaimantDataRowTextArea($label, $columnName, $rows=3, $columns=50)
{
    global $claimant;


    return newTableRow(
        newTableColumn($label)
        . newTableColumn("<textarea class=\"PersonInput form-control\" "
            . "id=\"$columnName\"" 
            . "rows=\"$rows\" cols=\"$columns\""
            . "disabled=\"disabled\">"
            . htmlspecialchars($claimant[$columnName]) 
            ."</textarea>"
            ));
}

function newClaimantDataRowDatePicker($label, $columnName)
{
    global $claimant;

    $date = htmlspecialchars($claimant[$columnName]); 
    return newTableRow(
        newTableColumn($label)
        . newTableColumn( 
            "<div class=\"input-group date\"" 
              ."data-date=\"$date\" data-date-format=\"yyyy-mm-dd\">"
              ."<input id=\"$columnName\" disabled='disabled' class=\"PersonInput form-control\" size=\"16\" type=\"text\" value=\"$date\" readonly/>"
              ."<span class=\"input-group-addon\"><i class=\"glyphicon glyphicon-th\"></i></span></div>"
        ));
}


/* Returns the default text to display in the status dropdown for a task.
 *
 * $taskStatus - a string to display as the default text of the dropdown; if the
 *              string does not match one of the preset statuses, "Unknown" is
 *              displayed instead
 */
function getTaskStatus($taskStatus)
{
    $confirmedTaskStatus;

    switch ($taskStatus) {
        case "Not Started":
        case "In Progress":
        case "Completed":
        case "Not Applicable":
            $confirmedTaskStatus = htmlspecialchars($taskStatus);
            break;
        default:
            $confirmedTaskStatus = "Unknown";
    }
    return $confirmedTaskStatus;
}

/* Returns the default color to display in the status dropdown for a task. The
 * color is determined using the task status
 *
 * $taskStatus - a string indicating the status of the task
 */
function getTaskStatusButtonStyle($taskStatus)
{
    $taskStatusButtonStyle;

    switch ($taskStatus) {
        case "Not Started":
            $taskStatusButtonStyle = "label-default";
            break;
        case "In Progress":
            $taskStatusButtonStyle = "btn-success";
            break;
        case "Completed":
            $taskStatusButtonStyle = "btn-default";
            break;
        case "Not Applicable":
            $taskStatusButtonStyle = "btn-default";
            break;
        default:
            $taskStatusButtonStyle = "btn-warning";
    }
    return $taskStatusButtonStyle;
}

/* Creates and returns a status dropdown for the given task. The default text
 * and color is set based on the task status.
 *
 * $task - an associative array representing a task
 */
function newTaskStatusDropdown($task)
{
    $taskID = $task["ID"];
    $taskStatus = getTaskStatus($task["Status"]);
    $taskStatusButtonStyle = getTaskStatusButtonStyle($taskStatus);

    return "
        <div class=\"btn-group\">
          <button id=\"taskStatusButton" . $taskID . "\" type=\"button\" "
    . "data-toggle=\"dropdown\"
            class=\"btn " . $taskStatusButtonStyle . " dropdown-toggle\">
            <span id=\"taskStatus" . $taskID . "\">" . $taskStatus . "</span>
              <span class=\"caret\"></span>
          </button>
          <ul class=\"dropdown-menu\" role=\"menu\">
            <li><a onClick=\"updateTaskStatus(" . $taskID . ", 'Not Started')\""
    . ">Not Started</a></li>
            <li><a onClick=\"updateTaskStatus(" . $taskID . ", 'In Progress')\""
    . ">In Progress</a></li>
            <li><a onClick=\"updateTaskStatus(" . $taskID . ", 'Completed')\""
    . ">Completed</a></li>
            <li><a onClick=\"updateTaskStatus(" . $taskID . ", 'Not Applicable')\""
    . ">Not Applicable</a></li>
            <li><a onClick=\"updateTaskStatus(" . $taskID . ", 'Unknown')\""
    . ">Unknown</a></li>
          </ul>
        </div>";
}

/* Echoes the given array as the string representation of a JSON object, with
 * single quotes and backslashes escaped
 *
 * $array - any array that can be passed to the standard json_encode() function
 */
function echoAsEscapedJSONObject($array)
{
    echo str_replace("'", "\\'", str_replace("\\", "\\\\", json_encode($array)));
}

?>
<div class="container">
<div class="page-header">
    <h2>
        Case: <strong><?php echo htmlspecialchars($case["Name"]); ?> </strong>
    </h2>
</div>
<h3>Status:
    <div id="caseStatusDropdown" class="btn-group">
        <button id="caseStatusButton" type="button" data-toggle="dropdown"
                class="btn <?php echo $caseStatusButtonStyle; ?>
        dropdown-toggle">
        <span id="caseStatus"
            ><?php echo htmlspecialchars($caseStatus); ?></span>
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
            <li><a>Active</a></li>
            <li><a>Inactive</a></li>
            <li><a>Complete</a></li>
            <li><a>Unknown</a></li>
        </ul>
    </div>
    <?php
        // Display the (un)archive button if the current user is the admin
        if ($_SESSION["user_name"] === "admin") { ?>
            <button id="archiveCaseButton" type="button" class="btn btn-warning"
                data-toggle="modal" data-target="#archiveCaseModal"
            ><?php echo $archiveButtonText; ?></button>
    <?php } ?>
</h3>
<br>

<h3>
    <ul id="tabs" class="nav nav-tabs nav-justified" role="tablist">
        <li class="active"><a href="#overviewTab" data-toggle="tab"
                              data-src="refreshOverview.php" data-target="#overview"
                              onClick="refresh(this)">Overview</a></li>
        <li><a href="#tasksTab" data-toggle="tab" data-src="tasksTab.php"
               data-target="#tasks">Tasks</a></li>
        <li><a href="#documentsTab" data-toggle="tab"
               data-src="documentsTab.php"
               data-target="#documents">Documents</a></li>
        <li><a href="#infoTab" data-toggle="tab" data-src="infoTab.php"
               data-target="#info">View/Edit Info</a></li>
    </ul>
</h3>
<div class="tab-content">
<div class="tab-pane active" id="overview">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="input-append"><b>Synopsis</b>

                <div class="btn-group btn-group-sm pull-right">
                    <button type="button" class="btn btn-primary"
                            title="Edit" id="editButton_CaseSynopsisInput"
                            onclick="editButton('CaseSynopsisInput', 'Case', saveCaseSynopsisToDB)">
	              <span class="glyphicon glyphicon-pencil"
                        id="editButtonImage_CaseSynopsisInput">
	              </span>
                    </button>
                    <button type="button" class="btn btn-primary"
                            title="Cancel" style="display:none;"
                            id="restoreButton_CaseSynopsisInput"
                            onclick="restoreButton('CaseSynopsisInput', 'Case', saveCaseSynopsisToDB)">
	              <span class="glyphicon glyphicon-remove">
	              </span>
                    </button>
                </div>
            </div>
        </div>
        <!-- end panel heading -->
        <textarea id="caseSynopsis" class="CaseSynopsisInput"
                  disabled="disabled" style="width:100%; cols:1; rows:1;
         max-width:100%; min-width:100%; webkit-box-sizing:border-box;
         moz-box-sizing:border-box; box-sizing:border-box;"
            ><?php echo htmlspecialchars($case["Synopsis"]); ?></textarea>
    </div>
    <!-- end panel -->
    <div class="panel panel-default">
        <div class="panel-heading"><b>Claimant Contact Information</b>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <tbody>
                <?php

                // Display the contact information for the claimant
                // associated with this case

                echo newContactInfoTableRow("Name",
                    htmlspecialchars($claimant["LastName"]) . ", "
                    . htmlspecialchars($claimant["FirstName"]));

                echo newContactInfoTableRow("Address",
                    htmlspecialchars($claimant["Address"]) . ", "
                    . htmlspecialchars($claimant["City"]) . ", "
                    . htmlspecialchars($claimant["State"]) . " "
                    . htmlspecialchars($claimant["ZIP"]));

                echo newContactInfoTableRow("Mailing Address",
                    htmlspecialchars($claimant["MailingAddress"]) . ", "
                    . htmlspecialchars($claimant["MailingCity"]) . ", "
                    . htmlspecialchars($claimant["MailingState"]) . " "
                    . htmlspecialchars($claimant["MailingZIP"]));

                echo newContactInfoTableRow("Home Phone",
                    htmlspecialchars($claimant["HomePhone"]));

                echo newContactInfoTableRow("Business Phone",
                    htmlspecialchars($claimant["BusinessPhone"]));

                echo newContactInfoTableRow("Cell Phone",
                    htmlspecialchars($claimant["CellPhone"]));

                echo newContactInfoTableRow("Fax",
                    htmlspecialchars($claimant["Fax"]));

                echo newContactInfoTableRow("Email",
                    htmlspecialchars($claimant["Email"]));

                ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- end panel -->
</div>
<!-- end overview tab pane -->
<div class="tab-pane" id="tasks">
    <div class="panel panel-default">
        <table class="table table-striped">
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Date Status Changed</th>
                <th>Open</th>
            </tr>
            <?php
            $db->each_row("SELECT * FROM TaskType LEFT JOIN Task ON TaskType.id = Task.TaskTypeID AND CaseID = ? ORDER BY TaskType.SortOrder;",
                array($_GET["id"]),
                function ($row) {
                    $caseID = $_GET["id"];
                    $taskTypeID = $row["id"];

                    echo "<tr>";

                    echo "<td>";
                    echo "<a href='viewTask.php?caseID=$caseID&taskTypeID=$taskTypeID'>" . $row["Name"] . "</a>";
                    echo "</td>";

                    echo "<td>";
                    echo newTaskStatusDropdown($row);
                    echo "</td>";

                    echo "<td>";
                    echo $row["DateStarted"];
                    echo "</td>";


                    echo "<td>";
                    echo "<button type='button' class='btn btn-primary' ";
                    echo "onclick='window.location=\"viewTask.php?caseID=$caseID&taskTypeID=$taskTypeID\"'>";
                    echo "<span class='glyphicon glyphicon-folder-open'></span>";
                    echo "</button>";
                    echo "</td>";


                    echo "</tr>";
                }
            );
            ?>
        </table>
    </div>
    <!-- end panel -->
</div>
<!-- end tasks tab pane -->
<div class="tab-pane" id="documents">
    <div class="panel panel-default">
        <div class="table-responsive table-striped">
            <table class="table">
                <tr>
                    <td><b>Document Information</b></td>
                    <td><b>Associated Task</b></td>
                    <td><b>Download</b></td>
                </tr>
                <?php
                $sql = "SELECT * FROM `Document` WHERE `CaseID` = :id";
                $db->each_row($sql, array('id' => $_GET["id"]),
                    function ($row) {
                        global $db;

                        $taskID = $row["TaskID"];
                        $docID = $row["ID"];

                        $associatedTaskResult = $db->interact(
                            "SELECT `Name` FROM `Tasks` WHERE `ID` = :id",
                            array("id" => $taskID))->fetch();
                        $associatedTask =
                            htmlspecialchars($associatedTaskResult["Name"]);

                        if ($associatedTask == null || $associatedTask == "") {
                            $associatedTask = "(None)";
                        } else {
                            $associatedTask = "<a href='viewTask.php?taskID=$taskID'>" . $associatedTask . "</a>";
                        }

                        $dlLink = "<button type='button' class='btn btn-primary'  onclick='document.location = \"download.php?id=$docID\"'>";
                        $dlLink .= "<span class='glyphicon glyphicon-download'></span>";
                        $dlLink .= "</button>";

                        echo newTableRow(
                            newTableColumn("<a href='viewDoc.php?id=$docID'>" . htmlspecialchars($row["Name"]) . "</a>")
                            . newTableColumn($associatedTask)
                            . newTableColumn($dlLink));
                    });
                ?>
            </table>
        </div>
    </div>
    <!-- end panel -->
    <br>

    <div class="panel panel-default">
        <div class="panel-heading"><b>Upload Document</b>
        </div>
        <!-- end panel heading -->
        <div class="panel-body">
            <?php if (!isset($_FILES["file"])) { ?>
                <form action="upload.php" method="post"
                      enctype="multipart/form-data">
                    <h5>Select a document to upload
                        .</h5>
                    <h5>All documents uploaded via this page are automatically associated with this case.</h5>
                    <br>
                    <table>
                        <tr>
                            <td><input type="file" name="file" id="file"></input>
                            </td>
                            <td><span>Associated Task: </span>

                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle"
                                            id="associatedTaskDropdown" type="button"
                                            data-toggle="dropdown">
                                        <span id="associatedTask">(None)</span>
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <?php
                                        echo "
                        <li><a onClick=\"setAssociatedTask('(None)')\""
                                            . ">(None)</a></li>";
                                        foreach ($caseTasks as $task) {
                                            $taskID = $task["ID"];
                                            $taskName = $task["Name"];
                                            echo "
                            <li><a onClick=\"setAssociatedTask('" . $taskID . "')\""
                                                . ">" . $taskName . "</a></li>";
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="hidden" name="caseID" value="<?php echo $_GET['id']; ?>"/></td>
                            <input type="hidden" id="associatedTaskID" name="taskID"/>
                        </tr>
                    </table>
                    <br>
                    <input onclick="alert($('#associatedTaskID').val())" type="submit" name="submit"
                           value="Submit"></input>
                </form>
            <?php } else { ?>
                <h1>File uploaded</h1>
                <br>
                <?php echo var_dump($_FILES);
            } ?>
        </div>
        <!-- end panel body -->
    </div>
    <!-- end panel -->
</div>
<!-- end documents tab pane -->
<div class="tab-pane" id="info">
<div class="panel panel-default">
<div class="panel-heading"><b>Claimant Information</b>

    <div class="btn-group btn-group-sm pull-right">
        <button type="button" class="btn btn-primary"
                title="Edit" id="editButton_PersonInput"
                onclick="editButton('PersonInput', 'Person', saveClaimantInfoToDB)">
		          <span class="glyphicon glyphicon-pencil"
                        id="editButtonImage_PersonInput">
	            </span>
        </button>
        <button type="button" class="btn btn-primary"
                title="Cancel" style="display:none;"
                id="restoreButton_PersonInput"
                onclick="restoreButton('PersonInput', 'Person', saveClaimantInfoToDB)">
			        <span class="glyphicon glyphicon-remove">
		          </span>
        </button>
    </div>
</div>
<!-- end panel heading -->
<div class="table-responsive">
    <table class="table table-striped">
        <tbody>
            <?php

            // Display the claimant information associated with this case that
            // is stored in the Person table

            echo newClaimantDataRow("First Name", "FirstName");
            echo newClaimantDataRow("Middle Name", "MiddleName");
            echo newClaimantDataRow("Last Name", "LastName");
            echo newClaimantDataRowTextArea("How They Found the Office",
                "HowFoundOffice");
            echo newClaimantDataRow("Alternate Contact",
                "NameOfAlternateContact");
            echo newClaimantDataRow("Relation to A.C.",
                "RelationToAlternateContact");
            echo newClaimantDataRow("Phone Number of A.C.",
                "PhoneOfAlternateContact");
            echo newClaimantDataRow("Address", "Address");
            echo newClaimantDataRow("City", "City");
            echo newClaimantDataRow("State", "State");
            echo newClaimantDataRow("Zip Code", "ZIP");
            echo newClaimantDataRow("Mailing Address", "MailingAddress");
            echo newClaimantDataRow("Mailing City", "MailingCity");
            echo newClaimantDataRow("Mailing State", "MailingState");
            echo newClaimantDataRow("Mailing Zip Code", "MailingZIP");
            echo newClaimantDataRow("Home Phone", "HomePhone");
            echo newClaimantDataRow("Business Phone", "BusinessPhone");
            echo newClaimantDataRow("Cell Phone", "CellPhone");
            echo newClaimantDataRow("Fax", "Fax");
            echo newClaimantDataRow("Email", "Email", 30);
            echo newClaimantDataRow("SSN", "SSN");
            echo newClaimantDataRowDatePicker("Date of Birth", "Birthdate");
            echo newClaimantDataRow("Birthplace", "Birthplace");
            echo newClaimantDataRow("Marital Status", "MaritalStatus");
            echo newClaimantDataRow("Marriage Date", "MarriageDate");
            echo newClaimantDataRow("Name of Spouse", "SpouseName");
            echo newClaimantDataRow("Divorce Date", "DivorceDate");
            echo newClaimantDataRow("State Where Divorced",
                "StateWhereDivorced");
            echo newClaimantDataRowCheckbox("Employed?", "Employed");
            echo newClaimantDataRow("Employer Name", "EmployerName");
            echo newClaimantDataRowTextArea("Reason for Unemployment",
                "ReasonForUnemployment");
            echo newClaimantDataRowCheckbox("Receiving SSDI/SSI?",
                "ReceivingSSDI/SSI");
            echo newClaimantDataRowCheckbox("Applied for SSI?", "AppliedForSSI");
            echo newClaimantDataRowCheckbox("Applied for SSDI?", "AppliedForSSDI");
            echo newClaimantDataRowCheckbox("Is a Veteran?", "IsVeteran");
            echo newClaimantDataRow("Veteran's Name", "VeteranName");
            echo newClaimantDataRowCheckbox("Military Retired?", "MilitaryRetired");
            echo newClaimantDataRowCheckbox("Military Medical Retired?",
                "MilitaryMedicalRetired");
            echo newClaimantDataRowCheckbox("Has PEB Disability Percentage?",
                "HasPEBDisabilityPercentage");
            echo newClaimantDataRow("Current VA Disability Percentage",
                "CurrentVADisabilityPercentage");
            echo newClaimantDataRow("Current Rating Disability Percentage",
                "CurrentRatingDisabilityPercentage");
            echo newClaimantDataRowCheckbox("Submitted SSDIB/SSI Application?",
                "SubmittedSSDIB/SSIApplication");
            echo newClaimantDataRow("Date Submitted SSDIB/SSI Application",
                "DateSubmittedSSDIB/SSIApplication");
            echo newClaimantDataRow("VARO", "VARO");
            echo newClaimantDataRow("VA File Number", "VAFileNumber");
            echo newClaimantDataRow("VA Decision Date", "VADecisionDate");
            echo newClaimantDataRowTextArea("Issues to Appeal", "IssuesToAppeal");
            echo newClaimantDataRow(
                "Date Notice of Disagreement Filed",
                "DateNoticeOfDisagreementFiled");
            echo newClaimantDataRowTextArea("Service Connection", "ServiceConnection");
            echo newClaimantDataRow("Percentage of Disability",
                "PercentageOfDisability");
            echo newClaimantDataRow("Unemployability", "Unemployability");
            echo newClaimantDataRowTextArea("Additional Information",
                "AdditionalInformation");


            function displayTable($db, $claimantID, $tableName, $title, $displayFn) {
                $tableInput = $tableName."Input";
                echo newTableRow(newTableColumn("<p id='$tableName'></p>") . newTableColumn(""));
                echo newTableRow(newTableColumn(bold("<p>$title:</p>"))
                    . newTableColumn('<div class="btn-group btn-group-sm pull-right">
                            <button type="button" class="btn btn-primary"
                              title="Edit" id="editButton_'.$tableInput.'"
                              onclick="editButton(\''.$tableInput.'\', \''.$tableName.'\', '
                        . 'saveAncillaryInfoToDB)">
                              <span class="glyphicon glyphicon-pencil"
                                id="editButtonImage_'.$tableInput.'">
                              </span>
                            </button>
                            <button type="button" class="btn btn-primary"
                              title="Cancel" style="display:none;"
                              id="restoreButton_'.$tableInput.'"
                              onclick="restoreButton(\''.$tableInput.'\', \''.$tableName.'\', '
                        . 'saveAncillaryInfoToDB)">
                              <span class="glyphicon glyphicon-remove">
                              </span>
                            </button>
                          </div>'));

                // Retrieve all dependants associated with this claimant
                $rows = $db->interact(
                    "SELECT * FROM `$tableName` WHERE `PersonID` = :id",
                    array('id' => $claimantID)
                )->fetchAll(PDO::FETCH_ASSOC);

                $inputButton = $tableInput."Button";
                // Display all of this claimant's dependants
                foreach ($rows as $row) {
                    $displayFn($row);

                    // Create the button for removing dependants via the UI
                    echo newTableRowWithAttributes(newTableColumn("")
                        . newTableColumn("<button type=\"button\" "
                            . "class=\"btn btn-danger $inputButton\" "
                            . "onClick=\"removeClaimantInfo('$tableName', "
                            . $row["ID"] . ");\" "
                            . "disabled=\"disabled\">Remove</button>"),
                        array("name" => "$tableName" . $row["ID"]));
                }

                // Create the button for adding dependants via the UI
                echo newTableRowWithAttributes(
                    newTableColumn('<button type="button" class="btn btn-primary '.$inputButton.'" disabled="disabled"
                              title="Add" id="addAncillaryInfo_'.$tableInput.'"
                              onclick="addNewDataRow(\''.$tableName.'\');"
                                    >Add</button>')
                    . newTableColumn(""), array("id" => "addButtonRow_$tableName"));
                return $rows;
            }

            $dependants = displayTable($db, $case["ClaimantID"], "Dependant", "Dependants", function ($row){
                echo newDataRow("Dependant", "Dependant", $row, "DependantName");
            }); 



            $disabilities = displayTable($db, $case["ClaimantID"], "Disability", "Disabilities", function ($row){
                echo newDataRow("Disability", "Disability", $row, 
                    "Disability");
                echo newDataRowCheckbox("Have they filed a claim?", "Disability", $row,
                    "HasFiledClaim");
                echo newDataRow("Claim Status", "Disability", $row,
                    "ClaimStatus");
                echo newDataRowDatePicker("Denial Date", "Disability", $row,
                    "DenialDate");
                echo newDataRow("Current Percentage", "Disability", $row,
                    "CurrentPercentage");
            }); 

            $thirdPartyTreatments = displayTable($db, $case["ClaimantID"], "ThirdPartyTreatment", "Third Party Treatment", function ($row){
                echo newDataRow(
                    "Name of Treatment Provider", "ThirdPartyTreatment", $row,
                    "TreatmentProvider");
                echo newDataRow("Provider Location", "ThirdPartyTreatment",
                    $row, "Location");
                echo newDataRowDatePicker("Date Treatment Began","ThirdPartyTreatment",
                    $row, "StartDate");
                echo newDataRowDatePicker("Date Treatment Ended","ThirdPartyTreatment",
                    $row, "EndDate");
                echo newDataRow("Diagnosis","ThirdPartyTreatment", $row,
                    "Diagnosis");
            }); 
            
            $VATreatments = displayTable($db, $case["ClaimantID"], "VATreatment", "VA Treatment", function ($row){
                echo newDataRow("Treatment Facility", "VATreatment", $row, "TreatmentFacility");
            }); 


            $militaryService = displayTable($db, $case["ClaimantID"], "MilitaryService", "Military Service", function ($row){
                echo newDataRow("Branch of Service", "MilitaryService",
                    $row, "BranchOfService");
                echo newDataRowDatePicker("Date Service Began", "MilitaryService",
                    $row, "StartDate");
                echo newDataRowDatePicker("Date Service Ended", "MilitaryService",
                    $row, "EndDate");
                echo newDataRow("MOS", "MilitaryService", $row, "MOS");
                echo newDataRow("Service Number", "MilitaryService", $row,
                    "ServiceNumber");
                echo newDataRow("Type of Discharge", "MilitaryService", 
                    $row, "TypeOfDischarge");
            }); 

            $wartimeService = displayTable($db, $case["ClaimantID"], "WartimeService", "Wartime Service", function ($row){
                echo newDataRow("War", "WartimeService", $row, "War");
                echo newDataRowDatePicker(
                    "Date Wartime Serivce Began", "WartimeService", $row,
                    "StartDateOfWartimeService");
                echo newDataRowDatePicker(
                    "Date Wartime Serivce Ended", "WartimeService", $row,
                    "EndDateOfWartimeService");
                echo newDataRow(
                    "Where Stationed During the War", "WartimeService", $row,
                    "WhereStationed");
                echo newDataRow("Unit", "WartimeService", $row, "Unit");
                echo newDataRow("Combat Medals (if any)", "WartimeService",
                    $row, "CombatMedals");
            }); 
            ?>
        </tbody>
    </table>
</div>
</div>
<!-- end panel -->
</div>
<!-- end info tab pane -->
</div>
<!-- end tab content -->
<div class="modal fade" id="archiveCaseModal" tabindex="-1" role="dialog" aria-labelledby="archiveCaseModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                        class="sr-only">Close</span></button>
                <h4 class="modal-title" id="archiveCaseModalLabel"
                    >Are you sure?</h4>
            </div>
            <div class="modal-body"><?php echo $archiveModalBody; ?></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger"
                        onClick="archiveCase(<?php echo $case['ID']; ?>)"
                    ><?php echo $archiveButtonText; ?> Case
                </button>
            </div>
        </div>
    </div>
</div>
<div id="caseComments">
    <?php
    $comments = new Comments("case" . $_GET["id"], $db);
    $comments->show();
    ?>
</div>
</div> <!-- end container -->


<script>
// Get the ID of the claimant associated with this case from PHP
var claimantID = <?php Print($claimant['ID']) ?>;
// Get the value indicating whether this case is currently archived from PHP
var isCaseArchived = <?php Print($case["Archived"]); ?>;

// Get all claimant information associated with this case that is not stored in
// the Person table from PHP
var ancillaryInfo = {};
ancillaryInfo["Dependant"] =
    jQuery.parseJSON('<?php echoAsEscapedJSONObject($dependants); ?>');
ancillaryInfo["Disability"] =
    jQuery.parseJSON('<?php echoAsEscapedJSONObject($disabilities); ?>');
ancillaryInfo["ThirdPartyTreatment"] =
    jQuery.parseJSON('<?php echoAsEscapedJSONObject($thirdPartyTreatments); ?>');
ancillaryInfo["VATreatment"] =
    jQuery.parseJSON('<?php echoAsEscapedJSONObject($VATreatments); ?>');
ancillaryInfo["MilitaryService"] =
    jQuery.parseJSON('<?php echoAsEscapedJSONObject($militaryService); ?>');
ancillaryInfo["WartimeService"] =
    jQuery.parseJSON('<?php echoAsEscapedJSONObject($wartimeService); ?>');

// A reference that lists all the columns in tables that store claimant
// information, except the Person table
var ancillaryColumns = {};
ancillaryColumns["Dependant"] = ["DependantName"];
ancillaryColumns["Disability"] = ["Disability", "HasFiledClaim", "ClaimStatus", "DenialDate", "CurrentPercentage"];
ancillaryColumns["ThirdPartyTreatment"] = ["TreatmentProvider", "Location", "StartDate", "EndDate", "Diagnosis"];
ancillaryColumns["VATreatment"] = ["TreatmentFacility"];
ancillaryColumns["MilitaryService"] = ["BranchOfService", "StartDate", "EndDate", "MOS", "ServiceNumber", "TypeOfDischarge"];
ancillaryColumns["WartimeService"] = ["War", "StartDateOfWartimeService", "EndDateOfWartimeService", "WhereStationed", "Unit", "CombatMedals"];

// A reference that lists human-readable descriptions for all the columns in
// tables that store claimant information, except the Person table
var columnDescriptions = {};
columnDescriptions["Dependant"] = {};
columnDescriptions["Dependant"]["DependantName"] = "Dependant";

columnDescriptions["Disability"] = {};
columnDescriptions["Disability"]["Disability"] = "Disability";
columnDescriptions["Disability"]["HasFiledClaim"] = "Have they filed a claim?";
columnDescriptions["Disability"]["ClaimStatus"] = "Claim Status";
columnDescriptions["Disability"]["DenialDate"] = "Denial Date";
columnDescriptions["Disability"]["CurrentPercentage"] = "Current Percentage";

columnDescriptions["ThirdPartyTreatment"] = {};
columnDescriptions["ThirdPartyTreatment"]["TreatmentProvider"] = "Name of Treatment Provider";
columnDescriptions["ThirdPartyTreatment"]["Location"] = "Provider Location";
columnDescriptions["ThirdPartyTreatment"]["StartDate"] = "Date Treatment Began";
columnDescriptions["ThirdPartyTreatment"]["EndDate"] = "Date Treatment Ended";
columnDescriptions["ThirdPartyTreatment"]["Diagnosis"] = "Diagnosis";

columnDescriptions["VATreatment"] = {};
columnDescriptions["VATreatment"]["TreatmentFacility"] = "VA Treatment Facility";

columnDescriptions["MilitaryService"] = {};
columnDescriptions["MilitaryService"]["BranchOfService"] = "Branch of Service";
columnDescriptions["MilitaryService"]["StartDate"] = "Date Service Began";
columnDescriptions["MilitaryService"]["EndDate"] = "Date Service Ended";
columnDescriptions["MilitaryService"]["MOS"] = "MOS";
columnDescriptions["MilitaryService"]["ServiceNumber"] = "Service Number";
columnDescriptions["MilitaryService"]["TypeOfDischarge"] = "Type of Discharge";

columnDescriptions["WartimeService"] = {};
columnDescriptions["WartimeService"]["War"] = "War";
columnDescriptions["WartimeService"]["StartDateOfWartimeService"] = "Date Wartime Serivce Began";
columnDescriptions["WartimeService"]["EndDateOfWartimeService"] = "Date Wartime Serivce Ended";
columnDescriptions["WartimeService"]["WhereStationed"] = "Where Stationed During the War";
columnDescriptions["WartimeService"]["Unit"] = "Unit";
columnDescriptions["WartimeService"]["CombatMedals"] = "Combat Medals (if any)";

<?php
if (isset($_GET["newData"])){
    $editButtonID = "#editButton_".$_GET["newData"]."Input";
    echo '$(\'#tabs a[href="#infoTab"]\').tab(\'show\');';
    echo "location.hash='".$_GET["newData"]."';";
    echo "$('$editButtonID').click();";
}

?>

/* Refreash the data of the overview tab in case it has changed since the page
 * was loaded
 *
 * tab - a tab on this Case page
 */
function refresh(tab) {
    var loadurl = $(tab).attr('data-src');
    var targ = $(tab).attr('data-target');

    if ($(tab).attr('href') == "#overviewTab") {
        $.get(loadurl + "?id=" + claimantID, function (data) {
            var jsonData = jQuery.parseJSON(data);
            var keys = Object.keys(jsonData);
            for (var i = 0; i < keys.length; ++i) {
                document.getElementById("overview_" + keys[i])
                    .innerHTML = jsonData[keys[i]];
            }
        });
    }
}

/* Save the current values of all HTML elements with the given class name
 *
 * className - a class assigned to HTML elements via the class attribute
 */
function saveInitData(className) {
    $("." + className).each(function (i, elem) {
        var input = $(elem);
        input.data("initialState", input.val().trim());
    });
}

/* Restore the saved values of all HTML elements with the given class name and
 * delete any data that was added after the last time data was saved
 *
 * className - a class assigned to HTML elements via the class attribute
 * tableName - the name of a table in the database
 */
function restore(className, tableName) {
    //Restore the saved values of all HTML elements with the given class name
    $("." + className).each(function (i, elem) {
        var input = $(elem);
        input.val(input.data("initialState"));
        input.prop('checked', input.val() == 1);
    });

    // Delete any data that was added after the last time data was saved
    if (newData[tableName] != null) {
        for (var i = 0; i < newData[tableName].length; i++) {
            var newElements = document.getElementsByName(newData[tableName][i]);
            while (newElements[0] != null) {
                $(newElements[0]).remove();
            }
        }
        delete newData[tableName];
    }
}

/* Called when an edit button is clicked. Unlocks all HTML input elements
 * associated with the given className and tableName for editing after saving
 * their values. Changes the edit button to a save button and displays the
 * cancel button. The given saveFunction will be called when the save button is
 * clicked.
 *
 * className - a class assigned to HTML elements via the class attribute
 * tableName - the name of a table in the database
 * saveFunction - a function to be called when the save button is clicked
 */
function editButton(className, tableName, saveFunction) {
    // Save initial values
    saveInitData(className);

    // Unlock input elements
    $("." + className).prop("disabled", false);
    $("." + className + "Button").prop("disabled", false);

    // Convert edit button to a save button
    $("#editButton_" + className)
        .attr("onclick", "saveButton('" + className + "', '" + tableName + "', " + saveFunction + ")");
    $("#editButton_" + className).attr("title", "Save");
    $("#editButtonImage_" + className)
        .attr("class", "glyphicon glyphicon-floppy-disk");

    // Display the cancel button
    $("#restoreButton_" + className).show();
}

/* Called when a save button is clicked. Locks all HTML input elements
 * associated with the given className and tableName from editing after saving
 * their values. Changes the save button to an edit button and hides the cancel
 * button. The given saveFunction will be called when the save button is
 * clicked.
 *
 * className - a class assigned to HTML elements via the class attribute
 * tableName - the name of a table in the database
 * saveFunction - a function to be called when the save button is clicked
 */
function saveButton(className, tableName, saveFunction) {
    // Lock input elements
    $("." + className).prop("disabled", true);
    $("." + className + "Button").prop("disabled", true);

    // Convert save button to an edit button
    $("#editButton_" + className)
        .attr("onclick", "editButton('" + className + "', '" + tableName + "', " + saveFunction + ")");
    $("#editButton_" + className).attr("title", "Edit");
    $("#editButtonImage_" + className).attr("class", "glyphicon glyphicon-pencil");

    // Hide the cancel button
    $("#restoreButton_" + className).hide();

    // Save values of the input elements
    saveFunction(className, tableName);
}

/* Called when a cancel button is clicked. Locks all HTML input elements
 * associated with the given className and tableName from editing after
 * restoring the values they had before they were last unlocked. Hides the
 * cancel button. The given saveFunction will be called when the save button is
 * clicked.
 *
 * className - a class assigned to HTML elements via the class attribute
 * tableName - the name of a table in the database
 * saveFunction - a function to be called when the save button is clicked
 */
function restoreButton(className, tableName, saveFunction) {
    // Restore initial values
    restore(className, tableName);

    // Lock input elements
    $("." + className).prop("disabled", true);
    $("." + className + "Button").prop("disabled", true);

    // Convert save button to an edit button
    $("#editButton_" + className)
        .attr("onclick", "editButton('" + className + "', '" + tableName + "', " + saveFunction + ")");
    $("#editButtonImage_" + className).attr("class", "glyphicon glyphicon-pencil");

    // Hide the cancel button
    $("#restoreButton_" + className).hide();
}

/* Saves the case synopsis from the overview tab to the database if the synopsis
 * has changed.
 *
 * className - a class assigned to HTML elements via the class attribute
 * tableName - the name of a table in the database
 */
function saveCaseSynopsisToDB(className, tableName) {
    var caseSynopsisElement = document.getElementById("caseSynopsis");
    var oldSynopsis = $(caseSynopsisElement).data("initialState");
    var newSynopsis = caseSynopsisElement.value.trim();

    // Only save the synopsis if it has changed
    if (oldSynopsis != newSynopsis) {
        var dataKV = {};
        dataKV["TableName"] = tableName;
        dataKV["id"] = window.location.search.replace("?id=", "");
        dataKV["Synopsis"] = newSynopsis;
        // Use the file below to do the actual saving
        $.post("updateVACaseSystemDB.php", dataKV,function (data) {
            // Restore the last known good data if an error occurs and notify
            // the user of the error
            if (data.indexOf("ERROR!!!") > -1) {
                restore(".caseSynopsisInput", tableName);
                setTimeout(function () {
                    $.blockUI({message: "Could not save case synopsis because of an error."
                    });
                    setTimeout($.unblockUI, 5000);
                }, 350);
            }
        }).always($.unblockUI);
    }
}

/* Saves the claimant information from the claimant information panel of the
 * view/edit info tab to the database.
 *
 * className - a class assigned to HTML elements via the class attribute
 * tableName - the name of a table in the database
 */
function saveClaimantInfoToDB(className, tableName) {
    var dataKV = {};
    dataKV["TableName"] = tableName;
    dataKV["id"] = claimantID;
    // Get the column values from the panel on the UI
    var inputElements = document.getElementsByClassName(className);
    for (var i = 0; i < inputElements.length; ++i) {
        var item = inputElements[i];
        dataKV[item.id] = item.value.trim();
    }
    // Use the file below to do the actual saving
    $.post("updateVACaseSystemDB.php", dataKV,function (data) {
        // Restore the last known good data if an error occurs and notify
        // the user of the error
        if (data.indexOf("ERROR!!!") > -1) {
            restore(className, tableName);
            setTimeout(function () {
                $.blockUI({message: "Could not save claimant data because of an error."
                });
                setTimeout($.unblockUI, 5000);
            }, 350);
        }
    }).always($.unblockUI);
}

/* Saves claimant information from the view/edit info tab that is not in the
 * claimant information panel to the database.
 *
 * className - a class assigned to HTML elements via the class attribute
 * tableName - the name of a table in the database
 */
function saveAncillaryInfoToDB(className, tableName) {
    // Delete hidden data (data that was removed while the UI was unlocked)
    if (hiddenDataRows != null) {
        var hiddenRows = hiddenDataRows[className];

        if (hiddenRows != null) { // if data for this class was removed
            for (var i = 0; i < hiddenRows.length; i++) {
                var objectID = hiddenRows[i].replace(tableName, "");

                // Remove UI data that was placed on the UI when the page was last
                // loaded
                if (objectID.indexOf("NEW") < 0) {
                    removeClaimantInfo(tableName, objectID);
                } else {
                    // Remove UI data that was added after the UI was last unlocked
                    // and removed from the UI before the UI was locked again
                    var hiddenEls = document.getElementsByName(hiddenRows[i]);
                    if (hiddenEls[0] != null) {
                        while (hiddenEls[0] != null) {
                            $(hiddenEls[0]).remove();
                        }
                    }

                }

                // Remove data backing the elements that were placed on the UI
                // when the page was last loaded
                for (var j = 0; j < ancillaryInfo[tableName].length; j++) {
                    if (ancillaryInfo[tableName][j]["ID"] == objectID) {
                        ancillaryInfo[tableName].splice(j, 1);
                        break;
                    }
                }
                // Remove data backing the elements that were placed on the UI
                // after the UI was last unlocked and removed from the UI before
                // the UI was locked again
                if (newData[tableName] != null) {
                    for (var j = 0; j < newData[tableName].length; j++) {
                        if (newData[tableName][j] == tableName + objectID) {
                            newData[tableName].splice(j, 1);
                            break;
                        }
                    }
                }
            }
            // Empty the data structure that is used to store hidden UI data for
            // the given className until it is deleted above
            delete hiddenDataRows[className];
        }
    }

    // Update data that was modified via the UI while the UI was unlocked
    for (var i = 0; i < ancillaryInfo[tableName].length; i++) {
        var dataKV = {};
        dataKV["TableName"] = tableName;
        dataKV["id"] = ancillaryInfo[tableName][i]["ID"];
        // Get the column values from the elements on the UI
        var inputElements = document.getElementsByClassName(className + " " + tableName + ancillaryInfo[tableName][i]["ID"]);
        for (var j = 0; j < inputElements.length; j++) {
            var item = inputElements[j];
            dataKV[item.id] = item.value.trim();
        }
        // Use the file below to do the actual updating
        $.post("updateVACaseSystemDB.php", dataKV,function (data) {
            if (data.indexOf("ERROR!!!") > -1) {
                // Restore the last known good data if an error occurs and notify
                // the user of the error
                restore(className, tableName);
                setTimeout(function () {
                    $.blockUI({message: "Could not save " + tableName + " data because of an error."
                    });
                    setTimeout($.unblockUI, 5000);
                }, 350);
                i = ancillaryInfo[tableName].length;
            }
        }).always($.unblockUI).success(location.reload);
        
    }

    // Save data that was added via the UI while the UI was unlocked and
    // replace the temporary identifiers for the UI elements with IDs from the
    // database
    if (newData[tableName] != null) {
        for (var i = 0; i < newData[tableName].length; i++) {
            saveAndReplace(className, tableName, i);
        }
        // All newData has been saved and the IDs replaces, so the data is no
        // longer new and can be deleted from the data structure for holding
        // new/temporary data
        delete newData[tableName];
    }
}

/* Saves claimant information from the view/edit info tab that is not in the
 * claimant information panel and was just added while the UI was unlocked to
 * the database.
 *
 * className - a class assigned to HTML elements via the class attribute
 * tableName - the name of a table in the database
 * i - the index of the new data in the data structure that holds new/temporary
 *      data that was just added to UI
 */
function saveAndReplace(className, tableName, i) {
    var dataKV = {};
    dataKV["TableName"] = tableName;
    dataKV["PersonID"] = claimantID;
    var initialID = newData[tableName][i];
    // Get the column values from the elements on the UI
    var inputElements = document.getElementsByClassName(className + " " + initialID);
    for (var j = 0; j < inputElements.length; j++) {
        var item = inputElements[j];
        dataKV[item.id] = item.value.trim();
    }
    // Use the file below to do the actual saving
    $.post("insertIntoVACaseSystemDB.php", dataKV,function (data) {
        // Restore the last known good data if an error occurs and notify
        // the user of the error; otherwise save and replace the data
        if (data.indexOf("ERROR!!!") > -1) {
            restore(className, tableName);
            setTimeout(function () {
                $.blockUI({message: "Could not save " + tableName + " data because of an error."
                });
                setTimeout($.unblockUI, 5000);
            }, 350);
        } else {
            // Parse the JSON object data returned from the database after the
            // new data was inserted
            data = jQuery.parseJSON(data);
            // Replace the temporary attributes for the new UI elements with
            // values from the database
            var elements = document.getElementsByName(initialID);
            while (elements[0] != null) {
                $(elements[0]).attr('name', tableName + data["ID"]);
            }
            var els = document.getElementsByClassName(className + " " + initialID);

            while (els[0] != null) {
                $(els[0]).addClass(tableName + data["ID"]);
                $(els[0]).removeClass(initialID);
            }
            var buttons = document.getElementsByClassName(className + "Button");
            for (var k = 0; k < buttons.length; k++) {
                var onClick = $(buttons[k]).attr('onclick');
                onClick = onClick.replace(initialID.replace(tableName, ""), data["ID"]);
                $(buttons[k]).attr('onclick', onClick);
            }
            // Add the new data to the data structure that stores the data
            // backing the UI
            ancillaryInfo[tableName].push(data);
        }
    }).always($.unblockUI);
}

/* Returns the classes to use to theme the status dropdown for a case.
 *
 * statusText - the status of the case
 */
function getCaseStatusButtonClasses(statusText) {
    var statusButtonClasses;

    switch (statusText) {
        case "Active":
            statusButtonClasses = "btn btn-success dropdown-toggle";
            break;
        case "Inactive":
            statusButtonClasses = "btn label-default dropdown-toggle";
            break;
        case "Complete":
            statusButtonClasses = "btn btn-default dropdown-toggle";
            break;
        default:
            statusButtonClasses = "btn btn-warning dropdown-toggle";
    }
    return statusButtonClasses;
}

/* Called when the case status dropdown is clicked. Sets the text and color of
 * the dropdown to reflect the new status text that was clicked and updates the
 * status in the database
 */
$("#caseStatusDropdown li a").click(function () {
    var caseStatusElement = document.getElementById("caseStatus");
    var oldStatus = caseStatusElement.innerHTML;
    var newStatus = $(this).text();

    // Do nothing if the new status is the same as the old
    if (oldStatus != newStatus) {
        document.getElementById("caseStatusButton").className =
            getCaseStatusButtonClasses(newStatus);
        caseStatusElement.innerHTML = newStatus;

        var dataKV = {};
        dataKV["TableName"] = "Case";
        dataKV["id"] = window.location.search.replace("?id=", "");
        dataKV["Status"] = newStatus;
        // Use the file below to do the actual saving
        $.post("updateVACaseSystemDB.php", dataKV,function (data) {
            // Restore the last known good data if an error occurs and notify
            // the user of the error
            if (data.indexOf("ERROR!!!") > -1) {
                document.getElementById("caseStatusButton").className =
                    getCaseStatusButtonClasses(oldStatus);
                caseStatusElement.innerHTML = oldStatus;
                setTimeout(function () {
                    $.blockUI({message: "Could not save case status because of an error."
                    });
                    setTimeout($.unblockUI, 5000);
                }, 350);
            }
        }).always($.unblockUI);
    }
});

/* Returns the classes to use to theme the status dropdown for a task.
 *
 * statusText - the status of the task
 */
function getTaskStatusButtonClasses(statusText) {
    var statusButtonClasses;

    switch (statusText) {
        case "Not Started":
            statusButtonClasses = "btn label-default dropdown-toggle";
            break;
        case "In Progress":
            statusButtonClasses = "btn btn-success dropdown-toggle";
            break;
        case "Completed":
            statusButtonClasses = "btn btn-default dropdown-toggle";
            break;
        case "Not Applicable":
            statusButtonClasses = "btn btn-default dropdown-toggle";
            break;
        default:
            statusButtonClasses = "btn btn-warning dropdown-toggle";
    }
    return statusButtonClasses;
}

/* Called when the task status dropdown is clicked. Sets the text and color of
 * the dropdown to reflect the new status text that was clicked and updates the
 * status in the database
 */
function updateTaskStatus(taskID, newStatus) {
    var taskStatusElement = document.getElementById("taskStatus" + taskID);
    var oldStatus = taskStatusElement.innerHTML;

    // Do nothing if the new status is the same as the old
    if (oldStatus != newStatus) {
        document.getElementById("taskStatusButton" + taskID).className =
            getTaskStatusButtonClasses(newStatus);
        taskStatusElement.innerHTML = newStatus;

        var dataKV = {};
        dataKV["TableName"] = "Task";
        dataKV["id"] = taskID;
        dataKV["Status"] = newStatus;
        // Use the file below to do the actual saving
        $.post("updateVACaseSystemDB.php", dataKV,function (data) {
            // Restore the last known good data if an error occurs and notify
            // the user of the error
            if (data.indexOf("ERROR!!!") > -1) {
                document.getElementById("taskStatusButton" + taskID).className =
                    getTaskStatusButtonClasses(oldStatus);
                taskStatusElement.innerHTML = oldStatus;
                setTimeout(function () {
                    $.blockUI({message: "Could not save task status because of an error."
                    });
                    setTimeout($.unblockUI, 5000);
                }, 350);
            }
        }).always($.unblockUI);
    }
}

/* Called when the associated task dropdown on the document upload form is
 * clicked. Sets the text of the dropdown to reflect the task that was clicked
 */
function setAssociatedTask(selectedTask) {
    document.getElementById("associatedTask").innerHTML = selectedTask;
    $("#associatedTaskID").val(selectedTask);
}

/* Archives the indicated case.
 *
 * caseID - the ID of the case to archive
 */
function archiveCase(caseID) {
    var dataKV = {};
    dataKV["TableName"] = "Case";
    dataKV["id"] = caseID;
    dataKV["Archived"] = (isCaseArchived == 1 ? 0 : 1);
    // Use the file below to do the actual saving
    $.post("updateVACaseSystemDB.php", dataKV,function (data) {
        if (data.indexOf("ERROR!!!") > -1) {
            // Notify the user of the error that occurred while saving
            setTimeout(function () {
                $.blockUI({message: "Could not archive case because of an error."
                });
                setTimeout($.unblockUI, 5000);
            }, 350);
        } else {
            // Redirect the user to the Cases page
            window.location = "index.php";
        }
    }).always($.unblockUI);
}

var hiddenDataRows = {};

/* Hides claimant information from the view/edit info tab that is not in the
 * claimant information panel
 *
 * className - a class assigned to HTML elements via the class attribute
 * tableName - the name of a table in the database
 * id - the id of the data that was just removed from the UI
 */
function hideAncillaryInfo(className, tableName, id) {
    var dataRows =
        document.getElementsByName(tableName + id);
    // Hide the UI elements
    for (var i = 0; i < dataRows.length; i++) {
        $(dataRows.item(i)).hide();
    }
    // Add the data backing the UI elements to a data structure so it can be
    // deleted/restored if the changes are saved/cancelled later
    if (hiddenDataRows[className] == null) {
        hiddenDataRows[className] = [];
    }
    hiddenDataRows[className].push(tableName + id);
}

/* Remove claimant information from the view/edit info tab that is in the
 * claimant information panel
 *
 * tableName - the name of a table in the database
 * id - the id of the data that was just removed from the UI
 */
function removeClaimantInfo(tableName, id) {
    var rm = confirm("Are you sure you want to delete this row?");
    if (rm == true){
        var dataKV = {};
        dataKV["TableName"] = tableName;
        dataKV["id"] = id;
        // Use the file below to do the actual removing from the database
        $.post("removeFromVACaseSystemDB.php", dataKV,function (data) {
            if (data.indexOf("ERROR!!!") > -1) {
                setTimeout(function () {
                    // Notify the user of the error that occurred while removing
                    $.blockUI({message: "Could not remove " + tableName + " info because "
                        + "of an error."
                    });
                    setTimeout($.unblockUI, 5000);
                }, 350);
            }
            else {
                var caseID = <?php echo $_GET['id'] ?>;
                window.location = "viewCase.php?id="+caseID+"&newData="+tableName;
            }
        }).always($.unblockUI);
    }
}

var newData = {};
var newDataID = 0;

/* Adds claimant information from the view/edit info tab that is not in the
 * claimant information panel
 *
 * className - a class assigned to HTML elements via the class attribute
 * tableName - the name of a table in the database
 */
function addAncillaryInfo(className, tableName) {
    var id = "NEW" + newDataID++; // a temporary ID for the new UI elements

    var addButtonRow = document.getElementById("addButtonRow_" + tableName);

    // Create a UI element for each column in the given table that will be
    // displayed
    for (var i = 0; i < ancillaryColumns[tableName].length; i++) {
        var newRow = document.createElement('tr');
        newRow.setAttribute('name', tableName + id);

        var column1 = document.createElement('td');
        column1.innerHTML =
            columnDescriptions[tableName][ancillaryColumns[tableName][i]]
        newRow.appendChild(column1);

        var column2 = document.createElement('td');
        var input = document.createElement('input');
        input.className = tableName + "Input " +" form-control "+ tableName + id; 
        var columnName = ancillaryColumns[tableName][i];
        input.id = columnName;

        if (columnName == "HasFiledClaim") {
            input.type = "checkbox";
            input.onclick = (function () {
                var checkbox = input;
                return function () {
                    toggleNewCheckbox(checkbox);
                }
            })();
        }
        else {
            input.type = "text";
        }

        column2.appendChild(input);
        newRow.appendChild(column2);
        addButtonRow.parentNode.insertBefore(newRow, addButtonRow);
    }
    // Add the new UI elements to the page
    var newRow = document.createElement('tr');
    newRow.setAttribute('name', tableName + id);

    var column1 = document.createElement('td');
    newRow.appendChild(column1);

    var column2 = document.createElement('td');
    var button = document.createElement('button');
    button.type = "button";
    button.className = "btn btn-danger " + tableName + "InputButton";
    button.setAttribute('onclick', "hideAncillaryInfo('" + tableName + "Input', '" +
        tableName + "', '" + id + "')");
    button.innerHTML = "Remove";

    column2.appendChild(button);
    newRow.appendChild(column2);
    addButtonRow.parentNode.insertBefore(newRow, addButtonRow);

    // Add the data backing the new UI elements to a data structure so it can be
    // saved/deleted if the changes are saved/cancelled later
    if (newData[tableName] == null) {
        newData[tableName] = [];
    }
    newData[tableName].push(tableName + id);
}

/* Toggles a checkbox for claimant information from the view/edit info tab that
 * is in the claimant information panel
 *
 * checkboxID - an identifier associated with a checkbox on the claimant
 *              information panel
 */


function toggleClaimantCheckbox(checkboxID) {
    var checkbox = document.getElementById(checkboxID);
    checkbox.value = (checkbox.value == 1) ? 0 : 1;
}

/* Toggles a checkbox for claimant information from the view/edit info tab that
 * is not in the claimant information panel and was just added while the section
 * was unlocked
 *
 * checkbox - a checkbox that was just added to a panel other than the claimant
 * information panel
 */
function toggleNewCheckbox(checkbox) {
    var checkbox = $(checkbox);
    console.log(checkbox.attr('id') + " " + checkbox.attr('checked') + " " + checkbox.val());
    checkbox.val((checkbox.val() == 1) ? 0 : 1);
    console.log(checkbox.attr('id') + " " + checkbox.attr('checked') + " " + checkbox.val());
}

/* Toggles a checkbox for claimant information from the view/edit info tab that
 * is not in the claimant information panel
 *
 * className - a class assigned to HTML elements via the class attribute
 * columnName - the name of a the column in the database associated with a
 *              checkbox
 */
function toggleAncillaryCheckbox(className, columnName) {
    var elems = document.getElementsByClassName(className);
    for (var i = 0; i < elems.length; i++) {
        console.log(columnName + " " + elems[i].id);
        if (columnName == elems[i].id) {
            elems[i].value = (elems[i].value == 1) ? 0 : 1;
            break;
        }
    }
}

function addNewDataRow(tableName) {
    var caseID = <?php echo $_GET['id'] ?>;
    var dataKV = {"TableName" : tableName, "PersonID" : claimantID};
    dataKV["TableName"] = tableName;
    $.post("addDataRow.php", dataKV , function (data) {
            window.location = "viewCase.php?id="+caseID+"&newData="+tableName;
        }
    );


} 

</script>
</body>
</html>
