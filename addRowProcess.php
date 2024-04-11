<?php

function addRow($addedVals, $addTable, $con){
    try{
     
       
        //echo $addTable; //test
        //$IDToadd = $addedVals["ID"];
        // bulding a Qry
        $qry = "INSERT INTO " . $addTable . "(";
        // adding the columns to Insert  
        foreach ($addedVals as $column => $valToCheck){
            if($column != "ID" ){ // id is automaticly incrimented so dont need it in the SQL statement
                $column = whitchDBColumn($column); 
                if($column != false){
                    $qry = $qry . " ". $column .",";
                }
                 
            }   
        }
        $qry = substr($qry, 0, -1);// remove extra commma 
        $qry = $qry .") VALUES ("; // closing open baracket
    
        // adding the columns to bind vals to 
        foreach ($addedVals as $column => $valToCheck){
            if($column != "ID" ){ // id is automaticly incrimented so dont need it in the SQL statement
                $qry = $qry . " :". $column .","; // using :$column so bind param can be used later  
            }   
        }
        $qry = substr($qry, 0, -1);// remove extra commma 
        $qry = $qry .");"; // closing open baracket 

        //echo $qry; //test
        $stmt = $con->prepare($qry);
        
        //binding the values to the colums
        foreach ($addedVals as $column => &$val){
            if($val == "false" or $val == "true"){// string to bool 
                $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
            }
            $bindStr = ":".$column;
            //echo $bindStr = ":".$column." "; // test 
            //echo $val; // test 
            $stmt->bindParam($bindStr, $val);
        }
        $stmt->execute();
        $lastID = $con->lastInsertId();
        if($addTable == "task"){
            $newStageVals = array("name" => "stage1","weighting"=>100.00,"taskID"=>$lastID);
            addRow($newStageVals, "stage", $con);
        }
        return $lastID  ; 
        //var_dump($stmt); test
    } catch(PDOException $e) {
        //echo "Error : ".$e->getMessage(); // dev error mesage 
        return false; 
    }
}

?>