<?php
// so i can use  editRowProcess for both AJAX and non AJAX scripts 
require "editRowProcess.php";
require "functions.php";
require_once "dbConnect.php";
//removing table from the get array as get array is used as the edited values arrray
$table = $_GET["table"]; 
unset($_GET["table"]);

// validtion
$editedVals; 
foreach($_GET as $column => $val){ // gets the data into correct formats
    if($val == "false" or $val == "true"){// string to bool 
        $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }elseif($column == "ID" ){
        $val = intval($val);
    }elseif($column == "weighting"){
        $val = floatval($val); 
    }
    $editedVals[$column] = $val; 
}


editRow($editedVals, $table, $conn);

?>