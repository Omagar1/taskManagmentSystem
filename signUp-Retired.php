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
<?php
$msg = ""; // so errorMsg dosent error 
// ---------------------------------------------------- Validation -------------------------------------------------
if (isset($_POST['submitL'])) {
    $uname = trim($_POST['Uname']);
    $pword = trim($_POST['Pwd']);
    if ($uname == "") {
        $msg = "<p id = 'msg'><b class = 'error'>Username Must Not be Empty</b></p>";
        
        //echo $msg;
        // echo "<script>processForm('Uname')</script>";
    } elseif ($pword == "") {
        $msg = "<p id = 'msg'><b class = 'error'>Password Must Not be Empty</b></p>";
        
        //echo $msg;
        // echo "<script>processForm('Pwd')</script>";
    } elseif (strlen($pword) > 20) {
        $msg = "<p id = 'msg'><b class = 'error'>Password Must Not Be Over 20 Characters in length </b></p>";
        
        //echo $msg;
        // echo "<script>processForm('Pwd')</script>";
    } else {
        //Passed Validation; passing data On to process page
        //$msg = checkLoginData($uname, $pword, $conn); 
    }
} else {

}

?>
</head>


<body id="test">
    <?php navBar(false, basename($_SERVER["PHP_SELF"]) ); ?>
    </div>
    <div id="mainLogin">
        <div class="tabs">
            <a href="index.php" class="tab first ">Log In</a>
            <a href="#signUp.php" class="tab selected tab-2">Sign Up</a>
            <a href="aboutUs.php"class="tab last">About Us</a>
        </div>
         <!-- sign up Form  -->
        <form action="index.php" method="post" id="signUpForm">
            <div class="txtLeft"><label for="Uname">Username</label></div>
            <input type="text" id="Uname" name="Uname" value=""><br>
            <div class="txtLeft"><label for="Email">Email</label></div>
            <input type="email" id="Email" name="Email" value=""><br>
            <div class="txtLeft"><label for="Pwd">Password</label></div>
            <input type="password" id="Pwd" name="Pwd" value=""><br>
            <div class="txtLeft"><label for="PwdC">Comfirm Password</label></div>
            <input type="password" id="PwdC" name="PwdC" value=""><br>
            <input type="submit" class="button green" value="Sign Up!" name="submitS">
        </form>
        <!-- Error message system  -->
        <?php errorMsg($msg); ?>         
    </div>


    <?php footer(); ?>
</body>

</html>