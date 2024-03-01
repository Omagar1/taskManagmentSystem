<?php
function deleteID($IDTodelete, $deleteFromTable, $con, $whereCondition="ID"){
    try{
        //echo "Delete ID Ran with ID: ".$IDTodelete; //test
        // bulding a Qry
        $qry = "DELETE FROM " . $deleteFromTable. " WHERE " . $whereCondition ." = ?;";
        $stmt = $con->prepare($qry);
        $stmt->execute([$IDTodelete]);
        $msg = "<p><b class = 'success'>Deletion Completed</b></p>";

        //so deleation is cascaded down all the relationaships 
        $lastId = $con->lastInsertId(); // in the case where the $whereCondition is not the Primary key of the record 
        switch($deleteFromTable){
            case "user":
                deleteID($lastId, "tasklist", $con, "ownerID");
                break;
            case "tasklist":
                deleteID($lastId, "task", $con, "taskListID");
                break;
            case "stage":
                deleteID($lastId, "stage", $con, "taskID");
                break;
        }
        
        return true; 
        
    } catch(PDOException $e) {
        echo "Error : ".$e->getMessage(); // dev error mesage 
        $msg = "<p><b class = 'error'>Failed to Delete</b></p>"; // user error mesage 
        
        return false; 
    }

}

?>