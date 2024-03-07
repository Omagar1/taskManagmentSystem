<?php

function editRow($editedVals, $editTable, $con){
    try{
        // bulding a Qry
        //var_dump($editedVals);//test
        $qry = "UPDATE " . $editTable . " SET";
        // adding the columns to update 
        foreach ($editedVals as $column => $valToCheck){
            if($column != "ID"){
                $dbColumn = whitchDBColumn($column); 
                $qry = $qry . " `". $dbColumn ."` = :". $column.","; // using :$column so bind param can be used later // using backticks to avoid any key words errors 
            } 
            
        }
        $qry = substr($qry, 0, -1);// remove extra commma 
        $qry = $qry . " WHERE ID = :ID;"; // adding where condition
        //echo $qry; //test
        $stmt = $con->prepare($qry);
        // binding params
        $count = 0; 
        foreach ($editedVals as $column => &$val){// need & for bind param
            if($val == "false" or $val == "true"){// string to bool 
                $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
            }
            $bindStr = ":".$column;
            //echo $bindStr = " :".$column." Val: ".$val; //test
            //echo $val;//test
            $stmt->bindParam($bindStr, $val);
            $count++;
        }
        //echo " count: ".$count; //test
        //echo " qry: ".$qry; //test
        $result = $stmt->execute();
        $rowCount = $stmt->rowCount();

        if($rowCount =='0'){ // 
            echo "Failed!";
        }
        else{
            echo "Success!";
        }
        //var_dump($stmt); //test
        //var_dump($editedVals); //test
        return $result; 
    } catch(PDOException $e) {
        echo "Error : ".$e->getMessage(); // dev error mesage 
        //var_dump($stmt); //test
        return false; 
   }

}
 //:ID Val: 2 :priority Val: 1 UPDATE  tasklist SET priority = :priority WHERE ID = :ID; priority
?>