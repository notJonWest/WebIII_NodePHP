<?php
require_once("./include/Task.php");
require_once("./include/functions.php");

if (isset($_POST["deletePrompt"]))
{
    ?>
    <!DOCTYPE html> <!-- DELETE PROMPT /-->
    <html lang="en">
        <head>
            <title>Delete Task</title>
            <meta charset="UTF-8"/>
            <meta name="viewport" content="width=device-width"/>
            <link rel="stylesheet" href="../public/styles/style.css"/>
        </head>
        <body>
            <header>
                <h2>Are You Sure?</h2>
            </header>
            <main>
                <form method="post" action="./manageTasks.php">
                    <p>
                        <label>&nbsp;</label>
                        <input type="submit" value="No, I want to keep it" name="keep"/>
                    </p>
                    <p>
                        <label>&nbsp;</label>
                        <input type="submit" value="Yes, delete it" class="secondary" name="delete"/>
                    </p>
                    <input type="hidden" name="edit" value="<?php echo $_POST["edit"]; ?>"/>
                    <input type="hidden" name="title" value="<?php echo $_POST["title"]; ?>"/>
                    <input type="hidden" name="description" value="<?php echo $_POST["description"]; ?>"/>
                    <input type="hidden" name="dateCreated" value="<?php echo $_POST["dateCreated"]; ?>"/>
                    <input type="hidden" name="dateModified" value="<?php echo $_POST["dateModified"]; ?>"/>
                </form>
            </main>
        </body>
    </html>
    <?php
}
else
{
    $notificationMsg = "";
    $tasks = array();

    if (!is_dir("./List"))
        mkdir("./List");
    if (file_exists("./List/tasks.json"))
    {
        $tasksJSON = json_decode(file_get_contents("./List/tasks.json"), true);
        foreach ($tasksJSON as $task)
            array_push($tasks, new Task($task));
    }

    $goToEdit = (isset($_POST["edit"]) and !isset($_POST["cancel"]));
    $validatedArr = validate(["emptyValidatedArr"=>true]);

    $currTask = null;
    $i = 0;
    if ($goToEdit)
    {
        while ($i < sizeof($tasks) and $currTask == null)
        {
            if ($tasks[$i] != null)
            {
                if ($_POST["edit"] == $tasks[$i]->getId())
                    $currTask = &$tasks[$i];
            }
            $i++;
        }
    }

    if (isset($_POST["commitChange"]))
    {
        $validatedArr = validate($_POST);
        $goToEdit = !$validatedArr["valid"]; //Go back to edit if not valid;

        if ($validatedArr["valid"])
        {
            $currTask->fill($_POST);
            $notificationMsg = "Successfully altered " . $currTask->getTitle().".";
            file_put_contents("./List/tasks.json", json_encode($tasks, JSON_PRETTY_PRINT));
        }
    }

    if (isset($_POST["delete"]))
    {
        $currTask->setStatus("-1");
        file_put_contents("./List/tasks.json", json_encode($tasks, JSON_PRETTY_PRINT));
        $notificationMsg = "Successfully deleted ".$currTask->getTitle().".";
        $goToEdit = false;
    }

    $selectedTab = "todo";
    if (isset($_POST["selectedTab"]))
        $selectedTab = $_POST["selectedTab"];
    else if (isset($_POST["status"]))
        $selectedTab = Task::STATUSES[(int) $_POST["status"]];

    if ($goToEdit)
    {
        $title = null;
        $desc = null;
        $dateCreated = null;
        $modified = null;

        if (isset($_POST["commitChange"]) or isset($_POST["keep"])) //commitChange will only exist if they came from the edit form
        {
            $title = $_POST["title"];
            $desc = $_POST["description"];
            $dateCreated = $_POST["dateCreated"];
            $modified = $_POST["dateModified"];
        }
        ?>
        <!DOCTYPE html> <!-- EDIT FORM /-->
        <html lang="en">
        <head>
            <title>Edit <?php echo $currTask->getTitle(); ?></title>
            <meta charset="UTF-8"/>
            <meta name="viewport" content="width=device-width"/>
            <link rel="stylesheet" href="../public/styles/style.css"/>
        </head>
        <body>
        <header>
            <h2>Edit Task</h2>
        </header>
        <main>
            <form method="post" action="./manageTasks.php">
                <p>
                    <label for="title">Title:</label>
                    <input type="text" name="title" id="title" class="<?php echo (empty($validatedArr["titleMsg"]))?"":"invalid error"; ?>" value="<?php echo $title??$currTask->getTitle(); ?>"/>
                    <span class="errorMsg"><?php echo $validatedArr["titleMsg"]; ?></span>
                </p>
                <p>
                    <label for="description">Description:</label>
                    <textarea name="description" id="desc" class="<?php echo (empty($validatedArr["descMsg"]))?"":"invalid error"; ?>"><?php echo $desc??$currTask->getDescription(); ?></textarea>
                    <span class="errorMsg"><?php echo $validatedArr["descMsg"]; ?></span>
                </p>
                <p>
                    <label for="created">Date Created:</label>
                    <input type="text" name="dateCreated" id="created" class="<?php echo (empty($validatedArr["createdMsg"]))?"":"invalid error"; ?>" value="<?php echo $dateCreated??$currTask->getDateCreated(); ?>"/>
                    <span class="errorMsg"><?php echo $validatedArr["createdMsg"]; ?></span>
                </p>
                <p>
                    <label for="modified">Date Updated:</label>
                    <input type="text" name="dateModified" id="modified" class="<?php echo (empty($validatedArr["modifiedMsg"]))?"":"invalid error"; ?>" value="<?php echo $modified??date("Y-m-d"); ?>"/>
                    <span class="errorMsg"><?php echo $validatedArr["modifiedMsg"]; ?></span>
                </p>
                <p>
                    <label for="status">Status:</label>
                    <select id="status" <?php echo ($currTask->getStatus() == 4)?"disabled":""; ?> name="status">
                        <?php
                        if ($currTask->getStatus() == 1)
                        {
                            ?>
                            <option selected value="1">To Do</option>
                            <option value="2">In Development</option>
                            <?php
                        }
                        elseif ($currTask->getStatus() == 2)
                        {
                            ?>
                            <option selected value="2">In Development</option>
                            <option value="3">Testing</option>
                            <?php
                        }
                        elseif ($currTask->getStatus() == 3)
                        {
                            ?>
                            <option value="2">In Development</option>
                            <option selected value="3">Testing</option>
                            <option value="4">Complete</option>
                            <?php
                        }
                        elseif ($currTask->getStatus() == 4)
                        {
                            ?>
                            <option selected value="4">Complete</option>
                            <?php
                        }
                        ?>
                    </select>
                    <?php
                        if ($currTask->getStatus() == 4)
                        { //Need this because when completed, the dropdown is disabled
                            ?>
                            <input type="hidden" value="4" name="status"/>
                            <?php
                        }
                    ?>
                </p>
                <p>
                    <label>&nbsp;</label>
                    <input type="submit" name="commitChange" id="update" value="Update"/>
                    <input type="submit" name="cancel" id="cancel" value="Cancel" class="secondary"/>
                </p>
                <p>
                    <label>&nbsp;</label>
                    <input type="submit" name="deletePrompt" id="deletePrompt" value="Delete Task" class="secondary"/>
                </p>
                <input type="hidden" name="edit" value="<?php echo $currTask->getId() ?>"/>
            </form>
        </main>
        </body>
        </html>
        <?php
    }
else
{
    $title = "";
    $desc = "";
    $created = "";
    $modified = "";

    $titleMsg = "";
    $descMsg = "";
    $createdMsg = "";
    $modifiedMsg = "";

    if (isset($_POST["newTask"]))
    {
        $title = $_POST["title"];
        $desc = $_POST["description"];
        $created = $_POST["dateCreated"];
        $modified = $_POST["dateModified"];

        $validatedArr = validate($_POST);

        if ($validatedArr["valid"])
        {
            $newTask = new Task($_POST);
            $newTask->setStatus(1);

            array_push($tasks, $newTask);

            file_put_contents("./List/tasks.json", json_encode($tasks, JSON_PRETTY_PRINT));

            $notificationMsg = "The task \"$title\" was successfully created!";

            //Blank fields to avoid sticky form after valid submission
            $title = "";
            $desc = "";
            $created = "";
            $modified = "";
        }
    }
    usort($tasks, Task::sort(["status", "heaven"], ["dateModified", "hell"],
                                    ["dateCreated", "asc"], ["title", "desc"],
                                    ["description", "desc"]));
?>
<!DOCTYPE html> <!-- TABLES /-->
<html lang="en">
<head>
    <title>Tasks</title>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width"/>
    <link rel="stylesheet" href="../public/styles/style.css"/>
</head>
<body>
<header>
    <h2>Manage Tasks</h2>
</header>
<main>
    <div class="tabs">
    <?php
    foreach (Task::STATUSES as $index=>$status)
    {
        ?>
        <div class="tab">
            <input type="radio" id="<?php echo $status; ?>" name="tabGrp" <?php echo ($selectedTab==$status)?"checked":""; ?>/>
            <label for="<?php echo $status; ?>"><?php echo TASK::numToStatus($index); ?></label>
            <div class="tabContent">
                <div class="tabContainer">
                    <form action="./manageTasks.php" method="post" class="ignoreFormatting">
                        <table class="expandLast hover">
                            <tr>
                                <th>Title</th>
                                <th>Created</th>
                                <th>Modified</th>
                                <?php echo ($index==0)?"<th>Status</th>":""; ?>
                                <th>Description</th>
                            </tr>
                            <?php
                            $taskCount = 0;
                            foreach ($tasks as $task)
                            {
                                if ($task->getStatus() != -1)
                                {
                                    if ($index == 0 or $task->getStatus() == $index)
                                    {
                                        $taskCount++;
                                        ?>
                                        <tr class="submitable">
                                            <td>
                                                <label for="a_task<?php echo $task->getId(); ?>"><?php echo $task->getTitle(); ?></label>
                                                <input type="submit" class="hide" name="edit" id="a_task<?php echo $task->getId(); ?>" value="<?php echo $task->getId(); ?>"/>
                                            </td>
                                            <td><label for="a_task<?php echo $task->getId(); ?>"><?php echo $task->getDateCreated(); ?></label></td>
                                            <td><label for="a_task<?php echo $task->getId(); ?>"><?php echo $task->getDateModified(); ?></label></td>
                                            <?php
                                            if ($index==0)
                                            {
                                                ?>
                                                <td><label for="a_task<?php echo $task->getId(); ?>"><?php echo Task::numToStatus($task->getStatus()); ?></label></td>
                                                <?php
                                            }
                                            ?>
                                            <td><label for="a_task<?php echo $task->getId(); ?>"><?php echo $task->getDescription(); ?></label></td>
                                        </tr>
                                        <?php
                                    }
                                }
                            }
                            if ($taskCount == 0)
                            {?>
                                <tr>
                                    <td colspan="4">There are currently no tasks under "<?php echo TASK::numToStatus($index); ?>".</td>
                                </tr>
                    <?php   }?>
                        </table>
                        <input type="hidden" name="selectedTab" value="<?php echo $status; ?>"/>
                    </form>
                </div>
            </div>
        </div>
<?php
    }
    ?>
        <div class="tab">
            <input type="radio" id="new" name="tabGrp" value="new" <?php echo ($selectedTab=="new")?"checked":""; ?>/>
            <label for="new">New</label>
            <div class="tabContent">
                <div class="tabContainer">
                    <form method="post" action="./manageTasks.php">
                        <p>
                            <label for="title">Title:</label>
                            <input type="text" name="title" id="title" class="<?php echo (empty($validatedArr["titleMsg"]))?"":"invalid error"; ?>" value="<?php echo $title; ?>"/>
                            <span class="errorMsg"><?php echo $validatedArr["titleMsg"]; ?></span>
                        </p>
                        <p>
                            <label for="desc">Description:</label>
                            <textarea name="description" id="desc" class="<?php echo (empty($validatedArr["descMsg"]))?"":"invalid error"; ?>"><?php echo $desc; ?></textarea>
                            <span class="errorMsg"><?php echo $validatedArr["descMsg"]; ?></span>
                        </p>
                        <p>
                            <label for="created">Date Created:</label>
                            <input type="text" name="dateCreated" id="created" class="<?php echo (empty($validatedArr["createdMsg"]))?"":"invalid error"; ?>" value="<?php echo (empty($created))?date("Y-m-d"):$created; ?>"/>
                            <span class="errorMsg"><?php echo $validatedArr["createdMsg"]; ?></span>
                        </p>
                        <p>
                            <label for="modified">Date Updated:</label>
                            <input type="text" name="dateModified" id="modified" class="<?php echo (empty($validatedArr["modifiedMsg"]))?"":"invalid error"; ?>" value="<?php echo (empty($modified))?date("Y-m-d"):$modified; ?>"/>
                            <span class="errorMsg"><?php echo $validatedArr["modifiedMsg"]; ?></span>
                        </p>
                        <p>
                            <label>&nbsp;</label>
                            <input type="submit" name="newTask" id="newTask" value="Add Task"/>
                        </p>
                        <input type="hidden" name="selectedTab" value="new"/>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php
        if (!empty($notificationMsg))
        {
?>
    <div class="notification-container">
        <input type="checkbox" id="close"/>
        <div class="notification">
            <div class="message"><?php echo $notificationMsg; ?></div>
            <label class="exit" for="close">Close</label>
        </div>
    </div>
<?php
       }
?>
</body>
</html>
<?php
    }
}
?>