<?php
// so i can use  deleteRowProcess for both AJAX and  AJAX  
require "addCollaborator.php";
require_once "dbConnect.php";
deleteID($_GET["taskListID"], $_GET["userID"], $conn);

?>