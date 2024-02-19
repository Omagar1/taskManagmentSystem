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
//----------------------------------------------------Functions ----------------------------------------------------
function openTaskList(){
    // create new tab with selected tasklist
    //create new section with selected tasklist
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
        $qry = "SELECT ID, `name`, weighting, complete, dateTimeCompleted, completedBy FROM stage WHERE ID = ?;"; 
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
        var_dump($this->tasks); //test
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
var_dump($taskLists); //test

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
            
        <?php
        }// taskList foreach close
        ?>
        <button onclick="changeTab('all')" id="allTab" class="tab selected tab2">All</button>
        </div>

        <!-- genral taskList Code  -->
        <?php
            foreach($taskLists as $taskList){
                // echo "test1: ". $taskList->ID;
                // echo "test2: ". var_dump($taskList);
        ?>
            
            <div id="<?php echo $taskList->ID; ?>Container"  class="hidden">
                <?php
                foreach($taskList->tasks as $task){
                    //echo"I Ran";//test
                ?>
                    <div id="<?php echo $task->ID; ?>" class="taskContainer">
                        <h4><?php echo$task->name;?></h4>
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
                                <?php echo$task->deadline;?>
                                </td>
                            </tr>
                            
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
                        <th>name</th>
                        <th>deadline</th>
                        <th>collab</th>
                        <th>priority</th>
                        <th>ownerID</th>
                        <th>open?</th>
                    </tr>
                
                <?php
                    foreach($taskLists as $taskList){
                        //var_dump($taskList); //test
                        echo"<tr>";
                        echo "<td>" . $taskList->name . "</td>";
                        echo "<td>" . $taskList->deadline . "</td>";
                        echo "<td>" . yesOrNo($taskList->collab) . "</td>";
                        echo "<td>" . $taskList->priority . "</td>";
                        echo "<td>" . $taskList->ownerID . "</td>";
                        echo "<td><button class='button green'>+</button></td>";
                        echo"</tr>";
                    }
                ?>
                
            </table>
        </div>
        
        <script>changeTab("<?php if (isset($currentDisplay)){ echo $currentDisplay;}else{echo "all";} ?>")</script>
               
    </div>


    <?php footer(); ?>
</body>

</html>