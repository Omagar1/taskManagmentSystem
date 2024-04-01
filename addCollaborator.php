<?php
function addCollaborator($taskListID, $userID, $con){
    try{
        $qry = "INSERT INTO tasklistcollab (taskListID, userID) VALUES (?,?);"; 
        $stmt = $con->prepare($qry);
        $stmt->execute([$taskListID, $userID]);
        return true; 
    } catch(PDOException $e){
        echo "Error : ".$e->getMessage();// dev error mesage
        return "error";
    }
}
?>