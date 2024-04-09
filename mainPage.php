<?php
ob_start(); // so that redirect works 
session_start();
require "functions.php";
require_once "dbConnect.php";

require "addRowProcess.php";
require "deleteRowProcess.php";
require "editRowProcess.php";
require "addCollaborator.php";
?>

<!DOCTYPE html>
<html lang="en">
<?php

// $_SESSION["previous"] = []; // initalising the Previous Stack
$prioritiesName = ["high","medium","low"]; 
$prioritiesColour = ["red","amber","green"]; // medium has no colour as it uses the default colour of the element
$pageName = basename($_SERVER["PHP_SELF"]); // getting the name of the page so head can add it to the Previous stack
$_SESSION["currentPage"] = $pageName; 

function getPriorityVal($PriorityVal, $priorities,){
    return $priorities[$PriorityVal-1];
}

unset($_SESSION["currentDisplay"]);// so an unopened display is not set as the current display 
// ---------------------------------------------------- task List Validation -------------------------------------------------
$OpenTabs = [] ;

function nameInDB($nameToCheck,$con){ // change ?
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
//echo $_SESSION["currentDisplay"]; //test 
//var_dump($_SESSION["tokenNTL"] == $_POST["tokenNTL"]); //test
if ((isset($_POST["tokenNTL"]) And $_SESSION["tokenNTL"] == $_POST["tokenNTL"]) 
Or (isset($_POST["tokenETL"]) And $_SESSION["tokenETL"] == $_POST["tokenETL"]) 
Or (isset($_POST["tokenNTK"]) And $_SESSION["tokenNTK"] == $_POST["tokenNTK"])
Or (isset($_POST["tokenETK"]) And $_SESSION["tokenETK"] == $_POST["tokenETK"]) 
Or (isset($_POST["tokenNSG"]) And $_SESSION["tokenNSG"] == $_POST["tokenNSG"]) 
Or (isset($_POST["tokenESG"]) And $_SESSION["tokenESG"] == $_POST["tokenESG"])){
    
    echo "valadation Ran"; //test
    
    //var_dump($OpenTabs);
    $valsToValadate = $_POST;
    $errors = []; // id then msg as key pair
    $endtag ="";
    //var_dump($editedVals); //test 

    if(isset($_POST["tokenNTL"])){
        $endtag = "NTL";
        array_push($OpenTabs,"newTaskList");
        $_SESSION["currentDisplay"] = "newTaskList";
    }elseif(isset($_POST["tokenETL"])){
        array_push($OpenTabs, $valsToValadate["ID"]);
        $_SESSION["currentDisplay"] = $valsToValadate["ID"];
        $endtag = "ETL";
    }elseif(isset($_POST["tokenNTK"])){
        $endtag = "NTK";
        array_push($OpenTabs,"newTask");
        $_SESSION["currentDisplay"] = "newTask";
    }elseif(isset($_POST["tokenETK"])){
        $endtag = "ETK";
        array_push($OpenTabs,"newTask");
        $_SESSION["currentDisplay"] = "newTask";
    }
    
    
    foreach ($valsToValadate as $column => $valToCheck){
        //removing the tagfrom the coloumn name
        unset($valsToValadate[$column]);
        $column = str_replace($endtag,"",$column);
        $valsToValadate[$column] = $valToCheck;

        //echo $column." ";//test
        if($valToCheck == "" And $column != "deadline" ){ 
            $msg = ucfirst($column)." Must Not Be Empty";
            $errors[$column.$endtag.$valsToValadate["ID"]] = $msg;
            var_dump($errors); 
        }elseif($column == "name"){
            if(nameInDB($valToCheck,$conn)){
                $msg = $valToCheck."Task List Already exists";
                $errors[$column] = $msg; 
            }
        }elseif($column == "weightig"){
            if($valToCheck > 100 ){
                $msg = $column." Must not be over 100";
                $errors[$column] = $msg; 
            }elseif($valToCheck < 0){
                $msg = $column." Must not be under 0";
                $errors[$column] = $msg; 
            }
        }
        // elseif($column == "deadline" And new DateTime($valToCheck) < date("d/m/Y h:i")  ){
        //     echo" Deadline test ran";//test 
        //     var_dump(new DateTime($valToCheck)); //test
        //     echo date("d/m/Y h:i"); //test 
        //     var_dump(new DateTime($valToCheck) < new DateTime(date("d/m/Y h:i"))); //test
        //     $msg = "Deadline Must Be In The Future";
        //     $errors[$column] = $msg; 
        // }
    }
    // valadation passsed
    //removing the defult value of dedline as it should be stored as null
    //var_dump($valsToValadate["deadline"]);//test
    if(isset($valsToValadate["deadline"]) And ($valsToValadate["deadline"] == "0000-00-00 00:00:00" Or $valsToValadate["deadline"] == "" )){
        $valsToValadate["deadline"] = null;// must be set as so you can remove a deadline
    } 
    //var_dump($errors);//test
    if(empty($errors) And isset($_POST["tokenNTL"]) ){ // new task list
        $valsToValadate["priority"] = array_search($valsToValadate["priority"],$prioritiesName) + 1; // index is used as encoded priority numeric value  
        $valsToValadate["ownerID"] = $_SESSION["userID"];
        unset($valsToValadate["submit"]);
        unset($valsToValadate["token"]); 
        //var_dump($valsToValadate); //test
        $newTaskListID = addRow($valsToValadate, "tasklist", $conn);
        // so the new taskList is opened 
        array_push($OpenTabs, $newTaskListID);
        $_SESSION["currentDisplay"] = $newTaskListID;
        // unseting $valsToValadate to not be used in the new task list tab as it it is finshed with now
        unset($valsToValadate);
        //close new Task List Tab
        unset($OpenTabs[0]);
        unset($_POST);
        //var_dump( $OpenTabs);
    }elseif(empty($errors) And isset($_POST["tokenETL"])){ // edit Task List 
        unset($valsToValadate["submit"]);
        unset($valsToValadate["token"]);
        editRow($valsToValadate, "tasklist", $conn);
        
        $_SESSION["currentDisplay"] = $valsToValadate["ID"];
        // unseting $valsToValadate to not be used in the new task list tab as it it is finshed with now
        unset($valsToValadate);
    }elseif(empty($errors) And isset($_POST["tokenNTK"])){ //new task
        echo "new task Ran";// test
        $valsToValadate["priority"] = array_search($valsToValadate["priority"],$prioritiesName) + 1; // index is used as encoded priority numeric value  
        unset($valsToValadate["submit"]);
        unset($valsToValadate["token"]);

        addRow($valsToValadate, "task", $conn);
        // sets the current display to the task list the new task is in 
        array_push($OpenTabs,$valsToValadate["taskListID"]);
        $_SESSION["currentDisplay"] = $valsToValadate["taskListID"];
        // unseting $valsToValadate to not be used in the new task list tab as it it is finshed with now
        unset($valsToValadate);
        //close new Task List Tab
        unset($OpenTabs[0]);
        unset($_POST);
    }elseif(empty($errors) And isset($_POST["tokenETK"])){ //editing task
        echo "edit tasks Ran";// test
        unset($valsToValadate["submit"]);
        unset($valsToValadate["token"]);
        editRow($valsToValadate, "task", $conn);
        
        //$_SESSION["currentDisplay"] = $result["tasklistID"];
        // unseting $valsToValadate to not be used in the new task list tab as it it is finshed with now
        unset($valsToValadate);
    }elseif(empty($errors) And isset($_POST["tokenNSG"])){
        echo "new stage Ran";// test
        unset($valsToValadate["tokenNSG"]);
        $result = addRow($valsToValadate, "stage", $conn);
        unset($valsToValadate);
    }elseif(empty($errors) And isset($_POST["tokenESG"])){
        echo "edit stage Ran";// test
        unset($valsToValadate["tokenESG"]);
        $result =  editRow($valsToValadate, "stage", $conn);
        unset($valsToValadate);
    }else{
        //var_dump($errors);//test
    }

}elseif (isset($_POST["tokenNCU"]) And $_SESSION["tokenNCU"] == $_POST["tokenNCU"]) {
    echo "NCU ran!";//test
    try{
        $_SESSION["currentDisplay"] = $_POST["taskListIDNCU"];

        $qry = "SELECT ID FROM user WHERE collabCode = :collabCode ";
        $stmt = $conn->prepare($qry);
        $stmt->bindParam('collabCode', $_POST["collabCodeNCU"]);
        $stmt->execute();
        $userID = $stmt->fetch()["ID"];
        $stmt->debugDumpParams(); //test
        echo "userID: ". var_dump($userID);//test
        echo "count: ".$stmt->rowCount();//test
        if($stmt->rowCount() == 1){
            addCollaborator($_POST["taskListIDNCU"], $userID, $conn); 
        }else{
            $errors["NCU"] = "Collab code does not Match with any users";
        }
        //$stmt->debugDumpParams(); //test
    } catch(PDOException $e){
        echo "Error : ".$e->getMessage();// dev error mesage
    }
}elseif(isset($_POST["tokenRC"]) And $_SESSION["tokenRC"] == $_POST["tokenRC"]){
    deleteID($_POST["taskListIDRC"],"taskListCollab",$conn,"taskListID"); // remove all the users
    $column["collab"] = false;
    $column["ID"] = $_POST["taskListIDRC"] ;
    editRow($column,"taskList",$conn);//removes changes to not collab any more
}
//generates a random tokens to be used to check that the Post is the first request going throuh valadation, hence why its after valadation
$tokenNTL =  md5(uniqid(rand(), true)); // for new task lists
$tokenETL =  md5(uniqid(rand(), true)); // for editing task lists

$tokenNTK =  md5(uniqid(rand(), true)); // for new tasks
$tokenETK =  md5(uniqid(rand(), true)); // for editing tasks

$tokenNSG =  md5(uniqid(rand(), true)); // for new stages 
$tokenESG =  md5(uniqid(rand(), true)); // for editing stages

$tokenNCU =  md5(uniqid(rand(), true)); // for new collab user
$tokenRC =  md5(uniqid(rand(), true)); // for romoving Collab on tasklists

$_SESSION["tokenNTL"] = $tokenNTL; // for new task lists
$_SESSION["tokenETL"] = $tokenETL; // for editing task lists

$_SESSION["tokenNTK"] = $tokenNTK; // for new tasks
$_SESSION["tokenETK"] = $tokenETK; // for editing task

$_SESSION["tokenNSG"] = $tokenNSG; // for new stages
$_SESSION["tokenESG"] = $tokenESG; // for editing stages

$_SESSION["tokenNCU"] = $tokenNCU; // for new collab user
$_SESSION["tokenRC"] = $tokenRC; // for romoving Collab on tasklists



head($pageName); // from functions.php, echoes out the head tags


?>
<script>
//---------------------------------------------------- globle variables ----------------------------------------------------

var openTabsIDQueue = []; 
const maxTabsOpen = 10; 
var currentTasklist; 
const prioritiesName = <?php echo json_encode($prioritiesName);?>; // so there is one priorty list
const userID = <?php echo $_SESSION["userID"];?>

const tokenETL = "<?php echo $tokenETL ?>"
const tokenETK = "<?php echo $tokenETK ?>"

const tokenNSG = "<?php echo $tokenNSG?>"
const tokenESG = "<?php echo $tokenESG?>"
//----------------------------------------------------Functions ----------------------------------------------------
function openTaskList(taskListID){
    
    if(openTabsIDQueue.length >= maxTabsOpen){ // if to many tabs open
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
    
    console.log("tasklist ID: "+ taskListID);//test
    taskListTab = document.getElementById(taskListID + "Tab");
    taskListTab.classList.add("hidden");
    console.log(taskListTab);//tets
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
    var priorityButton = document.getElementById(elementIDToChange);
    // the state of updateData is used as a boolean to see if upadating the DB is wanted 
    console.log("updateDate: "+ updateData); //test
    console.log("typeof(updateData): "+ typeof(updateData));

    if(typeof(updateData) == "object"){
        var currentPriorityIndex = prioritiesName.indexOf(priorityButton.innerHTML.replace(" Priority",""));
        //console.log(priorityButton.innerHTML.replace(" Priority",""))//test
    }else{
        var currentPriorityIndex = prioritiesName.indexOf(priorityButton.value);
    }
    
    console.log("currentPriorityIndex: "+currentPriorityIndex);//test
    //console.log(prioritiesName.length);//test

    if(currentPriorityIndex == (prioritiesName.length - 1 )){
        var priorityIndexToGet = 0; // sending the queue back to the begining
    }else{
        var priorityIndexToGet = currentPriorityIndex + 1; 
    }
    console.log("priorityIndexToGet: "+priorityIndexToGet);//test
    console.log(prioritiesName[priorityIndexToGet]);//test
    if(typeof(updateData) == "object"){
        priorityButton.innerHTML = prioritiesName[priorityIndexToGet] + " Priority";
        //db stuff
        var dataToDB = {ID: updateData["ID"], priority: priorityIndexToGet+1, table: updateData["table"]}; 
        result = useAJAXedit(dataToDB);
    }else{
        priorityButton.value = prioritiesName[priorityIndexToGet];
    }
    // change style
    console.log(prioritiesName);
    console.log("priorityIndexToGet: "+ priorityIndexToGet);
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

function hideStage(stageID){
    document.getElementById("stage"+stageID).remove();
}

function completeStage(stageID,){
    

    // getting data to send to db
    var dateTimeCompletedToSet;
    var completeButton = document.getElementById("complete" + stageID + "SG");
    var completeButtonOSD = document.getElementById("complete" + stageID + "SG-OSD");

    if (completeButton.classList.contains("green")){ // get currentVal based off if element has the green class
        currentVal = true; 
        dateTimeCompletedToSet = null;
        //visual stuff
        completeButton.classList.remove("green");
        completeButtonOSD.classList.remove("green");
        
    }else{
        currentVal = false;
        dateTimeCompletedToSet = getCurrentDateTime();
        console.log("current Date Time: " + dateTimeCompletedToSet )// test
        //visual stuff
        completeButton.classList.add("green");
        completeButtonOSD.classList.add("green");
        
    }
    
    console.log("currentVal: " + currentVal); // test 
    console.log("complete" + stageID + "SG"); // test 
    console.log(completeButton);  // test 
   
    
     
    // db stuff
    var updateData = {ID: stageID, complete: !currentVal, dateTimeCompleted: dateTimeCompletedToSet, completedBy: userID, table: "stage"}; 
    useAJAXedit(updateData);
}
function changeWeighting(taskID, numberExtra=-1){
    //numberExtra is for when a new stage is being added it is not set to -1 instead 1
    // why is numberExtra=-1 when not new stage and 1 when there is a new stage? 
    // numberOfStagesToConsider is based on the length of the list of elements with the class "weighting"+taskID+"SG",
    // the weighting row for new stage column has the  weighting"+taskID+"SG class so its weighting is changed correctly when displaded
    // but when its not displayd it will still be got by  getElementsByClassName("weighting"+taskID+"SG") hence the -1 when we dont want new Stage
    // the one comes from the fact that for the edit system to work there is two elements for each stage that needs to change the weightings but only one for the new stage tagfrom
    // and as we are dividing by two to get the correct amount of stages an even number is needed
    //the extra 1 simulates if the new stage had two elements with the class "weighting"+taskID+"SG" like the other elements 
    var weightingsToChange = document.getElementsByClassName("weighting"+taskID+"SG");
    console.log("weightingsToChange: "+ weightingsToChange); //test
    console.log("weightingsToChange num of : "+ weightingsToChange.length); //test
    var numberOfStagesToConsider = weightingsToChange.length + numberExtra ;// to be used when a stage is uneven weighted to calculate the new evenWeighting of the rest of the stages// its divided by 2 as weightingsToChange.length 
    var percentageLeft = 100.00; // to be used when a stage is unevenly weighted to calculate the new evenWeighting of the rest of the stages

    for(const weighting of weightingsToChange){ // first check if there is any uneven weighting this MUST be first  as percentageLeft and  numberOfStagesToConsider must be set before the even weitings are calculated    
        console.log("first loop: " + weighting.id );
        // var weightingsConsidered = []; 
        // var currentStageID = weighting.id.replace("weighting","").replace("Input","").replace("SG","")
        //console.log("currentStageID: "+currentStageID);
        //console.log("weightingsConsidered.includes(currentStageID)")
        //console.log("first Condition: "+ (typeEditing == "SG" && weighting.nodeName == "INPUT" && !weighting.classList.contains("ignore")));//test
        // console.log("seccond Condition: "+(weighting.classList.contains("unEven") && !weighting.classList.contains("ignore")) ); // test
        // typeEditing == "SG" && weighting.nodeName == "INPUT" && (!weighting.classList.contains("ignore") || !weighting.classList.contains("hidden")  )) || (weighting.classList.contains("unEven") && !weighting.classList.contains("ignore"))
        if (weighting.classList.contains("unEven")){
            //weightingsConsidered.push(currentStageID);
            if(weighting.nodeName == "INPUT"){
                weightingVal = weighting.value.replace("%",""); //removing the % 
            }else{
                weightingVal = weighting.innerHTML.replace("%",""); //removing the % 
            }
            percentageLeft = percentageLeft - weightingVal;
            console.log("percentageLeft: "+percentageLeft)
            numberOfStagesToConsider--; 
        }
    }
    for(const weighting of weightingsToChange){ // then change value of weighting  that are suposed to be even
        console.log("2nd loop: " + weighting );
        if (weighting.classList.contains("even")){
            let newWeighting = percentageLeft / numberOfStagesToConsider;
            let newWeightingNumber = Number(newWeighting);
            console.log("numberOfStagesToConsider: "+ numberOfStagesToConsider);
            console.log("newWeighting: " + newWeightingNumber + "%"); // Test
            //console.log(newWeightingNumber + " > 0: ", newWeightingNumber > 0); // Test

            if (newWeightingNumber < 0) {
                document.getElementById("task" + taskID + "ErrorMsg").innerHTML = "Percentages cannot total over 100%!";
            } else {
                document.getElementById("task" + taskID + "ErrorMsg").innerHTML = ""; // clearing Error Mesage
                if (weighting.nodeName == "INPUT") {
                    weighting.value = newWeightingNumber.toFixed(2) + "%";
                } else {
                    weighting.innerHTML = newWeightingNumber.toFixed(2) + "%";
                }
            }
            
            
        }
    }
}

function changeWeightingsInDB(taskID, reload=false){
    //change weightings in DB
    var weightingsToChange = document.getElementsByClassName("weighting"+taskID+"SG");
    console.log("weightings to change: "+ weightingsToChange);
    for(const weighting of weightingsToChange){ // then change value of weighting  that are suposed to be even excluding new satge weighting 
        var stageID = weighting.id.replace("weighting","").replace("SG","");
        if (weighting.classList.contains("even") &&  !stageID.includes("N") ){
             
            var updateData = {ID: Number(stageID), weighting: Number(weighting.innerHTML.replace("%","")), table:"stage"}
            console.log(updateData); //test 
            useAJAXedit(updateData);
        }
    }
    if (reload){
        post("mainPage.php",{left:"intentionaly Blank"}); // to reload the page 

    }
}

function changeWeightingToUnEven(taskID, stageID ,numberExtra=-1){ 
    //getting current val
    var weightingTag = document.getElementById("weighting"+stageID+"SG");
    var evenButton = document.getElementById("evenButton"+stageID+"SG");
    var weightingInput = document.getElementById("weightingInput"+stageID+"SG");
    var isEven;
    console.log("id used: weighting"+stageID+"SG")
    console.log("weightingTag: "+weightingTag);
    isUnEven = true;
    weightingTag.classList.replace("even","unEven");
    weightingTag.innerHTML = weightingInput.value;
    //weightingTag.classList.add("ignore");// for changechangeWeighting
    evenButton.innerHTML = "Uneven";

    //changeWeighting(taskID,numberExtra); //change display
    //changeWeightingsInDB(taskID);
    //chanage in DB
    var updateData = {ID: Number(stageID), unEvenWeighting: isUnEven, table:"stage"}
    console.log(updateData); //test 
    useAJAXedit(updateData);
    
    

}

function addNewStage(taskID, hideOneStageDisplay=false){ // displays create fields 
    if(hideOneStageDisplay){
        // show multi stage display, hide one stage display
        document.getElementById("oneStageDisplay"+taskID).classList.add("hidden");
        document.getElementById("multiStageDisplay"+taskID).classList.replace("hidden", "showing");
    }
    // display newStageRow
    document.getElementById("newStageRow"+taskID).classList.replace("hidden", "showing");
    document.getElementById("newStageRow"+taskID+"PT2").classList.replace("hidden", "showing");

    //change weighting on stages existing stages
    //changeWeighting(taskID,0);
}

function cancelNewStage(taskID, stageCount){
    document.getElementById("newStageRow"+taskID).classList.replace("showing", "hidden");
    document.getElementById("newStageRow"+taskID+"PT2").classList.replace("showing", "hidden");
    //document.getElementById("weighting"+taskID+"NSG").classList.replace("unEven", "even"); // resetting value to even o it dosent interfear with changeWeighting calculations 
    console.log(stageCount); 
    if(Number(stageCount) == 1){
        document.getElementById("oneStageDisplay"+taskID).classList.replace("hidden", "showing");
        document.getElementById("multiStageDisplay"+taskID).classList.replace("showing", "hidden");
    }
    //changeWeighting(taskID);
}



function createNewStage(taskID){ // adds new stage to DB 
    // on submit
    //changeWeightingsInDB(taskID); 
    // create new Stage
    newStageVals = new Map();
    newStageVals.set("name", document.getElementById("name"+taskID+"NSG").value);
    //newStageVals.set("weighting", document.getElementById("weighting"+taskID+"NSG").value);
    //newStageVals.set("unEvenWeighting", document.getElementById("weighting"+taskID+"NSG").classList.contains("unEven") );
    newStageVals.set("taskID",taskID);
    newStageVals.set("tokenNSG",tokenNSG);
    post("mainPage.php", newStageVals);
    
    
}

function evenUneven(stageID, taskID, numberExtra){ //toggle even value 
    //getting current val
    var weightingTag = document.getElementById("weighting"+stageID+"SG");
    var evenButton = document.getElementById("evenButton"+stageID+"SG");
    var isEven;
    console.log("id used: weighting"+stageID+"SG")
    console.log("weightingTag: "+weightingTag);
    // change display
    if(weightingTag.classList.contains("even")){
        isUnEven = true;
        weightingTag.classList.replace("even","unEven");
        evenButton.innerHTML = "Uneven"
    }else if(weightingTag.classList.contains("unEven")){
        isUnEven = false;
        weightingTag.classList.replace("unEven","even");
        evenButton.innerHTML = "Even"
    }
    //chanage in DB
    console.log("stageID.length: "+stageID.length);//test
    if(stageID[stageID.length-1] != "N"){
        var updateData = {ID: Number(stageID), unEvenWeighting: isUnEven, table:"stage"}
        console.log(updateData); //test 
        useAJAXedit(updateData);
    }else{
        document.getElementById("unEvenWeighting"+stageID+"SG").value = isUnEven;// change input field
    }
    

    //changeWeighting(taskID,numberExtra); //change display
    //changeWeightingsInDB(taskID);


    // change if uneven to even change weightings to reflect that
}
 // collab code
function makeCollab(taskListID){
    // set tasklist to collab in db
    useAJAXedit({ID: Number(taskListID), collab: 1, table:"taskList"});
    //addng user to taskListCollab table
    useAJAXaddCollaborator(Number(taskListID), userID);
    // display add Collaborators display
    document.getElementById("makeCollabButtonOf"+taskListID).classList.add("hidden");
    showCollaborators(taskListID);
}
function showCollaborators(taskListID){
    //hide tasks
    document.getElementById("tasksOf"+taskListID).classList.add("hidden");
    document.getElementById("showCollaboratorsButtonOf"+taskListID).classList.add("hidden");
    //show Collaborators 
    document.getElementById("collab"+taskListID).classList.remove("hidden");
    document.getElementById("showTasksButtonOf"+taskListID).classList.remove("hidden");
}

function showTasks(taskListID){
    // show tasks
    document.getElementById("tasksOf"+taskListID).classList.remove("hidden");
    document.getElementById("showCollaboratorsButtonOf"+taskListID).classList.remove("hidden");
    
    // hide Collaborators 
    document.getElementById("collab"+taskListID).classList.add("hidden");
    document.getElementById("showTasksButtonOf"+taskListID).classList.add("hidden");

}
function useAJAXaddCollaborator(taskListID, userID){
    addGenralErrorMsg("adding Collaborator...", "green");
    // upadating the DB
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
        console.log("add responce: "+this.responseText)
        clearGenralErrorMsg();
        return this.responseText;
    }else if(this.readyState == 4 && (this.status == 403 || this.status == 404 )) {
        addGenralErrorMsg("operation failed; oops!", "red");
    }
    };

    
    xmlhttp.open("GET", "AJAXaddCollaborator.php?taskListID="+taskListID+"&userID="+userID , true);
    xmlhttp.send()
}
function hideUser(IDtoHide){
    document.getElementById("collabUser"+IDtoHide).classList.add("hidden");
}
//event listerners



</script>
<?php
//deleteID("71", "tasklist", $conn); //test
// ---------------------------------------------------- class Stuff ----------------------------------------------------
class Stage{ 
    public $ID;
    public $name;
    public $weighting;
    public $unEvenWeighting;
    public $complete;	
    public $dateTimeCompleted;
    public $completedBy;

    public function __construct($vals){
        // seting varibles
        foreach($vals as $property => $val) {
            $this->$property = $val;
            //echo "property: ".$property." val:". $val. "<br>"; //test
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
        $qry = "SELECT ID, `name`, weighting, unEvenWeighting, complete, dateTimeCompleted, completedBy FROM stage WHERE taskID = ?;";
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
    public $collaborators =[];
    public $tasks = []; 

    public function __construct($vals, $con){
        // seting varibles
        foreach($vals as $property => $val) {
            $this->$property = $val;
            //echo $val; //test
        }
        // setting the Collaborators
        if($this->collab){
            $qry = "SELECT user.ID, user.username FROM user LEFT JOIN tasklistcollab ON user.ID = tasklistcollab.userID WHERE tasklistcollab.taskListID = ?;"; 
            $stmt = $con->prepare($qry);
            $stmt->execute([$this->ID]);
            $this->collaborators = $stmt->fetchAll(PDO::FETCH_ASSOC); 
            //var_dump($this->collaborators); //test
            
            
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

$qry = "SELECT ID, `name`, deadline, collab, `priority`, ownerID  FROM taskList WHERE ownerID = :userID OR ID IN (SELECT taskListID FROM taskListCollab WHERE userID = :userID);"; 
$stmt = $conn->prepare($qry);
$stmt->bindParam('userID', $_SESSION["userID"]);
$stmt->execute();
$taskLists = $stmt->fetchAll(PDO::FETCH_ASSOC);
//var_dump($taskLists); //test
foreach($taskLists as $row => $vals){
    $taskLists[$row] = new TaskList($vals, $conn);

}

?>
</head>


<body id>
    <?php navBar(false, basename($_SERVER["PHP_SELF"]) ); ?>
    <!-- </div> -->
    <div id="main">
        <div class="tabs">
        <?php
            foreach($taskLists as $taskList){
                
        ?>
            <button onclick="changeTab('<?php echo $taskList->ID?>')" id="<?php echo $taskList->ID."Tab"?>" class="tab <?php echo($taskList->name == "Genral") ? "first" : "hidden";  ?> "><?php echo $taskList->name?></button>
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
                //var_dump($taskList->collaborators);//test
        ?>
            
            <div id="<?php echo $taskList->ID; ?>Container" class="hidden">
                <div class = "taskListHeader">
                    <h2 onclick = "allowEdit('name' , <?php echo $taskList->ID;?>, 'TL')" class="editButtons editButtonsID<?php echo $taskList->ID;?>TL"><?php echo $taskList->name; ?></h2>
                    <input type = "text" id = "nameInput<?php echo $taskList->ID;?>TL" class =" inputbutton hidden editInputs editInputsID<?php echo $taskList->ID;?>TL" name = "nameInput<?php echo $taskList->ID;?>TL"  onclick = "allowEdit('name' , <?php echo $taskList->ID;?>,'TL')" value = "<?php echo $taskList->name; ?>"/>
                    <div id="nameETL<?php echo $taskList->ID;?>Error"></div>
                </div>
                
                <button class="button editButtons editButtonsID<?php echo $taskList->ID;?>TL" onclick = "allowEdit('deadline' , <?php echo $taskList->ID; ?>,'TL')">Deadline: <?php echo (isset($taskList->deadline))? $taskList->deadline : "none"  ?> </button>
                <input type = "datetime-local" min = "<?php date("Y-m-d h:i:s")?>" id = "deadlineInput<?php echo $taskList->ID;?>TL" class =" inputbutton hidden editInputs editInputsID<?php echo $taskList->ID;?>TL" name = "deadlineInput<?php echo $taskList->ID;?>TL"  onclick = "allowEdit('deadline' , <?php echo $taskList->ID;?>,'TL')" value = "<?php echo $taskList->deadline; ?>"/>

                <button onclick="showCollaborators(<?php echo $taskList->ID; ?>)" id="showCollaboratorsButtonOf<?php echo $taskList->ID; ?>" class="button collabColour <?php echo (!$taskList->collab)? "hidden":"";?>">Collaborators</button>
                <button onclick="showTasks(<?php echo $taskList->ID; ?>)" id="showTasksButtonOf<?php echo $taskList->ID; ?>" class="button hidden">Tasks</button>
                <button onclick="makeCollab(<?php echo $taskList->ID; ?>)" id="makeCollabButtonOf<?php echo $taskList->ID; ?>" class="button collabColour <?php echo ($taskList->collab)? "hidden":"";?>">Make Collab</button>

                <button onclick="changePriority('<?php echo $taskList->ID; ?>priorityTL',{ID: '<?php echo $taskList->ID; ?>',table: 'tasklist'} )" class='button <?php echo getPriorityVal($taskList->priority, $prioritiesColour) ?>' id="<?php echo $taskList->ID; ?>priorityTL"><?php echo getPriorityVal($taskList->priority,$prioritiesName)." Priority"?></button>
                <button onclick="useAJAXdelete(<?php echo $taskList->ID; ?>, 'tasklist', <?php echo $taskList->ownerID; ?>)" class="button red">Delete</button>

                <!-- ------------- tasks ------------- -->
                <div id="tasksOf<?php echo $taskList->ID; ?>">
                <?php
                
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

                            <table class="taskTable centreTable">
                                <tr class="taskTr">
                                    <td class="taskTd tableDisplay">
                                        priority
                                    </td>
                                    <td class="taskTd tableDisplay">
                                        <button onclick="changePriority('<?php echo $task->ID; ?>priorityTL',{ID: '<?php echo $task->ID; ?>',table: 'task'} )" class='button <?php echo getPriorityVal($task->priority, $prioritiesColour) ?>' id="<?php echo $task->ID; ?>priorityTL"><?php echo getPriorityVal($task->priority,$prioritiesName)." Priority"?></button>
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
                            <?php if(count($task->stages)== 1):?> 
                                <!-- one Stage -->
                                <div  id ="oneStageDisplay<?php echo $task->ID; ?>" class ="stageList">
                                    <p>Complete:</p>
                                    <button onclick="completeStage(<?php echo $task->stages[0]->ID?>)" id="complete<?php echo $task->stages[0]->ID?>SG-OSD" class="stageButton <?php echo ($task->stages[0]->complete == 1)?  "green": "" ?>">✓</button>
                                    <button onclick="addNewStage(<?php echo $task->ID; ?>, true)" id="addNewStageButton<?php echo $task->ID; ?>-OSD" class = "button green">Add New Stage</button>
                                </div>
                            <?php endif;?>
                            <!-- two stages above -->
                            <table id ="multiStageDisplay<?php echo $task->ID; ?>" class="clear centreTable <?php echo (count($task->stages)== 1)? "hidden" : "" ;?>">
                                <tr>
                                    <th class="clear textWhite"><b>Stages</b></th>
                                    <!-- <th class="clear textWhite"><b>Weighting</b></th>
                                    <th class="clear textWhite"><b></b></th> -->
                                    <th class="clear textWhite"><b>Mark as Complete</b></th>
                                    <th class="clear textWhite"><button onclick="addNewStage(<?php echo $task->ID; ?>)" id="addNewStageButton<?php echo $task->ID; ?>" class = "button green">Add New Stage</button></th>
                                </tr>
                                
                                <?php foreach($task->stages as $stage):?>
                                    <?php //var_dump( $stage->unEvenWeighting); //test?>
                                    <tr id = "stage<?php echo $stage->ID?>" class="stagesOfTask<?php echo $task->ID?>">
                                        <td class='clear'>
                                            <button onclick="allowEdit('name', <?php echo $stage->ID;?>, 'SG')" class="button clear editButtons editButtonsID<?php echo $stage->ID;?>SG"><?php echo $stage->name?></button>
                                            <input onclick = "allowEdit('name' , <?php echo $stage->ID;?>,'SG')" type = "text" id = "nameInput<?php echo $stage->ID;?>SG" class =" inputbutton hidden editInputs editInputsID<?php echo $stage->ID;?>SG" name = "nameInput<?php echo $stage->ID;?>SG"  value = "<?php echo $stage->name; ?>"/>
                                        </td>
                                        <!-- <td class='clear'>
                                            <button onclick="allowEdit('weighting', <?php echo $stage->ID;?>, 'SG')" id="weighting<?php echo $stage->ID?>SG" class="weighting<?php echo $task->ID?>SG <?php echo ($stage->unEvenWeighting == 1 )? "unEven" : "even"?> button clear editButtons editButtonsID<?php echo $stage->ID;?>SG"><?php echo $stage->weighting?>%</button> 
                                            <input onclick = "allowEdit('weighting' , <?php echo $stage->ID;?>,'SG')" onkeydown="changeWeightingToUnEven(<?php echo $task->ID. ', '.  $stage->ID;?>)" type = "text" id = "weightingInput<?php echo $stage->ID;?>SG" class =" inputbutton hidden editInputs editInputsID<?php echo $stage->ID;?>SG" name = "weightingInput<?php echo $stage->ID;?>SG"  value = "<?php echo $stage->weighting?>%"/>
                                        </td>
                                        <td class='clear'>
                                            <button onclick="evenUneven(<?php echo $stage->ID?> , <?php echo $task->ID?>)" id="evenButton<?php echo $stage->ID?>SG" class="button"><?php echo ($stage->unEvenWeighting == 1 )? "Uneven" : "Even"?></button>
                                        </td> -->
                                        <td class='clear'>
                                            <button onclick="completeStage(<?php echo $stage->ID?>)" id="complete<?php echo $stage->ID?>SG" class="stageButton <?php echo ($stage->complete == 1)?  "green": "" ?>">✓</button>
                                        </td>                                        
                                        <td class='clear'>
                                            <?php if(count($task->stages) > 1 ):?>
                                                <button onclick="useAJAXdelete(<?php echo $stage->ID?>, 'stage',<?php echo $task->ID?> )" class="button red">Remove</button>
                                            <?php endif?>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                                <!-- new stage Row  -->
                                
                                <tr id="newStageRow<?php echo $task->ID; ?>" class="hidden">
                                    
                                    <td class='clear'>
                                        <input  id="name<?php echo $task->ID; ?>NSG" name ="name<?php echo $task->ID; ?>NSG" type="text" value="New Stage">
                                    </td>
                                    <!-- <td class='clear'>
                                        <input onkeydown="changeWeightingToUnEven(<?php echo $task->ID; ?>,'<?php echo $task->ID; ?>N', 0 )" id="weighting<?php echo $task->ID; ?>NSG" name ="weighting<?php echo $task->ID; ?>NSG" class ="weighting<?php echo $task->ID?>SG even ignore" type="text" value="<?php echo 100.00/(count($task->stages)+1)?>%">
                                    </td>
                                    <td class='clear'>
                                        <button onclick="evenUneven('<?php echo $task->ID; ?>N' ,<?php echo $task->ID?>, 0 )" id="evenButton<?php echo $task->ID?>NSG" class="button">Even</button>
                                        <input type="hidden" id ="unEvenWeighting<?php echo $task->ID; ?>NSG" name="unEvenWeighting<?php echo $task->ID; ?>NSG" value="0">
                                    </td> -->
                                    <td class='clear'>
                                        <input id="complete<?php echo $task->ID; ?>NSG" class="stageButton" type="checkbox" value=""/>
                                    </td>
                                    <td class='clear'>
                                        <button onclick="cancelNewStage(<?php echo $task->ID . ',' . count($task->stages) ?>)" class="button red">cancel</button> 
                                    </td>
                                    
                                </tr>
                                <tr id="newStageRow<?php echo $task->ID; ?>PT2" class="hidden">
                                    <td class='clear'>
                                        <input type="hidden" name="tokenNSG" value="<?php echo $tokenNSG; ?>" />
                                    </td>

                                    <!-- <td class='clear'>
                                    </td> -->
                                    <td class='clear'>
                                        <button  onclick="createNewStage(<?php echo $task->ID; ?>)" id='submitNSG'  class=" button green">Create!</button>
                                    </td>
                                    <!-- <td class='clear'>
                                    </td> -->
                                    <td class='clear'>
                                    </td>
                                </tr>
                                <div id="task<?php echo $task->ID; ?>ErrorMsg" class = "red"></div>
                            </table>
                        </div>
                        
                <?php
                    }// taskList foreach close
                }// else statement close 
                ?>
                </div>
                <!-- collab display -->
                <div id="collab<?php echo $taskList->ID; ?>" class="hidden">
                    <h3>Add Collaborators: </h3>
                    <form action="mainPage.php" method="Post">
                        <input type="number" id="collabCodeNCU" name="collabCodeNCU">
                        <input type="hidden" id="tokenNCU" name="tokenNCU" value="<?php echo $tokenNCU; ?>">
                        <input type="hidden" id="taskListIDNCU" name="taskListIDNCU" value="<?php echo $taskList->ID; ?>">
                        <input class="button green" type="submit" id="submitNCU" name="submitNCU" value="+">
                    </form>
                    <div id="NCUError"><b class="red"> <?php echo(isset($errors["NCU"]))? $errors["NCU"] :"";  ?></b></div>
                    <?php if (count($taskList->collaborators) > 1 ):?>
                        <h3>Collaborators: </h3>
                        <table class="clear centreTable">
                        <tr>
                            <th class="clear textWhite">User</th>
                            <th class="clear textWhite">Role</th>
                            <th class="clear textWhite"></th>
                        </tr>
                        <?php foreach($taskList->collaborators as $collaborator): ?>
                            <tr id="collabUser<?php echo $collaborator['ID'] ?>">
                                <td class='clear'>
                                    <?php echo $collaborator["username"] ?>
                                </td>
                                <td class='clear'>
                                    <?php echo ($taskList->ownerID == $collaborator["ID"])? "Owner" : "Collaborator" ?>
                                </td>
                                <td class='clear'>
                                    <button onclick="useAJAXdelete(<?php echo $collaborator['ID'] ?>, 'tasklistcollab',<?php echo $taskList->ownerID?> )" class="button red">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach?>
                        </table>
                    <?php else:?>
                        <p>you dont have any collaborators yet</p>
                    <?php endif;?>
                    <form action="mainPage.php" method="post">
                        <input type="hidden" name="tokenRC" value="<?php echo $tokenRC; ?>">
                        <input type="hidden" name="taskListIDRC" value="<?php echo $taskList->ID; ?>">
                        <input type="submit" class="button red" value="Remove Collab">
                    </form>
                </div>
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
                <input type="datetime-local" min="<?php echo date("d-m-Y h:i:s")// wrong format? ?>" name="deadlineNTL" id="deadlineNTL" value="<?php if(isset($valsToValadate["deadlineNTL"])){echo $valsToValadate["deadlineNTL"];}?>">
                <div id="deadlineNTLError"></div>

                <div class="txtLeft"><label for="priorityNTL">Priority</label></div>
                <input onclick="changePriority('priorityNTL', false)" type='text' name='priorityNTL' id='priorityNTL' class='button' value="<?php if(isset($valsToValadate["priorityNTL"])){echo $valsToValadate["priorityNTL"];}else{echo'medium';}?>"readonly>
                <div id="priorityNTLError"></div>

                <input type="submit" name='submitNTL' id='submitNTL' class="green"value="Create!">
            </form>
            <script> errorMsg(<?php if (isset($errors)){ echo json_encode($errors);} // need the json encode part ?>)  </script> 
            <button onclick="closeTaskList('newTaskList')" class="button red">cancel</button>
        </div>

        <!-- newTask -->
        <div id="newTaskContainer" class = "hidden">
            <form action="mainPage.php" method="post">

                <input type="hidden" name="tokenNTK" value="<?php echo $tokenNTK; ?>" />

                <div class="txtLeft"><label for="nameNTK">Task Name</label></div>
                <input type="text"name="nameNTK" id="nameNTK" value="<?php if(isset($valsToValadate["nameNTK"])){echo $valsToValadate["nameNTK"];}?>">
                <div id="nameNTKError"></div>

                <div class="txtLeft"><label for="taskListIDNTK">Belongs to Task List:</label></div>
                <select type="text"name="taskListIDNTK" id="taskListIDNTK">
                <?php
                foreach ($taskLists as $taskList){
                    if(isset($valsToValadate["taskListIDNTK"]) And $taskList->ID == $valsToValadate["taskListIDNTK"]){
                        echo "<option value=".$taskList->ID."selected>".$taskList->name."</option>";
                    }else{
                        echo "<option value=".$taskList->ID.">".$taskList->name."</option>";
                    }
                }
                
                ?>
                </select>
                <div id="taskListIDNTError"></div>

                <div class="txtLeft"><label for="deadlineNTK">Deadline</label></div>
                <input type="datetime-local" min="<?php echo date("d-m-Y h:i:s")?>" name="deadlineNTK" id="deadlineNTK" value="<?php if(isset($valsToValadate["deadlineNTK"])){echo $valsToValadate["deadlineNTK"];}?>">
                <div id="deadlineNTKError"></div>

                <div class="txtLeft"><label for="priorityNTK">Priority</label></div>
                <input onclick="changePriority('priorityNTK', false)" type='text' name='priorityNTK' id='priorityNTK' class='button' value="<?php if(isset($valsToValadate["priorityNTK"])){echo $valsToValadate["priorityNTK"];}else{echo'medium';}?>"readonly>
                <div id="priorityNTKError"></div>

                <input type="submit" name='submitNTK' id='submitNTK' class="green"value="Create!">
            </form>
            <script> errorMsg(<?php if (isset($errors)){ echo json_encode($errors);} // need the json encode part ?>)  </script> 
            <button onclick="closeTaskList('newTask')" class="button red">cancel</button>
        </div>
        

        <!-- All task lists  -->

        <div id="allContainer" class="showing">
            <table class = "tableDisplay">
                    <tr>
                        <th>Name</th>
                        <th class= "hideWhenScreenSmall">Deadline</th>
                        <th class= "hideWhenScreenSmall">Collab</th>
                        <th>Priority</th>
                        <th>Owner</th>
                        <th></th>
                    </tr>
                
                <?php foreach($taskLists as $taskList): ?> 
                        <tr id='allRow<?php echo $taskList->ID;?>'> 
                            <td><?php echo $taskList->name ?></td>
                            <td class= "hideWhenScreenSmall"><?php echo yesOrNo($taskList->deadline) ?></td>
                            <td class= "hideWhenScreenSmall"><?php echo yesOrNo($taskList->collab) ?></td>
                            <td><button onclick="changePriority('priority<?php echo $taskList->ID ?>ATL',{ID: '<?php echo $taskList->ID; ?>',table: 'tasklist'})"  id='priority<?php echo $taskList->ID ?>ATL' class='button <?php echo getPriorityVal($taskList->priority, $prioritiesColour) ?>'><?php echo getPriorityVal($taskList->priority, $prioritiesName)  ?> Priority</td>
                            <td><?php echo ($taskList->ownerID == $_SESSION["userID"])? "you" : getNameFromID($taskList->ownerID,$conn) ?></td>
                            <td>
                                <button onclick='openTaskList("<?php echo $taskList->ID ?>")' id='openButton<?php echo $taskList->ID ?>' class='button green'>Open?</button>
                                <button onclick='closeTaskList("<?php echo $taskList->ID ?>")' id='closeButton<?php echo $taskList->ID ?>' class='button red hidden'>Close?</button>
                            </td>
                        </tr>
                <?php endforeach ?>
            </table>
        </div>
        
        <script>
        <?php
        //var_dump($OpenTabs);
        foreach($OpenTabs as $Tab){
            echo "openTaskList('".$Tab."'); \n";
        }
        ?>
        changeTab("<?php if (isset($_SESSION["currentDisplay"]) And $_SESSION["currentDisplay"]!="" ){ echo $_SESSION["currentDisplay"];}else{echo "all";} ?>");
        // closing new task List tab if opended and new task list is created
        //
        // if(typeof changeWeightingsInDB_IDtoChange !== 'undefined' ){
        //     //changeWeightingsInDB(changeWeightingsInDB_IDtoChange, true );
        // }

        </script>
             
    </div>


    
</body>
<?php footer($pageName); ?>
</html>