<?php
ob_start(); // so that redirect works 
session_start();
require "functions.php";
require "loginProcess.php";
require "signUpProcess.php";
require_once "dbConnect.php";
?>

<!DOCTYPE html>
<html lang="en">
<?php
$_SESSION["previous"] = []; // initalising the Previous Stack
$pageName = basename($_SERVER["PHP_SELF"]); // getting the name of the page so head can add it to the Previous stack
head($pageName); // from functions.php, echoes out the head tags  
?>
<!-- <script>
    var tabsOpen = 0; 
</script> -->
<?php
$errorsL = []; // id then msg as key pair
$errorsSU = []; // id then msg as key pair
function isInArray($searchFor, $inArr){
    foreach($inArr as $val){
        //test
        // echo"val: ";
        // var_dump($val);
        // echo "\n";
        if(implode($val) == $searchFor){
            return true;
        }
    }
    return false;
}
// ---------------------------------------------------- logIn Validation -------------------------------------------------
if (isset($_POST['submitL'])) {
    
    $uname = trim($_POST['Uname']);
    $pword = trim($_POST['Password']);
    $valadationPassed = true;
    // ---------- Username valadtaion ----------
    if ($uname == "") {
        $msg = "Username Must Not be Empty";
        $errorsL["Uname"] = $msg;
        $valadationPassed = false;
    }
    // ---------- password valadtaion ----------
    //hence continuous not seperate if statements
    if ($pword == "") {
        $msg = "Password Must Not be Empty";
        $errorsL["Password"] = $msg;
        $valadationPassed = false;
    } elseif (strlen($pword) > 20) {
        $msg = "Password Must Not Be Over 20 Characters in length";
        $errorsL["Password"] = $msg;
        $valadationPassed = false;
    }
    // ---------- Passed Validation; passing data On to process page ----------
    if ($valadationPassed){
        $msg = checkLoginData($uname, $pword, $conn); 
        $errorsL["Password"] = $msg;
        $errorsL["Uname"] = "";
    }
// ---------------------------------------------------- Sign Up Validation -------------------------------------------------
} elseif (isset($_POST['submitSU'])) {

    $unameSU = trim($_POST['UnameSU']);
    $email = trim($_POST['Email']); 
    $pwordSU = trim($_POST['PasswordSU']);
    $comfirmPword = trim($_POST['PasswordComfirm']);
    $valadationPassed = true;
    // qry to get existing usernames for valadation
    $qry = "SELECT username FROM user";
    $stmt = $conn->prepare($qry);
    $stmt->execute();
    $existingUsernames = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($existingUsernames == null){
        $existingUsernames = []; 
    }
    //var_dump($existingUsernames); 
    //echo "does Bob exixst: "; //test
    //var_dump(isInArray("Bob", $existingUsernames));
    // ---------- Username valadtaion ----------
    if ($unameSU == "") {
        $msg = "Username Must Not be Empty";
        $errorsSU["UnameSU"] = $msg;
        $valadationPassed = false;
    }elseif (isInArray($unameSU, $existingUsernames)){
        $msg = "Username Already Exists ";
        $errorsSU["UnameSU"] = $msg;
        $valadationPassed = false;
    }
    // ---------- email valadtaion ----------
    if ($email == "") {
        $msg = "email Must Not be Empty";
        $errorsSU["Email"] = $msg;
        $valadationPassed = false;
    }elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // from https://www.w3schools.com/php/php_form_url_email.asp
        $msg = "Invalid email format"; 
        $errorsSU["Email"] = $msg;
    }

    // ---------- password valadtaion ----------
    //hence continuous not seperate if statements
    if($pwordSU != $comfirmPword){
        $msg = "Passwords must match";
        $errorsSU["PasswordSU"] = $msg;
        $errorsSU["PasswordComfirm"] = $msg;
        $valadationPassed = false;
    }elseif ($pwordSU == "") {
        $msg = "Password Must Not be Empty";
        $errorsSU["PasswordSU"] = $msg;
        $errorsSU["PasswordComfirm"] = $msg;
        $valadationPassed = false;
    } elseif (strlen($pwordSU) > 20) {
        $msg = "Password Must Not Be Over 20 Characters in length";
        $errorsSU["PasswordSU"] = $msg;
        $errorsSU["PasswordComfirm"] = $msg;
        $valadationPassed = false;
    }
    // ---------- Passed Validation; passing data On to process page ----------
    if ($valadationPassed){
        $msg = createUser($unameSU, $email, $pwordSU, $conn); 
        $errorsSU["PasswordSU"] = $msg;
    }
    $currentDisplay = "signUp";

}else {

}

?>
</head>


<body id>
    <?php navBar(false, basename($_SERVER["PHP_SELF"]) ); ?>
    </div>
    <div id="mainLogin">
        <div class="tabs">
            <button onclick="changeTab('logIn')"  id="logInTab" class="tab selected ">Log In</button> 
            <script>shiftTab('logIn', 0)</script>
            <button onclick="changeTab('signUp')"  id="signUpTab" class="tab ">Sign Up</button>
            <script>shiftTab('signUp', 1)</script>
            <button onclick="changeTab('aboutUs')"  id="aboutUsTab" class="tab ">About Us</button>
            <script>shiftTab('aboutUs', 2)</script>
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
            <h2>TaskMaster Pro</h2>
            <p>
            <b>Elevate your productivity</b> with TaskMaster Pro, the ultimate web application for task management. 
            Not only can you <b>organize tasks into TaskLists and break them down into stages</b>, but you can also <b>collaborate in real-time</b> with colleagues or friends. 
            Invite others to join your TaskList using a unique <b>Collab Code</b>, and watch as teamwork transforms your project planning. 
            Whether you’re delegating tasks, sharing updates, or tracking progress, TaskMaster Pro’s <b>cloud-based</b> platform ensures that everyone’s contributions are synchronized across all devices. 
            With TaskMaster Pro, teamwork is just a few clicks away, making it the perfect tool for collective efficiency and enhanced productivity.
            </p>
            <button onclick="changeTab('signUp')"  id="SignUpNowButton" class="button green">Sign Up Now!</button>
            <p> *Decription Provided by MS Copilot AI assistant. </p>
            <p> *Cloud-based is assuming the system is setup on a server as intended instead of in local host it has been for development.</p>
        </div>
        
        <script>changeTab("<?php if (isset($currentDisplay)){ echo $currentDisplay;}else{echo "logIn";} ?>")</script>
               
    </div>


    <?php footer(); ?>
</body>

</html>