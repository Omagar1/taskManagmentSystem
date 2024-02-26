<?php
ob_start(); // so that redirect works 
session_start();
require "functions.php";
require_once "dbConnect.php";
// require("editRowProcess.php"); not implemented yet 
require("addRowProcess.php");
require "deleteRowProcess.php";
?>

<!DOCTYPE html>
<html lang="en">
<?php

// $_SESSION["previous"] = []; // initalising the Previous Stack
$priorities = ["high","medium","low"]; 
$pageName = basename($_SERVER["PHP_SELF"]); // getting the name of the page so head can add it to the Previous stack
$_SESSION["currentPage"] = $pageName; 

function getPriorityName($PriorityVal, $priorities){
    return $priorities[$PriorityVal-1];
}


// ---------------------------------------------------- task List Validation -------------------------------------------------
$OpenTabs = [] ;// so reloading dosent close them
$currentDisplay; 
function nameInDB($nameToCheck,$con){
      // qry to get existing usernames for valadation
      $qry = "SELECT `name` FROM tasklist WHERE `name` = ? AND ownerID = ?";
      $stmt = $con->prepare($qry);
      $stmt->execute([$nameToCheck,$_SESSION["userID"]]);
      $count = $stmt->rowCount(); 
      if ($count == 0){
        return false;
      }else{
        return true;
      }
}

//var_dump($_POST);//test

//var_dump($_SESSION["tokenNTL"] == $_POST["tokenNTL"]); //test
if (isset($_POST["tokenNTL"]) And $_SESSION["tokenNTL"] == $_POST["tokenNTL"] ) {
    echo " submitNTL Ran"; //test
    array_push($OpenTabs,"newTaskList");
    $currentDisplay = "newTaskList";
    //var_dump($OpenTabs);
    $valsToValadate = $_POST;
    $errorsTL = []; // id then msg as key pair
    //var_dump($editedVals); //test 
    
    foreach ($valsToValadate as $column => $valToCheck){
        //echo $column." ";//test
        if($valToCheck == "" And $column != "deadline" ){ 
            $msg = $column." Must Not Be Empty";
            $errorsTL[$column] = $msg; 
        }elseif($column == "name"){
            if(nameInDB($nameToCheck,$con)){
                $msg = $valToCheck."Task List Already exists";
                $errorsTL[$column] = $msg; 
            }
        }
        // elseif($column == "deadline" And new DateTime($valToCheck) < date("d/m/Y h:i")  ){
        //     echo" Deadline test ran";//test 
        //     var_dump(new DateTime($valToCheck)); //test
        //     echo date("d/m/Y h:i"); //test 
        //     var_dump(new DateTime($valToCheck) < new DateTime(date("d/m/Y h:i"))); //test
        //     $msg = "Deadline Must Be In The Future";
        //     $errorsTL[$column] = $msg; 
        // }
    }
    if(empty($errorsTL)){ // ie no errors so valadation passed
        $valsToValadate["priority"] = array_search($valsToValadate["priority"],$priorities) + 1; // index is used as encoded priority numeric value  
        $valsToValadate["ownerID"] = $_SESSION["userID"];
        unset($valsToValadate["submitNTL"]);
        unset($valsToValadate["tokenNTL"]);
        //var_dump($valsToValadate); //test
        $newTaskListID = addRow($valsToValadate, "tasklist", $conn);
        // so the new taskList is opened 
        array_push($OpenTabs, $newTaskListID);
        $currentDisplay = $newTaskListID;
        // unseting $valsToValadate to not be used in the new task list tab as it it is finshed with now
        unset($valsToValadate);
        //close new Task List Tab
        unset($OpenTabs[0]);
        unset($_POST);
        //var_dump( $OpenTabs);
    }

}

$tokenNTL =  md5(uniqid(rand(), true)); //generates a random token to be used to check that the Post is the first request going throuh valadation, hence why its after valadation
$_SESSION["tokenNTL"] = $tokenNTL;
head($pageName); // from functions.php, echoes out the head tags


?>
<script>
//---------------------------------------------------- globle variables ----------------------------------------------------

var openTabsIDQueue = []; 
const maxTabsOpen = 10; 
var currentTasklist; 
const priorities = <?php echo json_encode($priorities);?>; // so there is one priorty list
//----------------------------------------------------Functions ----------------------------------------------------
function openTaskList(taskListID){
    
    if(openTabsIDQueue.length >= maxTabsOpen){
        closeTaskList(openTabsIDQueue[0]); // removes the tasklist opened the first
    }

    shiftTab(taskListID, openTabsIDQueue.length, maxTabsOpen);// sets position of opening tab
    openTabsIDQueue.push(String(taskListID)); // converts to string so comparisons work 
    shiftTab("all", openTabsIDQueue.length, maxTabsOpen);// resteing teh All tabs position at the end
    
    taskListTab = document.getElementById(taskListID + "Tab");
    taskListTab.classList.remove("hidden");
    changeTab(taskListID); 
    // console.log(taskListID);//test
    // console.log(taskListID != "newTaskList"); //test
    if (taskListID != "all" && taskListID != "newTaskList" ){//change open tab button to close
        document.getElementById("openButton"+taskListID).classList.add("hidden");
        document.getElementById("closeButton"+taskListID).classList.remove("hidden");
    }
}


function closeTaskList(taskListID){
    // getting them as the same data types so comparisons work
    taskListID = String(taskListID);
    //hide tasklist Tab
    
    //console.log("tasklist ID: "+ taskListID);//test
    taskListTab = document.getElementById(taskListID + "Tab");
    taskListTab.classList.add("hidden");
    //change view
    changeTab("all"); 
    //change close tab button to open
    if (taskListID != "all" && taskListID != "newTaskList" ){//change open tab button to close
        document.getElementById("openButton"+taskListID).classList.remove("hidden");
        document.getElementById("closeButton"+taskListID).classList.add("hidden");
    }
    //change postition for all tabs after tab closed and remove from openTabsIDQueue
    var indexToRemove = openTabsIDQueue.indexOf(taskListID);
    //console.log("indexToRemove: " +  indexToRemove);
    //console.log("Open tabs before sliece:" );//test
    openTabsIDQueue.splice(indexToRemove,1);
    //console.log("Open tabs after sliece:"); //test
    //console.log(openTabsIDQueue);//test
    var i = 0; 
    for(var tab of openTabsIDQueue){
        console.log("Close Tab Shift: " + tab); //test 
        shiftTab(tab,i, maxTabsOpen);
        i++;
    }
    shiftTab("all",openTabsIDQueue.length, maxTabsOpen);//as all tab is not included in the openTabsIDQueue

}

function changePriority(elementIDToChange, updateDB=false){
    var priorityButton = document.getElementById(elementIDToChange)
    if(updateDB){
        var currentPriorityIndex = priorities.indexOf(priorityButton.innerHTML.replace(" Priority",""));
    }else{
        var currentPriorityIndex = priorities.indexOf(priorityButton.value);
    }
    
    console.log(currentPriorityIndex);//test
    console.log(priorities.length);//test

    if(currentPriorityIndex == (priorities.length - 1 )){
        var priorityIndexToGet = 0; 
    }else{
        var priorityIndexToGet = currentPriorityIndex + 1; 
    }
    console.log(priorityIndexToGet);//test
    console.log(priorities[priorityIndexToGet]);//test
    if(updateDB){
        priorityButton.innerHTML = priorities[priorityIndexToGet] + " Priority";
    }else{
        priorityButton.value = priorities[priorityIndexToGet];
    }
    // change style
    switch (priorityIndexToGet){
        case 0:
            priorityButton.classList.remove("green");
            priorityButton.classList.add("red");
            break;
        case 1:
            priorityButton.classList.remove("red");
            priorityButton.classList.add("amber");
            break;
        case 2:
            priorityButton.classList.remove("amber");
            priorityButton.classList.add("green");
            break;
    }
}



function newTaskList(){
    if(!openTabsIDQueue.includes("newTaskList")){ // so dubble clicks dont mess up the tab positions 
        openTaskList("newTaskList");
    }

    
}

function deleteTaskList(tasklistIDToDelete){
    
    if (confirm("are you sure?")) {
        //visual 
        closeTaskList(tasklistIDToDelete);// if open 
        document.getElementById("allRow"+tasklistIDToDelete).classList.add("hidden");
        //from data base - using ajax 
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("txtHint").innerHTML = this.responseText;
        }
        };
        xmlhttp.open("GET", "AJAXdelete.php?ID=" + tasklistIDToDelete +"&table=tasklist", true);
        xmlhttp.send()
    } 
    
    

}


</script>
<?php
//deleteID("71", "tasklist", $conn); //test
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


// echo "</br></br>"; //test
//var_dump($taskLists); //test

// ---------------------------------------------------- getting data for display  -------------------------------------------------

$qry = "SELECT ID, `name`, deadline, collab, `priority`, ownerID  FROM taskList WHERE ownerID = ?;"; 
$stmt = $conn->prepare($qry);
$stmt->execute([$_SESSION["userID"]]);
$taskLists = $stmt->fetchAll(PDO::FETCH_ASSOC);
//var_dump($taskLists); //test
foreach($taskLists as $row => $vals){
    $taskLists[$row] = new TaskList($vals, $conn);

}

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
            <?php if($taskList->name == "Genral") {
                array_push($OpenTabs, $taskList->ID);
            }
        
        }// taskList foreach close
        ?>
        <!-- Other tabs -->
        <button onclick="changeTab('newTaskList')" id="newTaskListTab" class="tab hidden">New Task List</button>
        
        <!-- the all tab which is allways last -->
        <button onclick="changeTab('all')" id="allTab" class="tab selected ">All</button>
        
        <!-- <script>openTabsIDQueue.length = shiftTab('all', openTabsIDQueue.length) -1 // so add is not counted as a tab </script> -->
        </div>

        <!-- genral taskList Code  -->
        <?php
            foreach($taskLists as $taskList){
                // echo "test1: ". $taskList->ID;
                // echo "test2: ". var_dump($taskList);
        ?>
            
            <div id="<?php echo $taskList->ID; ?>Container" class="hidden">
                <button class="button">Deadline: <?php echo yesOrNo($taskList->deadline); ?> </button>
                <button class="button collabColour">Make Collab</button>
                <button onclick="changePriority('<?php echo $taskList->ID; ?>priorityTL', true )" class='button' id="<?php echo $taskList->ID; ?>priorityTL"><?php echo getPriorityName($taskList->priority,$priorities)." priority"?></button>
                <button onclick="deleteTaskList(<?php echo $taskList->ID; ?>)" class="button red">Delete</button>

                <?php
                // ------------- tasks -------------
                if(count($taskList->tasks) == 0){
                    echo "<br/>WOW such emptiness<br/> ";
                    echo"<button class='button green'>New Task</button>";
                }else{
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
                }// else statement close 
                ?>
            </div>
        <?php
        }// taskList foreach close
        ?>
        <!-- newTaskList -->
        <div id="newTaskListContainer" class = "hidden">
            <form action="mainPage.php" method="post">

                <input type="hidden" name="tokenNTL" value="<?php echo $tokenNTL; ?>" />

                <div class="txtLeft"><label for="taskListName">Name</label></div>
                <input type="text"name="taskListName" id="taskListName" value="<?php if(isset($valsToValadate["taskListName"])){echo $valsToValadate["taskListName"];}?>">
                <div id="taskListNameError"></div>

                <div class="txtLeft"><label for="deadline">Deadline</label></div>
                <input type="date" min="<?php echo date("d-m-Y")?>" name="deadline" id="deadline" value="<?php if(isset($valsToValadate["deadline"])){echo $valsToValadate["deadline"];}?>">
                <div id="deadlineError"></div>

                <div class="txtLeft"><label for="priority">Priority</label></div>
                <input onclick="changePriority('priority')" type='text' name='priority' id='priority' class='button' value="<?php if(isset($valsToValadate["priority"])){echo $valsToValadate["priority"];}else{echo'medium';}?>"readonly>
                <div id="priorityError"></div>

                <input type="submit" name='submitNTL' id='submitNTL' class="green"value="Create!">
            </form>
            <script> errorMsg(<?php if (isset($errorsTL)){ echo json_encode($errorsTL);} // need the json encode part ?>)  </script> 
            <button onclick="closeTaskList('newTaskList')" class="button red">Cancle</button>
        </div>

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
                        echo"<tr id='allRow".$taskList->ID."'>";
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
        <?php
        //var_dump($OpenTabs);
        foreach($OpenTabs as $Tab){
            echo "openTaskList('".$Tab."'); ";
        }
        ?>
        changeTab("<?php if (isset($currentDisplay)){ echo $currentDisplay;}else{echo "all";} ?>")
        // closing new task List tab if opended and new task list is created
        </script>
             
    </div>


    <?php footer(); ?>
</body>

</html>