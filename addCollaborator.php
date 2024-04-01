<?php
function addCollaborator($taskListID, $userID, $con){
    try{
        echo "typeof taskListID : ". var_dump($taskListID);
        echo "typeof userID : ". var_dump($userID);
        $qry = "INSERT INTO tasklistcollab (taskListID, userID) VALUES (:taskListID,:userID);";
        
        $stmt = $con->prepare($qry);
        $stmt->bindParam('taskListID', $taskListID, PDO::PARAM_INT);
        $stmt->bindParam('userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->debugDumpParams(); //test
        return true; 
    } catch(PDOException $e){
        echo "Error : ".$e->getMessage();// dev error mesage
        return "error";
    }
}
?>