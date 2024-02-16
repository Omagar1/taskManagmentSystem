<?php
ob_start(); // so that redirect works 
session_start();
require "functions.php";
require_once "dbConnect.php";
?>

<!DOCTYPE html>
<html lang="en">
<?php
$_SESSION["previous"] = []; // initalising the Previous Stack
$pageName = basename($_SERVER["PHP_SELF"]); // getting the name of the page so head can add it to the Previous stack
head($pageName); // from functions.php, echoes out the head tags  
?>
<?php

?>
</head>


<body id>
    <?php navBar(false, basename($_SERVER["PHP_SELF"]) ); ?>
    </div>
    <div id="mainLogin">
        <div class="tabs">
            <button onclick="changeTab('logIn')" id="logInTab" class="tab selected first">Log In</button>
            <button onclick="changeTab('signUp')" id="signUpTab" class="tab tab-2">Sign Up</button>
            <button onclick="changeTab('aboutUs')"id="aboutUsTab" class="tab last">About Us</button>
        </div>

         <!-- Login Form  -->
        <div id="logInContainer" class="showing">
            <form action="index.php" method="post" id="logInForm">

                <div class="txtLeft"><label for="Uname">Username</label></div>
                <input type="text" id="Uname" name="Uname" value="<?php if(isset($uname)){echo $uname;}?>"><br>
                <div id="UnameError"></div>

                <div class="txtLeft"><label for="Password">Password</label></div>
                <input type="password" id="Password" name="Password" value="<?php if(isset($pword)){echo $pword;}?>"><br>
                <div id="PasswordError"></div>

                <input type="submit" class="button green" value="login" name="submitL">
                <script> errorMsg(<?php if (isset($errorsL)){ echo json_encode($errorsL);} // need the json encode part ?>)  </script> 
            </form>
            <button class="button red">Forgot Password?</button> 
        </div>

         <!-- sign Up Form  --> 
        <div id="signUpContainer" class="hidden">
            <form action="index.php" method="post" id="signUpForm">
                <div class="txtLeft"><label for="UnameSU">Username</label></div>
                <input type="text" id="UnameSU" name="UnameSU" value="<?php if(isset($unameSU)){echo $unameSU;}?>"><br>
                <div id="UnameSUError"></div>

                <div class="txtLeft"><label for="Email">Email</label></div>
                <input type="email" id="Email" name="Email" value="<?php if(isset($email)){echo $email;}?>"><br>
                <div id="EmailError"></div>

                <div class="txtLeft"><label for="PasswordSU">Password</label></div>
                <input type="password" id="PasswordSU" name="PasswordSU" value="<?php if(isset($pwordSU)){echo $pwordSU;}?>"><br>
                <div id="PasswordSUError"></div>

                <div class="txtLeft"><label for="PasswordComfirm">Comfirm Password</label></div>
                <input type="password" id="PasswordComfirm" name="PasswordComfirm" value="<?php if(isset($comfirmPword)){echo $comfirmPword;}?>"><br>
                <div id="PasswordComfirmError"></div>

                <input type="submit" class="button green" value="Sign Up!" name="submitSU">
                <script> errorMsg(<?php if (isset($errorsSU)){ echo json_encode($errorsSU);} // need the json encode part ?>)  </script> 
            </form>
        </div>

         <!-- About Us   --> 
        <div id="aboutUsContainer" class="hidden">
            <p>
                Lorem ipsum dolor sit amet consectetur adipisicing elit. 
                Quis aperiam unde ex ipsum corporis, doloribus, velit enim culpa explicabo vero,
                amet assumenda voluptatem pariatur dolorem quae repudiandae quisquam. Maiores, 
                inventore.
            </p>
        </div>
        
        <script>changeTab("<?php if (isset($currentDisplay)){ echo $currentDisplay;}else{echo "logIn";} ?>")</script>
               
    </div>


    <?php footer(); ?>
</body>

</html>