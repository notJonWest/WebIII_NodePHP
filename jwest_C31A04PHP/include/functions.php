<?php
//    require_once("Task.php");
//    $tasks = array();
//
//    array_push($tasks,
//        new Task(["title"=>"Fo it", "description"=>"Nama"]));
//    array_push($tasks,
//        new Task(["title"=>"Eo it", "description"=>"Oama"]));
//    array_push($tasks,
//        new Task(["title"=>"Do it", "description"=>"Mama"]));
//    array_push($tasks,
//        new Task(["title"=>"Do it", "description"=>"Aama"]));
//    array_push($tasks,
//        new Task(["title"=>"Co it", "description"=>"Mama"]));
//    array_push($tasks,
//        new Task(["title"=>"Eo it", "description"=>"Nama"]));
//
//    usort($tasks, Task::sort(["title", "hell"], "description"));
//
//
//    foreach($tasks as $task)
//    {
//        var_dump($task);
//        echo "<br/>";
//        echo $task->getId().$task->getTitle().$task->getDescription()."<br/><br/><br/>";
//    }

function validate($data)
{
    $returnArr = [
        "valid"=>true,
        "titleMsg"=>"",
        "descMsg"=>"",
        "createdMsg"=>"",
        "modifiedMsg"=>""
    ];

    if (isset($data["emptyValidatedArr"]))
    {
        $returnArr["valid"] = $data["emptyValidatedArr"]??true;
        return $returnArr;
    }

    if (isset($data["title"]) and isset($data["description"]) and
        isset($data["dateModified"]) and isset($data["dateCreated"]))
    {
        if (empty($data["title"]))
        {
            $returnArr["valid"] = false;
            $returnArr["titleMsg"] = "Please give the task a title.";
        }
        if (empty($data["description"]))
        {
            $returnArr["valid"] = false;
            $returnArr["descMsg"]= "Please describe the task.";
        }


        if ($data["dateModified"] < $data["dateCreated"])
        {
            $returnArr["valid"] = false;
            $returnArr["createdMsg"] = "The creation date must be before or the same as the modified date.";
            $returnArr["modifiedMsg"] = "The modified date must be after or the same as the created date.";
        }

        if ($data["dateModified"] > date("Y-m-d"))
        {
            $returnArr["valid"] = false;
            $returnArr["createdMsg"] = "";
            $returnArr["modifiedMsg"] = "Cannot have a future date.";
        }

        if ($data["dateCreated"] > date("Y-m-d"))
        {
            $returnArr["valid"] = false;
            $returnArr["createdMsg"] = "Cannot have a future date.";
            $returnArr["modifiedMsg"] = "";
        }

        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data["dateModified"]))
        {
            $returnArr["valid"] = false;
            $returnArr["modifiedMsg"]= "Please enter the date in the format YYYY-MM-DD";
        }
        if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $data["dateCreated"]))
        {
            $returnArr["valid"] = false;
            $returnArr["createdMsg"]= "Please enter the date in the format YYYY-MM-DD";
        }


        if (empty($data["dateCreated"]))
        {
            $returnArr["valid"] = false;
            $returnArr["createdMsg"]= "Please provide a creation date.";
        }
        if (empty($data["dateModified"]))
        {
            $returnArr["valid"] = false;
            $returnArr["modifiedMsg"]= "Please provide the last modified date.";
        }
    }
    else
    {
        throw new InvalidArgumentException("Invalid arguments. \$data must contain ");
    }

    return $returnArr;
}

?>