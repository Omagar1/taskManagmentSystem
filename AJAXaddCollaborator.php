<?php
// so i can use  deleteRowProcess for both AJAX and  AJAX  
require "addCollaborator.php";
require_once "dbConnect.php";
addCollaborator(intval($_GET["taskListID"]), intval($_GET["userID"]), $conn);

?>