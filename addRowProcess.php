<?php

function addRow($addedVals, $addTable, $con){
    try{
        //echo"addRow Ran for: ". $addTable;//test
     
       
        //echo $addTable; //test
        //$IDToadd = $addedVals["ID"];
        // bulding a Qry
        $qry = "INSERT INTO " . $addTable . "(";
        // adding the columns to Insert  
        foreach ($addedVals as $column => $valToCheck){
            if($column != "ID" ){ // id is automaticly incrimented so dont need it in the SQL statement
                $column = whitchDBColumn($column); 
                if($column != false){
                    $qry = $qry .  $column .", ";
                }
                 
            }   
        }
        $qry = substr($qry, 0, -2);// remove extra space + commma 
        $qry = $qry .") VALUES ("; // closing open baracket
    
        // adding the columns to bind vals to 
        foreach ($addedVals as $column => $valToCheck){
            if($column != "ID" ){ // id is automaticly incrimented so dont need it in the SQL statement
                $qry = $qry . ":". $column .", "; // using :$column so bind param can be used later  
            }   
        }
        $qry = substr($qry, 0, -2);// remove  space + extra commma 
        $qry = $qry .");"; // closing open baracket 

        //echo $qry; //test
        $stmt = $con->prepare($qry);
        
        //binding the values to the colums
        $count = 0; 
        foreach ($addedVals as $column => &$val){// need & for bind param
            $bindStr = ":".$column;
            //echo "$bindStr = :".$column." Val: ".$val; //test
            //echo $val;//test
            switch(gettype($val)){
                case "boolean":
                    $stmt->bindValue($bindStr, $val, PDO::PARAM_BOOL);
                    //echo"boolean";//test
                    break;
                case "integer":
                    $stmt->bindValue($bindStr, $val, PDO::PARAM_INT);
                    //echo"integer";//test
                    break;
                case "string":
                    $stmt->bindValue($bindStr, $val, PDO::PARAM_STR);
                    //echo"string";//test
                    break;
                case "NULL":
                    $stmt->bindValue($bindStr, $val, PDO::PARAM_NULL);
                    //echo"NULL";//test
                    break;
                default:
                    $stmt->bindValue($bindStr, $val);
                    //echo"default";//test
            }
            $count++;
        }
        // echo "<br>";//test
        // echo $qry;//test
        // echo "<br>";//test
        //$stmt->debugDumpParams(); //test
        $stmt->execute();
        
        $lastID = $con->lastInsertId();
        if($addTable == "task"){
            $newStageVals = array("name" => "stage1","weighting"=>100.00,"taskID"=>$lastID);
            addRow($newStageVals, "stage", $con);
        }
        //echo $con->errorInfo();//test
        //var_dump($stmt); test
        return $lastID ; 

    } catch(PDOException $e) {
        echo "Error : ".$e->getMessage(); // dev error mesage 
        return false; 
    }
}

?>