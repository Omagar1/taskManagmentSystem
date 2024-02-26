<?php
// so i can use  deleteRowProcess for both AJAX and  AJAX  
require "deleteRowProcess.php";
require_once "dbConnect.php";
deleteID($_GET["ID"], $_GET["table"], $conn);

?>