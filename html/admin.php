<?php
/*
   Admin interface UI
*/
require('header.php');
require_once('../loginsystem/classes/Registration.php');

if ($_SESSION["user_name"] !== "admin") {
    echo "You don't have access to this page.";
    die();
}
//switch to the login system database
$db->getConn()->exec('USE login;');
?>

<div class="container">
<h1>Admin Interface</h1>
<br>

<div class="panel panel-default">
    <div class="panel-heading">User Control</div>
    <p>
        <?php
        //Show add user success
        if (isset($_GET["newUser"])) {
            echo "<div width='75%' class='alert alert-success alert-dismissible' role='alert'>";
            echo "New user " . $_GET['newUser'] . " added successfuly.";
            echo "</div>";
        }
        ?>
    </p>

    <div id="addUserContent" class="panel-body">
        <button data-toggle="modal" data-target="#addUser" type="button" class="btn btn-primary">Add User</button>
        <br>
        <br>
        <table class="table">
            <tr>
                <th>ID</th>
                <th>User name</th>
                <th>Email</th>
                <th>Options</th>
            </tr>

            <?php
            //shows the user table existing in the database
            $db->each_row('SELECT * FROM `users`', null, function ($row) {
                $userID = $row['user_id'];
                $userName = $row['user_name'];
                echo "<tr>";

                echo "<td>" . $row['user_id'] . "</td>";

                echo "<td>" . $row['user_name'] . "</td>";

                echo "<td>" . $row['user_email'] . "</td>";

                echo "<td>";
                echo "<div class='btn-group'>";
                echo "<button type='button' class='btn btn-primary' title='Edit' onclick='editUser($userID)'>";
                echo "<span class='glyphicon glyphicon-pencil'></span>";
                echo "</button>";
                echo "<button type='button' class='btn btn-danger' onclick='deleteUser($userID, \"$userName\")'><span class='glyphicon glyphicon-remove'/></button>";
                echo "</div>";
                echo "</td>";
                echo "</tr>";
            });
            ?>
        </table>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">Change Admin Password</div>
    <div id="adminChangePassword" class="panel-body">
        <p id="changeAdminPasswordErrors"></p>

        <form id="changePasswordForm" name="user_edit_submit_password" value="1" role="form">
            <div class="form-group">
                <label for="user_password_old">Old Password</label>
                <input id="user_password_old" type="password" name="user_password_old" autocomplete="off"/>
            </div>

            <div class="form-group">
                <label for="user_password_new">New Password</label>
                <input id="user_password_new" type="password" name="user_password_new" autocomplete="off"/>
            </div>

            <div class="form-group">
                <label for="user_password_repeat">Repeat New Password</label>
                <input id="user_password_repeat" type="password" name="user_password_repeat" autocomplete="off"/>
            </div>
            <div class="form-group">
                <button id="changePasswordButton" type="button" class="btn btn-primary">Change Password</button>
            </div>
        </form>
    </div>
</div>


<div class="modal fade" id="editUser" tabindex="-2" role="dialog" aria-labelledby="editUserLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="editUserLabel">Edit User</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editUserID"/>

                <div class="panel panel-default">
                    <div class="panel-heading">Edit Password</div>
                    <div class="panel-body">
                        <form id="editPassword" role="form">

                            <div class="form-group">
                                <label for="user_password_new">New Password</label>
                                <input id="user_password_new" type="password" name="user_password_new"
                                       autocomplete="off"/>
                            </div>

                            <div class="form-group">
                                <button id="editUserChangePasswordButton" type="button" class="btn btn-primary">Change
                                    Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteUser" tabindex="-1" role="dialog" aria-labelledby="deleteUserLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                        class="sr-only">Close</span></button>
                <h4 class="modal-title" id="deleteUserLabel">Delete User</h4>
            </div>
            <input id="deleteUserID" type="hidden" val=""/>

            <div id="deleteUserMessage" class="modal-body">
                Are you sure you want to delete user <strong id="deleteUserName"></strong> forever?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteUserButton">Delete User</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUser" tabindex="-1" role="dialog" aria-labelledby="addUserLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                        class="sr-only">Close</span></button>
                <h4 class="modal-title" id="addUserLabel">Add User</h4>
            </div>
            <div class="modal-body">
                <p id="addUserErrorMessages"></p>

                <form id="newUserForm" role="form">
                    <div class="form-group">
                        <label for="user_name">User name</label>
                        <input type="text" class="form-control" id="user_name" name="user_name"
                               pattern="[a-zA-Z0-9]{2,64}" required/>
                    </div>

                    <div class="form-group">
                        <label for="user_email">Email</label>
                        <input type="email" class="form-control" id="user_email" name="user_email" required/>
                    </div>

                    <div class="form-group">
                        <label for="user_password_new">Password</label>
                        <input type="password" class="form-control" id="user_password_new" name="user_password_new"
                               pattern=".{6,}" required autocomplete="off"/>
                    </div>

                    <div class="form-group">
                        <label for="user_password_repeat">Password Repeat</label>
                        <input type="password" class="form-control" id="user_password_repeat" autocomplete="off"
                               name="user_password_repeat" pattern=".{6,}" autocomplete="off"/>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="addUserButton">Add User</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteTaskType" tabindex="-1" role="dialog" aria-labelledby="deleteTaskTypeLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span
                        class="sr-only">Close</span></button>
                <h4 class="modal-title" id="deleteTaskTypeLabel">Delete Task Type</h4>
            </div>
            <input id="deleteTaskTypeID" type="hidden" val=""/>

            <div id="deleteUserMessage" class="modal-body">
                Are you sure you want to delete task type <strong id="deleteTaskTypeName"></strong> forever?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteTaskTypeButton">Delete Task Type</button>
            </div>
        </div>
    </div>
</div>

<?php
$db->getConn()->exec('USE `va-case-system`;');
?>
<div class="panel panel-default">
    <div class="panel-heading">Task Types</div>
    <div class="panel-body">
        <button data-toggle="modal" data-target="#addTaskType" type="button" class="btn btn-primary">Add Task Type
        </button>
        <br>
        <br>
        <table class="table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Auto Generation Routines</th>
                <th>Sort Order</th>
                <th>Options</th>
            </tr>
            <?php
            //lists the Task Types existing in the database
            $db->each_row('SELECT * FROM `TaskType` Order By `SortOrder`', null, function ($row) {
                $ttID = $row["id"];
                $ttName = $row["Name"];


                echo "<tr>";

                echo "<td>$ttID</td>";

                echo "<td>";
                echo htmlspecialchars($row["Name"]);
                echo "</td>";

                echo "<td>";
                echo htmlspecialchars(substr($row["Description"], 0, 30));
                echo "</td>";

                echo "<td>";
                echo htmlspecialchars(substr($row["AutoGenerationRoutines"], 0, 30));
                echo "</td>";

                echo "<td>";
                echo htmlspecialchars($row["SortOrder"]);
                echo "</td>";


                echo "<td>";
                echo "<div class='btn-group'>";
                echo "<button type='button' class='btn btn-primary' title='Edit' ";
                echo "onclick='editTaskType($ttID, \"$ttName\", " . json_encode($row["Description"]) . ",";
                echo json_encode($row["AutoGenerationRoutines"]) . ", ";
                echo "\"" . $row["SortOrder"] . "\")'>";
                echo "<span class='glyphicon glyphicon-pencil'></span>";
                echo "</button>";
                echo "<button type='button' class='btn btn-danger' onclick='deleteTaskType($ttID, \"$ttName\")'><span class='glyphicon glyphicon-remove'/></button>";
                echo "</div>";
                echo "</td>";
                echo "</tr>";
            });
            ?>
        </table>
    </div>
</div>
<div class="modal fade" id="addTaskType" tabindex="-2" role="dialog" aria-labelledby="addTaskTypeLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="addTaskTypeLabel">Add Task Type</h4>
            </div>
            <div class="modal-body">
                <div class="panel panel-default">
                    <div class="panel-heading">Task Type</div>
                    <div class="panel-body">
                        <form id="addTaskTypeForm" class="form">
                            <input type="hidden" name="addTaskType" value="1"/>

                            <div class="form-group">
                                <label for="task_title">Name</label>
                                <input class="form-control" id="task_name" name="task_name" required/>
                            </div>
                            <div class="form-group">
                                <label for="task_description">Description</label>
                                <textarea class="form-control" rows="4" id="task_description"
                                          name="task_description"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="task_autogen">Autogeneration Routines</label>
                                <textarea class="form-control" rows="4" id="task_autogen"
                                          name="task_autogen"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="task_sortOrder">Sort Order</label>
                                <input class="form-control" type="text" id="task_sortOrder" pattern="[0-9]*"
                                       name="task_sortOrder"/>
                            </div>
                            <div class="form-group">
                                <button id="addNewTaskTypeButton" type="button" class="btn btn-primary">Add New Task
                                    Type
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editTaskType" tabindex="-2" role="dialog" aria-labelledby="editTaskTypeLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">Close</span>
                </button>
                <h4 class="modal-title" id="editTaskTypeLabel">Edit Task Type</h4>
            </div>
            <div class="modal-body">
                <div class="panel panel-default">
                    <div class="panel-heading">Edit Task Type</div>
                    <div class="panel-body">
                        <form id="editTaskTypeForm" class="form">
                            <input type="hidden" id="editTaskTypeID" name="editTaskTypeID"/>

                            <div class="form-group">
                                <label for="task_title">Name</label>
                                <input class="form-control" id="edit_task_name" name="task_name" required/>
                            </div>
                            <div class="form-group">
                                <label for="task_description">Description</label>
                                <textarea class="form-control" rows="4" id="edit_task_description"
                                          name="task_description"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="task_autogen">Autogeneration Routines</label>
                                <textarea class="form-control" rows="4" id="edit_task_autogen"
                                          name="task_autogen"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="task_sortOrder">Sort Order</label>
                                <input class="form-control" type="text" id="edit_task_sortOrder" pattern="[0-9]*"
                                       name="task_sortOrder"/>
                            </div>
                            <div class="form-group">
                                <button id="editTaskTypeButton" type="button" class="btn btn-primary">Save Task Type
                                    Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script>

//prepares the edit user modal
    function editUser(user) {
        $("#editUserID").val(user);
        $("#editUser").modal('show');
    }

//prepares the edit task type modal
    function editTaskType(id, name, description, autoGen, sortOrder) {
        $("#editTaskTypeID").val(id);
        $("#edit_task_name").val(name);
        $("#edit_task_description").val(description);
        $("#edit_task_autogen").val(autoGen);
        $("#edit_task_sortOrder").val(sortOrder);
        $("#editTaskType").modal('show');
    }

//prepares the delete user
    function deleteUser(userID, userName) {
        $("#deleteUserID").val(userID);
        $("#deleteUserName").html(userName);
        $("#deleteUser").modal('show');
    }

//prepares the Delete Task Type modal
    function deleteTaskType(userID, userName) {
        $("#deleteTaskTypeID").val(userID);
        $("#deleteTaskTypeName").html(userName);
        $("#deleteTaskType").modal('show');
    }

//add task type button handler
    $("#addNewTaskTypeButton").click(function () {
        $.post("adminTaskType.php",
            $("#addTaskTypeForm").serialize(),
            function (data) {
                if (data !== "") {
                    alert("Error while adding task type");
                    console.error(data);
                } else {
                    document.location = "admin.php";
                }
            }
        );
    });

//edit task type button handler
    $("#editTaskTypeButton").click(function () {
        $.post("adminTaskType.php",
            $("#editTaskTypeForm").serialize(),
            function (data) {
                if (data !== "") {
                    alert("Error while editing Task Type");
                    console.error(data);
                } else {
                    document.location = "admin.php";
                }
            }
        );
    });
//delete user button handler
    $("#deleteUserButton").click(function () {
        console.log($("#deleteUserID").val());
        $.post("adminRemoveUser.php", {"userID": $("#deleteUserID").val() }, function (data) {
            if (data.indexOf("ERROR!!!") > -1) {
                alert("Could not remove user");
            } else {
                window.location = "admin.php";
            }
        });
    });

//delete task type button handler
    $("#deleteTaskTypeButton").click(function () {
        $.post("adminTaskType.php",
            {"removeTaskTypeID": $("#deleteTaskTypeID").val()},
            function (data) {
                alert(data);
                if (data !== "") {
                    alert("Error while removing the TaskType");
                } else {
                    window.location = "admin.php";
                }
            }

        );
    });

    //debug function
    $("#editUserChangePasswordButton").click(function () {
        alert($("#editUserID").val());
    });


    //change the password event handler
    $("#changePasswordButton").click(function () {
        var changePasswordFormData = $("#changePasswordForm").serialize();
        changePasswordFormData += "&user_edit_submit_password=1";
        $.post("adminChangePassword.php", changePasswordFormData, function (data) {
            if (data.indexOf("ERROR!!!") > -1) {
                var errorMessage = data.match(new RegExp("<div class='alert alert-danger' role='alert'>.*</div>", "m"));
                $("#changeAdminPasswordErrors").html(errorMessage);
            } else {
                var successMessage = "<div width='75%' class='alert alert-success alert-dismissible' role='alert'>";
                successMessage += "Password successfully changed</div>";
                $("#changeAdminPasswordErrors").html(successMessage);
            }
        });
    });

    
    //add User button event handler
    $("#addUserButton").click(function () {
        $("#addUser").hide();
        $.blockUI({message: "<p>Adding user</p>"});
        $.post("addUser.php", $("#newUserForm").serialize(),function (data) {
            if (data.indexOf("ERROR!!!") > -1) {
                var errorMessage = data.match(new RegExp("<div class='alert alert-danger' role='alert'>.*</div>", "m"));
                $.unblockUI();
                //alert("Error adding user\n"+data+errorMessage);
                $("#addUserErrorMessages").html(errorMessage);
                $("#addUser").show();
            } else {
                window.location = "admin.php?newUser=" + $("#user_name").val();
            }
        }).always(function () {
            $.unblockUI();
            $("#addUser").show();
        });
    });
</script>
</body>
</html>
