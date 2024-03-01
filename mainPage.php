<?php
ob_start(); // so that redirect works 
session_start();
require "functions.php";
require_once "dbConnect.php";
// require("editRowProcess.php"); not implemented yet 
require("addRowProcess.php");
require "deleteRowProcess.php";
require "editRowProcess.php"
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

var_dump($_POST);//test
//var_dump($_SESSION["tokenNTL"] == $_POST["tokenNTL"]); //test
if ((isset($_POST["tokenNTL"]) And $_SESSION["tokenNTL"] == $_POST["tokenNTL"]) Or (isset($_POST["tokenETL"]) And $_SESSION["tokenETL"] == $_POST["tokenETL"]) Or (isset($_POST["tokenNTK"]) And $_SESSION["tokenNTK"] == $_POST["tokenNTK"] Or (isset($_POST["tokenETK"]) And $_SESSION["tokenETK"] == $_POST["tokenETK"]) )){
    
    echo " valadation Ran"; //test
    
    //var_dump($OpenTabs);
    $valsToValadate = $_POST;
    $errorsTL = []; // id then msg as key pair
    //var_dump($editedVals); //test 

    if(isset($_POST["tokenNTL"])){
        $endtag = "NTL";
        array_push($OpenTabs,"newTaskList");
        $currentDisplay = "newTaskList";
    }elseif(isset($_POST["tokenETL"])){
        $endtag = "";
    }elseif(isset($_POST["tokenNTK"])){
        $endtag = "NTK";
        array_push($OpenTabs,"newTask");
        $currentDisplay = "newTask";
    }elseif(isset($_POST["tokenETK"])){
        $endtag = "ETK";
        array_push($OpenTabs,"newTask");
        $currentDisplay = "newTask";
    }
    
    
    foreach ($valsToValadate as $column => $valToCheck){
        //removing the tagfrom the coloumn name
        unset($valsToValadate[$column]);
        $column = str_replace($endtag,"",$column);
        $valsToValadate[$column] = $valToCheck;

        //echo $column." ";//test
        if($valToCheck == "" And $column != "deadline" ){ 
            $msg = $column." Must Not Be Empty";
            $errorsTL[$column] = $msg; 
        }elseif($column == "name"){
            if(nameInDB($valToCheck,$conn)){
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
    // valadation passsed
    //removing the defult value of dedline as it should be stored as null
    //var_dump($valsToValadate["deadline"]);//test
    if(isset($valsToValadate["deadline"]) And ($valsToValadate["deadline"] == "0000-00-00 00:00:00" Or $valsToValadate["deadline"] == "" )){
        $valsToValadate["deadline"] = null;// must be set as so you can remove a deadline
    }
    if(empty($errorsTL) And isset($_POST["tokenNTL"]) ){ // new task list
        $valsToValadate["priority"] = array_search($valsToValadate["priority"],$priorities) + 1; // index is used as encoded priority numeric value  
        $valsToValadate["ownerID"] = $_SESSION["userID"];
        unset($valsToValadate["submit"]);
        unset($valsToValadate["token"]); 
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
    }elseif(empty($errorsTL) And isset($_POST["tokenETL"])){ // edit Task List 
        unset($valsToValadate["submitETL"]);
        unset($valsToValadate["tokenETL"]);
        editRow($valsToValadate, "tasklist", $conn);
        
        $currentDisplay = $valsToValadate["ID"];
        // unseting $valsToValadate to not be used in the new task list tab as it it is finshed with now
        unset($valsToValadate);
    }elseif(empty($errorsTL) And isset($_POST["tokenNTK"])){ //new task
        $valsToValadate["priority"] = array_search($valsToValadate["priority"],$priorities) + 1; // index is used as encoded priority numeric value  
        unset($valsToValadate["submit"]);
        unset($valsToValadate["token"]);

        addRow($valsToValadate, "task", $conn);
        // sets the current display to the task list the new task is in 
        array_push($OpenTabs,$valsToValadate["taskListID"]);
        $currentDisplay = $valsToValadate["taskListID"];
        // unseting $valsToValadate to not be used in the new task list tab as it it is finshed with now
        unset($valsToValadate);
        //close new Task List Tab
        unset($OpenTabs[0]);
        unset($_POST);
    }elseif(empty($errorsTL) And isset($_POST["tokenETK"])){ //editing task
        echo "edit tasks Ran";// test
        unset($valsToValadate["submit"]);
        unset($valsToValadate["token"]);
        editRow($valsToValadate, "task", $conn);
        
        //$currentDisplay = $result["tasklistID"];
        // unseting $valsToValadate to not be used in the new task list tab as it it is finshed with now
        unset($valsToValadate);
    }

}
//generates a random tokens to be used to check that the Post is the first request going throuh valadation, hence why its after valadation
$tokenNTL =  md5(uniqid(rand(), true)); // for new task lists
$tokenETL =  md5(uniqid(rand(), true)); // for editing task lists

$tokenNTK =  md5(uniqid(rand(), true)); // for new tasks
$tokenETK =  md5(uniqid(rand(), true)); // for editing tasks

$_SESSION["tokenNTL"] = $tokenNTL; // for new task lists
$_SESSION["tokenETL"] = $tokenETL; // for editing task lists

$_SESSION["tokenNTK"] = $tokenNTK; // for new tasks
$_SESSION["tokenETK"] = $tokenETK; // for editing task 

head($pageName); // from functions.php, echoes out the head tags


?>
<script>
//---------------------------------------------------- globle variables ----------------------------------------------------

var openTabsIDQueue = []; 
const maxTabsOpen = 10; 
var currentTasklist; 
const priorities = <?php echo json_encode($priorities);?>; // so there is one priorty list

const tokenETL = "<?php echo $tokenETL ?>"
const tokenETK = "<?php echo $tokenETK ?>"
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
    if (taskListID != "all" && taskListID != "newTaskList" && taskListID != "newTask" ){//change open tab button to close
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
    if (taskListID != "all" && taskListID != "newTaskList" && taskListID != "newTask" ){//change open tab button to close
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

function hideTask(IDtoHide){
    document.getElementById("task" + IDtoHide).classList.add("hidden");
}

function changePriority(elementIDToChange, updateData=null, ){
    var priorityButton = document.getElementById(elementIDToChange)
    // the state of updateData is used as a boolean to see if upadating the DB is wanted 
    console.log("updateDate: "+ updateData); //test
    console.log("typeof(updateData): "+ typeof(updateData));

    if(typeof(updateData) != null){
        var currentPriorityIndex = priorities.indexOf(priorityButton.innerHTML.replace(" Priority",""));
        //console.log(priorityButton.innerHTML.replace(" Priority",""))//test
    }else{
        var currentPriorityIndex = priorities.indexOf(priorityButton.value);
    }
    
    console.log(currentPriorityIndex);//test
    //console.log(priorities.length);//test

    if(currentPriorityIndex == (priorities.length - 1 )){
        var priorityIndexToGet = 0; 
    }else{
        var priorityIndexToGet = currentPriorityIndex + 1; 
    }
    console.log(priorityIndexToGet);//test
    console.log(priorities[priorityIndexToGet]);//test
    if(typeof(updateData) != null){
        priorityButton.innerHTML = priorities[priorityIndexToGet] + " Priority";
        // upadating the DB
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.open("GET", "AJAXeditRow.php?ID=" + updateData["ID"] + "&priority=" + (priorityIndexToGet + 1)  +"&table=" + updateData["table"], true);
        xmlhttp.send()
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
function newTask(){
    if(!openTabsIDQueue.includes("newTask")){ // so dubble clicks dont mess up the tab positions 
        openTaskList("newTask");
    }  
}


function useAJAXdelete(IDToDelete,tableFrom){
    
    if (confirm("are you sure?")) {
        //visual 
        if(tableFrom == "tasklist"){
            closeTaskList(IDToDelete);// if open 
            document.getElementById("allRow"+IDToDelete).classList.add("hidden");
        }else if(tableFrom == "task"){
            hideTask(IDToDelete);
        }
        
        //from data base - using ajax 
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            // document.getElementById("msg").innerHTML = this.responseText;
            console.log("delete responce: "+this.responseText)
        }
        };
        xmlhttp.open("GET", "AJAXdelete.php?ID=" + IDToDelete + "&table=" + tableFrom, true);
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
        <button onclick="changeTab('newTask')" id="newTaskTab" class="tab hidden">New Task </button>
        <!-- the all tab which is allways last -->
        <button onclick="changeTab('all')" id="allTab" class="tab selected ">All</button>
        
        <!-- <script>openTabsIDQueue.length = shiftTab('all', openTabsIDQueue.length) -1 // so add is not counted as a tab </script> -->
        </div>

        <!--  taskList Code  -->
        <?php
            foreach($taskLists as $taskList){
                // echo "test1: ". $taskList->ID;
                // echo "test2: ". var_dump($taskList);
        ?>
            
            <div id="<?php echo $taskList->ID; ?>Container" class="hidden">
                <div class = "taskListHeader">
                    <h2 onclick = "allowEdit('name' , <?php echo $taskList->ID;?>, 'TL')" class="editButtons editButtonsID<?php echo $taskList->ID;?>TL"><?php echo $taskList->name; ?></h2>
                    <input type = "text" id = "nameInput<?php echo $taskList->ID;?>TL" class =" inputbutton hidden editInputs editInputsID<?php echo $taskList->ID;?>TL" name = "nameInput<?php echo $taskList->ID;?>TL"  onclick = "allowEdit('name' , <?php echo $taskList->ID;?>,'TL')" value = "<?php echo $taskList->name; ?>"/>
                </div>
                
                <button class="button editButtons editButtonsID<?php echo $taskList->ID;?>TL" onclick = "allowEdit('deadline' , <?php echo $taskList->ID; ?>,'TL')">Deadline: <?php echo (isset($taskList->deadline))? $taskList->deadline : "none"  ?> </button>
                <input type = "datetime-local" min = "<?php date("d-m-Y h:i:s")?>" id = "deadlineInput<?php echo $taskList->ID;?>TL" class =" inputbutton hidden editInputs editInputsID<?php echo $taskList->ID;?>TL" name = "deadlineInput<?php echo $taskList->ID;?>TL"  onclick = "allowEdit('deadline' , <?php echo $taskList->ID;?>,'TL')" value = "<?php echo $taskList->deadline; ?>"/>
                
                <button class="button collabColour">Make Collab</button>
                <button onclick="changePriority('<?php echo $taskList->ID; ?>priorityTL',{ID: '<?php echo $taskList->ID; ?>',table: 'tasklist'} )" class='button' id="<?php echo $taskList->ID; ?>priorityTL"><?php echo getPriorityName($taskList->priority,$priorities)." Priority"?></button>
                <button onclick="useAJAXdelete(<?php echo $taskList->ID; ?>, 'tasklist')" class="button red">Delete</button>

                <?php
                // ------------- tasks -------------
                if(count($taskList->tasks) == 0){
                    echo "<br/>WOW such emptiness<br/> ";
                    echo"<button onclick='newTask()' class='button green'>New Task</button>";
                }else{
                    foreach($taskList->tasks as $task){
                        //echo"I Ran";//test
                ?>
                        <div id="task<?php echo $task->ID;?>" class="taskContainer">
                            <div class="taskHeader">
                                <h2 class="editButtons editButtonsID<?php echo $task->ID;?>TK"><?php echo $task->name;?></h2>
                                <input  onclick = "allowEdit('name' , <?php echo $task->ID;?>,'TK')" type = "text" id = "nameInput<?php echo $task->ID;?>TK" class =" inputbutton hidden editInputs editInputsID<?php echo $task->ID;?>TK" name = "nameInput<?php echo $task->ID;?>TK"  value = "<?php echo $task->name; ?>"/>
                                <div id="nameTKError"></div>

                                <button onclick = "allowEdit('name' , <?php echo $task->ID;?>,'TK')"  class="clear button" >edit</button>
                                <!-- <button  class="clear">MakeRepeat</button> -->
                                <button onclick="useAJAXdelete(<?php echo $task->ID;?>,'task')" class="clear button">X</button>
                            </div>

                            <table class="taskTable">
                                <tr class="taskTr">
                                    <td class="taskTd tableDisplay">
                                        priority
                                    </td>
                                    <td class="taskTd tableDisplay">
                                        <button onclick="changePriority('<?php echo $task->ID; ?>priorityTL',{ID: '<?php echo $task->ID; ?>',table: 'task'} )" class='button' id="<?php echo $task->ID; ?>priorityTL"><?php echo getPriorityName($task->priority,$priorities)." Priority"?></button>
                                        <div id="priorityTKError"></div>

                                    </td>
                                </tr>
                                <tr class="taskTr">
                                    <td class="taskTd tableDisplay">
                                        deadline
                                    </td>
                                    <td class="taskTd tableDisplay">
                                        <p class="editButtons editButtonsID<?php echo $task->ID;?>TK"><?php echo $task->deadline;?></p>
                                        <input onclick = "allowEdit('deadline' , <?php echo $task->ID;?>,'TK')" type = "text" id = "deadlineInput<?php echo $task->ID;?>TK" class =" inputbutton hidden editInputs editInputsID<?php echo $task->ID;?>TK" name = "deadlineInput<?php echo $task->ID;?>TK"  value = "<?php echo $task->deadline; ?>"/>
                                        <div id="deadlineTKError"></div>

                                    </td>
                                </tr>

                            </table>
                            <!-- ------------- stages ------------- -->
                            <table class="clear">
                                <tr>
                                    <th class="clear textWhite"><b>Stages</b></th>
                                    <th class="clear textWhite"><b>weighting</b></th>
                                </tr>
                                <?php foreach($task->stages as $stage):?>
                                    <tr>
                                        <td class='clear'>
                                            <?php echo $stage->name?>
                                        </td>
                                        <td class='clear'>
                                            <?php echo $stage->weighting?>
                                        </td>
                                        <td class='clear'>
                                            <?php echo yesOrNo($stage->complete)?>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
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

                <div class="txtLeft"><label for="deadlineNTL">Deadline</label></div>
                <input type="datetime-local" min="<?php echo date("d-m-Y h:i:s")?>" name="deadlineNTL" id="deadlineNTL" value="<?php if(isset($valsToValadate["deadlineNTL"])){echo $valsToValadate["deadlineNTL"];}?>">
                <div id="deadlineNTLError"></div>

                <div class="txtLeft"><label for="priorityNTL">Priority</label></div>
                <input onclick="changePriority('priorityNTL')" type='text' name='priorityNTL' id='priorityNTL' class='button' value="<?php if(isset($valsToValadate["priorityNTL"])){echo $valsToValadate["priorityNTL"];}else{echo'medium';}?>"readonly>
                <div id="priorityNTLError"></div>

                <input type="submit" name='submitNTL' id='submitNTL' class="green"value="Create!">
            </form>
            <script> errorMsg(<?php if (isset($errorsTL)){ echo json_encode($errorsTL);} // need the json encode part ?>)  </script> 
            <button onclick="closeTaskList('newTaskList')" class="button red">Cancle</button>
        </div>

        <!-- newTask -->
        <div id="newTaskContainer" class = "hidden">
            <form action="mainPage.php" method="post">

                <input type="hidden" name="tokenNTK" value="<?php echo $tokenNTK; ?>" />

                <div class="txtLeft"><label for="nameNT">Task Name</label></div>
                <input type="text"name="nameNT" id="nameNT" value="<?php if(isset($valsToValadate["nameNT"])){echo $valsToValadate["nameNT"];}?>">
                <div id="nameNTError"></div>

                <div class="txtLeft"><label for="taskListIDNT">Belongs to Task List:</label></div>
                <select type="text"name="taskListIDNT" id="taskListIDNT">
                <?php
                foreach ($taskLists as $taskList){
                    if(isset($valsToValadate["BTtaskList"]) And $taskList->ID == $valsToValadate["BTtaskList"]){
                        echo "<option value=".$taskList->ID."selected>".$taskList->name."</option>";
                    }else{
                        echo "<option value=".$taskList->ID.">".$taskList->name."</option>";
                    }
                }
                
                ?>
                </select>
                <div id="taskListIDNTError"></div>

                <div class="txtLeft"><label for="deadlineNT">Deadline</label></div>
                <input type="datetime-local" min="<?php echo date("d-m-Y h:i:s")?>" name="deadlineNT" id="deadlineNT" value="<?php if(isset($valsToValadate["deadlineNT"])){echo $valsToValadate["deadlineNT"];}?>">
                <div id="deadlineNTError"></div>

                <div class="txtLeft"><label for="priorityNT">Priority</label></div>
                <input onclick="changePriority('priorityNT')" type='text' name='priorityNT' id='priorityNT' class='button' value="<?php if(isset($valsToValadate["priorityNT"])){echo $valsToValadate["priorityNT"];}else{echo'medium';}?>"readonly>
                <div id="priorityNTError"></div>

                <input type="submit" name='submitNT' id='submitNT' class="green"value="Create!">
            </form>
            <script> errorMsg(<?php if (isset($errorsT)){ echo json_encode($errorsT);} // need the json encode part ?>)  </script> 
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
                        <th>Owner</th>
                        <th></th>
                    </tr>
                
                <?php foreach($taskLists as $taskList): ?> 
                        <tr id='allRow".$taskList->ID."'> 
                            <td><?php echo $taskList->name ?></td>
                            <td><?php echo yesOrNo($taskList->deadline) ?></td>
                            <td><?php echo yesOrNo($taskList->collab) ?></td>
                            <td><button onclick="changePriority('priority<?php echo $taskList->ID ?>ATL',{ID: '<?php echo $taskList->ID; ?>',table: 'tasklist'})"  id='priority<?php echo $taskList->ID ?>ATL' class='button'><?php echo getPriorityName($taskList->priority, $priorities)  ?></td>
                            <td><?php echo ($taskList->ownerID == $_SESSION["userID"])? "you" : getNameFromID($taskList->ownerID,$conn) ?></td>
                            <td>
                                <button onclick='openTaskList("<?php echo $taskList->ID ?>")' id='openButton<?php echo $taskList->ID ?>' class='button green'>Open?</button>
                                <button onclick='closeTaskList("<?php echo $taskList->ID ?>")' id='closeButton<?php echo $taskList->ID ?>' class='button red hidden'>Close?</button>
                            </td>
                        </tr>";
                <?php endforeach ?>
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


    
</body>
<?php footer(); ?>
</html>