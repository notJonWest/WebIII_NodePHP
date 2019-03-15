<?php
    require_once("Task.php");
    $tasks = array();

    array_push($tasks,
        new Task(["title"=>"Fo it", "description"=>"Nama"]));
    array_push($tasks,
        new Task(["title"=>"Eo it", "description"=>"Oama"]));
    array_push($tasks,
        new Task(["title"=>"Do it", "description"=>"Mama"]));
    array_push($tasks,
        new Task(["title"=>"Do it", "description"=>"Aama"]));
    array_push($tasks,
        new Task(["title"=>"Co it", "description"=>"Mama"]));
    array_push($tasks,
        new Task(["title"=>"Eo it", "description"=>"Nama"]));

    usort($tasks, Task::sort(["title", "hell"], "description"));


    foreach($tasks as $task)
    {
        var_dump($task);
        echo "<br/>";
        echo $task->getId().$task->getTitle().$task->getDescription()."<br/><br/><br/>";
    }

?>