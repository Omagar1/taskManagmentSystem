<?php
ob_start(); // so that redirect works 
session_start();
require "functions.php";
require_once "dbConnect.php";
?>

<!DOCTYPE html>
<html lang="en">
<?php
$_SESSION["previous"] = []; // initalising the Previous Stack
$pageName = basename($_SERVER["PHP_SELF"]); // getting the name of the page so head can add it to the Previous stack
head($pageName); // from functions.php, echoes out the head tags  
?>
<script>
var openTabsIDQueue = []; 
const maxTabsOpen = 10; 
//----------------------------------------------------Functions ----------------------------------------------------
function openTaskList(taskListID){
    if(openTabsIDQueue.length >= maxTabsOpen){
        closeTaskList(openTabsIDQueue[0]); // removes the tasklist opened the first
    }

    shiftTab(taskListID, openTabsIDQueue.length, maxTabsOpen);// sets position of opening tab
    openTabsIDQueue.push(taskListID);
    shiftTab("all", openTabsIDQueue.length, maxTabsOpen);// resteing teh All tabs position at the end
    
    taskListTab = document.getElementById(taskListID + "Tab");
    taskListTab.classList.remove("hidden");
    changeTab(taskListID); 
    
    //change open tab button to close
    document.getElementById("openButton"+taskListID).classList.add("hidden");
    document.getElementById("closeButton"+taskListID).classList.remove("hidden");
}


function closeTaskList(taskListID){
    //hide tasklist Tab
    taskListTab = document.getElementById(taskListID + "Tab");
    taskListTab.classList.add("hidden");
    //change view
    changeTab("all"); 
    //change close tab button to open
    document.getElementById("openButton"+taskListID).classList.remove("hidden");
    document.getElementById("closeButton"+taskListID).classList.add("hidden");
    //change postition for all tabs after tab closed and remove from openTabsIDQueue
    indexToRemove = openTabsIDQueue.indexOf(taskListID);
    openTabsIDQueue.splice(indexToRemove,1);
    console.log(openTabsIDQueue);
    for(var i = indexToRemove; i < openTabsIDQueue.length; i++){
        shiftTab(taskListID,openTabsIDQueue.length, maxTabsOpen);
    }
    shiftTab("all",openTabsIDQueue.length, maxTabsOpen);//as all tab is not included in the openTabsIDQueue

}

</script>
<?php
// ---------------------------------------------------- class Stuff ----------------------------------------------------
class Stage{ 
    public $ID;
    public $name;
    public $weighting;	
    public $complete;	
    public $dateTimeCompleted;
    public $completedBy;

    public function __construct($vals,){
        // seting varibles
        foreach($vals as $property => $val) {
            $this->$property = $val;
        }
    }
}
class Task{
    public $ID;
    public $name;	
    public $deadline;	
    public $priority;
    public $stages = [];

    public function __construct($vals, $con){
        // seting varibles
        foreach($vals as $property => $val) {
            $this->$property = $val;
        }
       

        // seting tasks in the task list
        $qry = "SELECT ID, `name`, weighting, complete, dateTimeCompleted, completedBy FROM stage WHERE taskID = ?;"; 
        $stmt = $con->prepare($qry);
        $stmt->execute([$this->ID]);
        $this->stages = $stmt->fetchAll(PDO::FETCH_ASSOC); 
        //var_dump($this->stages); //test
        foreach($this->stages as $row => $vals){
            $this->stages[$row] = new Stage($vals,);

        }
        //echo "</br></br>";
        //var_dump($this->stages); //test
    }

}


class TaskList{
    public $ID;	
    public $name;
    public $deadline;
    public $collab;
    public $priority;	
    public $ownerID;
    public $tasks = []; 

    public function __construct($vals, $con){
        // seting varibles
        foreach($vals as $property => $val) {
            $this->$property = $val;
            //echo $val; //test
        }
       

        // seting tasks in the task list
        $qry = "SELECT ID, `name`, deadline, `priority` FROM task WHERE taskListID = ?;"; 
        $stmt = $con->prepare($qry);
        $stmt->execute([$this->ID]);
        $this->tasks = $stmt->fetchAll(PDO::FETCH_ASSOC); 
        //var_dump($this->tasks); //test
        foreach($this->tasks as $row => $vals){
            $this->tasks[$row] = new Task($vals, $con);

        }
        //echo "</br></br>";
        //var_dump($this->tasks); //test
    }
    
    

}

$qry = "SELECT ID, `name`, deadline, collab, `priority`, ownerID  FROM taskList WHERE ownerID = ?;"; 
$stmt = $conn->prepare($qry);
$stmt->execute([$_SESSION["userID"]]);
$taskLists = $stmt->fetchAll(PDO::FETCH_ASSOC);
//var_dump($taskLists); //test
foreach($taskLists as $row => $vals){
    $taskLists[$row] = new TaskList($vals, $conn);

}
echo "</br></br>";
//var_dump($taskLists); //test

// pull all tasklists 
// pull all tasks 


?>
</head>


<body id>
    <?php navBar(false, basename($_SERVER["PHP_SELF"]) ); ?>
    </div>
    <div id="main">
        <div class="tabs">
        <?php
            foreach($taskLists as $taskList){
                
        ?>
            <button onclick="changeTab('<?php echo $taskList->ID?>')" id="<?php echo $taskList->ID."Tab"?>" class="tab <?php echo($taskList->name == "Genral") ? "first" : "hidden";  ?>"><?php echo $taskList->name?></button>
            <script><?php echo ($taskList->name == "Genral") ? "var genralTaskListID =".$taskList->ID : ""; ?>//for opening genral tasklist automaticly </script>
            
        <?php
        }// taskList foreach close
        ?>
        <!-- the all tab which is allways last -->
        <button onclick="changeTab('all')" id="allTab" class="tab selected tab2">All</button>
        <!-- <script>openTabsIDQueue.length = shiftTab('all', openTabsIDQueue.length) -1 // so add is not counted as a tab </script> -->
        </div>

        <!-- genral taskList Code  -->
        <?php
            foreach($taskLists as $taskList){
                // echo "test1: ". $taskList->ID;
                // echo "test2: ". var_dump($taskList);
        ?>
            
            <div id="<?php echo $taskList->ID; ?>Container"  class="hidden">
                <button class="button collabColour">Make Collab</button>
                <button class="button red">Delete</button>

                <?php
                // ------------- tasks -------------
                if(count($taskList->tasks) == 0){
                    echo "<br/>WOW such emptiness<br/> ";
                    echo"<button class='button green'>New Task</button>";
                }
                foreach($taskList->tasks as $task){
                    //echo"I Ran";//test
                ?>
                    <div id="<?php echo $task->ID; ?>" class="taskContainer">
                        <div class="taskHeader">
                            <h2><?php echo $task->name;?></h2>
                            <button  class="clear" >edit</button>
                            <button  class="clear">MakeRepeat</button>
                            <button class="clear">X</button>
                        </div>

                        <table class="taskTable">
                            <tr class="taskTr">
                                <td class="taskTd tableDisplay">
                                    priority
                                </td>
                                <td class="taskTd tableDisplay">
                                    <button class="button green"><?php echo $task->priority;?></button>
                                </td>
                            </tr>
                            <tr class="taskTr">
                                <td class="taskTd tableDisplay">
                                    deadline
                                </td>
                                <td class="taskTd tableDisplay">
                                    <?php echo $task->deadline;?>
                                </td>
                            </tr>

                        </table>
                        <!-- ------------- stages ------------- -->
                        <table class="clear">
                            <tr>
                                <th class="clear textWhite"><b>Stages</b></th>
                                <th class="clear textWhite"><b>weighting</b></th>
                            </tr>
                            <?php
                        
                            foreach($task->stages as $stage){
                                echo"
                                <tr>
                                    <td class='clear'>
                                        ".$stage->name."
                                    </td>
                                    <td class='clear'>
                                        ".$stage->weighting."%
                                    </td>
                                    <td class='clear'>
                                        ".yesOrNo($stage->complete)."
                                    </td>
                                </tr>
                                ";
                                
                            }
                            
                            ?>
                        </table>
                            
                        
                        
                    </div>
                <?php
                }// taskList foreach close
                ?>
            </div>
        <?php
        }// taskList foreach close
        ?>

        <!-- All task lists  -->
        <div id="allContainer" class="showing">
            <table class = "tableDisplay">
                    <tr>
                        <th>Name</th>
                        <th>Deadline</th>
                        <th>Collab</th>
                        <th>Priority</th>
                        <th>OwnerID</th>
                        <th></th>
                    </tr>
                
                <?php
                    foreach($taskLists as $taskList){
                        //var_dump($taskList); //test
                        echo"<tr>";
                        echo "<td>" . $taskList->name . "</td>";
                        echo "<td>" . yesOrNo($taskList->deadline) . "</td>";
                        echo "<td>" . yesOrNo($taskList->collab) . "</td>";
                        echo "<td>" . $taskList->priority . "</td>";
                        echo "<td>" . $taskList->ownerID . "</td>";
                        echo"<td>
                        <button onClick='openTaskList(".$taskList->ID.")' id='openButton".$taskList->ID."' class='button green'>Open?</button>
                        <button onClick='closeTaskList(".$taskList->ID.")' id='closeButton".$taskList->ID."' class='button red hidden'>Close?</button>
                        </td>";
                        echo"</tr>";
                    }
                ?>
                
            </table>
        </div>
        
        <script>
        openTaskList(genralTaskListID);
        changeTab("<?php if (isset($currentDisplay)){ echo $currentDisplay;}else{echo "all";} ?>")
        </script>
               
    </div>


    <?php footer(); ?>
</body>

</html>