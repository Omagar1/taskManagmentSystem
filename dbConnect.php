<?php
$servername = "localhost";
$username = "Josiah";
$password = "PHP4life";

try {
  $conn = new PDO("mysql:host=$servername;dbname=tms; charset=utf8mb4", $username, $password);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  //echo "Connected successfully </br>"; //test
} catch(PDOException $e) {
  echo $e;

}
?>
