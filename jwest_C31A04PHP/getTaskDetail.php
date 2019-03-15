<?php
require_once("./include/Task.php");

$id = -1;
if (isset($_REQUEST["id"]))
    $id = $_REQUEST["id"];
$responseJSON = "";
if (file_exists("./List/tasks.json"))
{
    $tasksJSON = json_decode(file_get_contents("./List/tasks.json"), true);
        foreach ($tasksJSON as $task)
            if ($id == $task["id"])
            {
                $task["status"] = Task::numToStatus($task["status"]);
                $task["dateCreated"] = date("l, \\t\h\\e jS \of F, Y", strtotime($task["dateCreated"]));
                $task["dateModified"] = date("l, \\t\h\\e jS \of F, Y", strtotime($task["dateModified"]));
                $responseJSON = json_encode($task, JSON_PRETTY_PRINT);
            }
}

header("Content-Type: application/json");
echo $responseJSON;