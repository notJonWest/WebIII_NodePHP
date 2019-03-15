<?php
    require_once("./include/Task.php");
    $tasks = array();
    $status = 0;
    if (isset($_REQUEST["status"]))
        $status = $_REQUEST["status"];
    $responseJSON = "";
    if (file_exists("./List/tasks.json"))
    {
        $tasksJSON = json_decode(file_get_contents("./List/tasks.json"), true);

        foreach($tasksJSON as $task)
            array_push($tasks, new Task($task));
        usort($tasks, Task::sort("status", ["dateModified", "desc"], "title", "id"));

        $tasksJSON = array();

        foreach (json_decode(json_encode($tasks), true) as $task)
        {
            if ($status == 0 or strtolower($status) == "all" or
                $status == $task["status"] or
                strtolower($status) == strtolower(Task::STATUSES[$task["status"]]))
            {
                foreach($task as $attr=>$val)
                    if ($attr != "id" and $attr != "title" and
                        $attr != "dateModified" and $attr != "status")
                            unset($task[$attr]);

                $task["dateModified"] = date("Y/m/d", strtotime($task["dateModified"]));

                array_push($tasksJSON, $task);
            }
        }
        $responseJSON = json_encode($tasksJSON, JSON_PRETTY_PRINT);
    }

    header("Content-Type: application/json");
    header("Content-Length: " . strlen($responseJSON));
    echo $responseJSON;