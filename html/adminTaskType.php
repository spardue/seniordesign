<?php
include_once("headlessheader.php");
/*
   functions for adding, changing, and deleting TaskTypes

*/

//add a Task Type
if (isset($_POST["addTaskType"])) {
    $db->interact("INSERT INTO TaskType(Name, Description, AutoGenerationRoutines, SortOrder)" .
        "VALUES(:name, :description, :autogen, :sortOrder);",
        array(":name" => $_POST["task_name"],
            ":description" => $_POST["task_description"],
            ":autogen" => $_POST["task_autogen"],
            ":sortOrder" => $_POST["task_sortOrder"]
        )
    );
} else if (isset($_POST["removeTaskTypeID"])) { //remove a task type
    $db->interact("DELETE FROM TaskType WHERE id = :id", array(":id" => $_POST["removeTaskTypeID"]));
} else if (isset($_POST["editTaskTypeID"])) { //edit a task type
    $db->interact("UPDATE TaskType SET Name = :name, Description = :description, AutoGenerationRoutines = :auto, SortOrder = :so WHERE id = :id",
        array(":name" => $_POST["task_name"],
            ":description" => $_POST["task_description"],
            ":auto" => $_POST["task_autogen"],
            ":so" => $_POST["task_sortOrder"],
            ":id" => $_POST["editTaskTypeID"]
        )
    );
}



?>
