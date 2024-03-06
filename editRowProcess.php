<?php

function editRow($editedVals, $editTable, $con){
    try{
        // bulding a Qry
        //var_dump($editedVals);//test
        $qry = "UPDATE  " . $editTable . " SET";
        // adding the columns to update 
        foreach ($editedVals as $column => $valToCheck){
            if($column != "ID"){
                $dbColumn = whitchDBColumn($column); 
                $qry = $qry . " ". $dbColumn ." = :". $column.","; // using :$column so bind param can be used later  
            } 
            
        }
        $qry = substr($qry, 0, -1);// remove extra commma 
        $qry = $qry . " WHERE ID = :ID;"; // adding where condition
        //echo $qry; //test
        $stmt = $con->prepare($qry);
        // binding params
        foreach ($editedVals as $column => &$val){// need & for bind param
            if($val == "false" or $val == "true"){// string to bool 
                $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
            }
            $bindStr = ":".$column;
            echo $bindStr = " :".$column." Val: ".$val; //test
            //echo $val;//test
            $stmt->bindParam($bindStr, $val);
        }
        echo $qry; //test
        $stmt->execute();
        var_dump($stmt); //test
        //var_dump($editedVals); //test
        return true; 
    } catch(PDOException $e) {
        echo "Error : ".$e->getMessage(); // dev error mesage 
        var_dump($stmt); //test
        return false; 
   }

}
 //:ID Val: 2 :priority Val: 1 UPDATE  tasklist SET priority = :priority WHERE ID = :ID; priority
?>