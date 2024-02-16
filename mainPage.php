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
<?php

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
        }
       

        // seting tasks in the task list
        $qry = "SELECT ID, `name`, deadline, `priority` FROM task WHERE ID = ?;"; 
        $stmt = $con->prepare($qry);
        $stmt->execute([$this->ID]);
        $this->tasks = $stmt->fetchAll(PDO::FETCH_ASSOC); 
        //var_dump($this->tasks); //test
        foreach($this->tasks as $row => $vals){
            $tasks[$row] = new Task($vals, $con);

        }
        //echo "</br></br>";
        //var_dump($this->tasks); //test
    }
    

}

$qry = "SELECT ID, `name`, deadline, collab, `priority`, ownerID  FROM taskList WHERE ownerID = ?;"; 
$stmt = $conn->prepare($qry);
$stmt->execute([$_SESSION["userID"]]);
$taskLists = $stmt->fetchAll(PDO::FETCH_ASSOC);
var_dump($taskLists); //test
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
            <button onclick="changeTab('genral')" id="genralTab" class="tab first">Genral</button>
            <button onclick="changeTab('all')" id="allTab" class="tab selected tab2">All</button>
        </div>
        <!-- genral taskList Code  -->
        <div id="genralContainer"  class="hidden">

        </div>

        <!-- All task lists  -->
        <div id="allContainer" class="showing">

        </div>
        
        <script>changeTab("<?php if (isset($currentDisplay)){ echo $currentDisplay;}else{echo "all";} ?>")</script>
               
    </div>


    <?php footer(); ?>
</body>

</html>