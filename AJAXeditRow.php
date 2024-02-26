<?php
// so i can use  editRowProcess for both AJAX and non AJAX scripts 
require "editRowProcess.php";
require "functions.php";
require_once "dbConnect.php";
//removing table from the get array as get array is used as the edited values arrray
$table = $_GET["table"]; 
unset($_GET["table"]);

editRow($_GET, $table, $conn);

?>