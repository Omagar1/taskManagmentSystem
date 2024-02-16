<?php
ob_start(); // so that redirect works 
session_start();
require "functions.php";
//require "loginProcess.php";
//require_once "dbConnect.php";
?>

<!DOCTYPE html>
<html lang="en">
<?php
$_SESSION["previous"] = []; // initalising the Previous Stack
$pageName = basename($_SERVER["PHP_SELF"]); // getting the name of the page so head can add it to the Previous stack
head($pageName); // from functions.php, echoes out the head tags  
?>
</head>


<body id="test">
    <?php navBar(false, basename($_SERVER["PHP_SELF"]) ); ?>
    </div>
    <div id="mainLogin">
        <div class="tabs">
            <a href="index.php" class="tab first ">Log In</a>
            <a href="signUp.php" class="tab tab-2">Sign Up</a>
            <a href="#aboutUs.php"class="tab selected last">About Us</a>
        </div>
        <p>
            Lorem ipsum dolor sit amet consectetur adipisicing elit. 
            Quis aperiam unde ex ipsum corporis, doloribus, velit enim culpa explicabo vero,
            amet assumenda voluptatem pariatur dolorem quae repudiandae quisquam. Maiores, 
            inventore.
        </p>
              
    </div>


    <?php footer(); ?>
</body>

</html>