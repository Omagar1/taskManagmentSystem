<?php
session_start();
session_unset();
session_destroy();
header("location: index.php"); // redirects user to login page
?>