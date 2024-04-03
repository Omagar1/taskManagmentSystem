<?php
ob_start(); // so that redirect works 
session_start();
require "functions.php";
require_once "dbConnect.php";
require "editRowProcess.php";
?>

<!DOCTYPE html>
<html lang="en">
<?php
$pageName = basename($_SERVER["PHP_SELF"]); // getting the name of the page so head can add it to the Previous stack
head($pageName); // from functions.php, echoes out the head tags  
?>
<script>
    function newColabCode(userID){
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            //console.log("edit responce: "+this.responseText)
            var newColabCode = this.responseText;
            // change Visule 
            document.getElementById(userID + "collabCodeUR").innerHTML = newColabCode
            //chnage DB
            useAJAXedit({ID: Number(userID), collabCode: newColabCode, table:"user"});
        }else if(this.readyState == 4 && (this.status == 403 || this.status == 404 )) {
            
        }
        };

        
        xmlhttp.open("GET", "AJAXcollabCode.php?" , true);
        xmlhttp.send()
    }
</script>
<?php
$errors = []; // id then msg as key pair
// ---------------------------------------------------- account Validation -------------------------------------------------
if (isset($_POST["tokenEUR"]) And $_SESSION["tokenEUR"] == $_POST["tokenEUR"])  {
    $valsToValadate = $_POST;
    var_dump($valsToValadate);
    $valadationPassed = true;
    // qry to get existing usernames for valadation
    $qry = "SELECT username FROM user";
    $stmt = $conn->prepare($qry);
    $stmt->execute();
    $existingUsernames = $stmt->fetch(PDO::FETCH_BOTH);
    if ($existingUsernames == null){
        $existingUsernames = []; 
    }
    foreach ($valsToValadate as $column => $valToCheck){
        // ---------- Username valadtaion ----------
        if($column == "username"){
            if ($valToCheck == "") {
                $msg = "Username Must Not be Empty";
                $errors["Uname"] = $msg;
                $valadationPassed = false;
            }elseif (in_array($valToCheck, $existingUsernames)){
                $msg = "Username Already Exists ";
                $errors["Uname"] = $msg;
                $valadationPassed = false;
            }
        }   
        // ---------- email valadtaion ----------
        if($column == "email"){
            if ($valToCheck == "") {
                $msg = "email Must Not be Empty";
                $errors["Email"] = $msg;
                $valadationPassed = false;
            }elseif (!filter_var($valToCheck, FILTER_VALIDATE_EMAIL)) { // from https://www.w3schools.com/php/php_form_url_email.asp
                $msg = "Invalid email format"; 
                $errors["Email"] = $msg;
            }
        }
        // ---------- password valadtaion ----------
        if($column == "password"){
            if($valToCheck != $valsToValadate["PasswordComfirm"]){
                $msg = "Passwords must match";
                $errors["Password"] = $msg;
                $errors["PasswordComfirm"] = $msg;
                $valadationPassed = false;
            }elseif ($valToCheck == "") {
                $msg = "Password Must Not be Empty";
                $errors["Password"] = $msg;
                $errors["PasswordComfirm"] = $msg;
                $valadationPassed = false;
            } elseif (strlen($valToCheck) > 20) {
                $msg = "Password Must Not Be Over 20 Characters in length";
                $errors["Password"] = $msg;
                $errors["PasswordComfirm"] = $msg;
                $valadationPassed = false;
            }else{
                // password valadation passed so setting up SQL 
                unset($valsToValadate["PasswordComfirm"]); 
                $valsToValadate["password"] = password_hash($valsToValadate["password"], PASSWORD_DEFAULT);
                unset($valsToValadate["submitPR"]);
            }
        }


    }
    // ---------- Passed Validation; passing data On to process page ----------
    if ($valadationPassed){
        unset($valsToValadate["tokenEUR"]);
        $msg = editRow($valsToValadate, "user", $conn);
    }

}else {

}
$qry = "SELECT * FROM user WHERE ID = ?";
$stmt = $conn->prepare($qry);
$stmt->execute([$_SESSION["userID"]]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$tokenEUR =  md5(uniqid(rand(), true)); // for new task lists
$_SESSION["tokenEUR"] = $tokenEUR; // for new task lists
?>
<script>
    const tokenEUR = "<?php echo $tokenEUR ?>"
</script>
</head>


<body >
    <?php navBar(false, basename($_SERVER["PHP_SELF"]) ); ?>
    </div>
    <div id="main">
        
        <div class="tabs">
            <button onclick="changeTab('account')"  id="accountTab" class="tab selected ">Account</button>
            <script>shiftTab('account', 0)</script>
            <button onclick="changeTab('changePassword')"  id="changePasswordTab" class="tab">Change Password</button>
            <script>shiftTab('changePassword', 1)</script>
        </div>
        <div id="accountContainer" class = "showing">
            <div class="taskHeader">
                <h2> Mannage <?php echo $row["username"];?>'s Account</h2>
            </div>

            <table class="taskTable centreTable">
                <tr class="taskTr">
                    <td class="taskTd tableDisplay">Username: </td>
                    <td class="taskTd tableDisplay">
                        <button onclick = "allowEdit('username' , <?php echo $row['ID']?>,'UR')" class=" clear editButtons editButtonsID<?php echo $row['ID']?>UR" id="<?php echo $row['ID'] ?>usernameUR"><p><?php echo $row["username"] ?></p></button>
                        <input onclick = "allowEdit('username' , <?php echo $row['ID']?>,'UR')" type = "text" id = "usernameInput<?php echo $row['ID']?>UR" class =" inputbutton hidden editInputs editInputsID<?php echo $row['ID']?>UR" name = "usernameInput<?php echo $row['ID']?>UR"  value = "<?php echo  $row['username'] ?>"/>
                        <div id="usernameError"></div>
                    </td>
                

                </tr>
                <tr class="taskTr">
                    <td class="taskTd tableDisplay">Password: </td>
                    <td class="taskTd tableDisplay">
                        <button onclick="changeTab('changePassword')"class="button red" id="<?php echo $row['ID'] ?>passwordUR"> Reset Password</button>
                        
                        <div id="passwordError"></div>
                    </td>
                </tr>
                <tr class="taskTr">
                    <td class="taskTd tableDisplay">Email: </td>
                    <td class="taskTd tableDisplay">
                        <button onclick = "allowEdit('email' , <?php echo $row['ID']?>,'UR')" class=" clear editButtons editButtonsID<?php echo $row['ID']?>UR" id="<?php echo $row['ID'] ?>emailUR"><p><?php echo $row["email"] ?></p></button>
                        <input onclick = "allowEdit('email' , <?php echo $row['ID']?>,'UR')" type = "text" id = "emailInput<?php echo $row['ID']?>UR" class =" inputbutton hidden editInputs editInputsID<?php echo $row['ID']?>UR" name = "emailInput<?php echo $row['ID']?>UR"  value = "<?php echo  $row['email'] ?>"/>
                        <div id="emailError"></div>
                    </td>
                </tr>
                <tr class="taskTr">
                    <td class="taskTd tableDisplay">collabCode</td>
                    <td class="taskTd tableDisplay">
                        <p class="" id="<?php echo $row['ID'] ?>collabCodeUR"><?php echo $row["collabCode"] ?></p>
                        <button onclick="newColabCode(<?php echo $row['ID'] ?>)"class="button">New Code</button>
                        
                        <div id="collabCodeError"></div>
                    </td>
                </tr>
                <script> errorMsg(<?php if (isset($errors)){ echo json_encode($errors);} // need the json encode part ?>)  </script>
            </table>
        </div>
        <div id="changePasswordContainer" class="hidden">
            <form action="settings.php" method="post">
                <label for="PwdNew">Enter New Password:</label> <br>
                <input type="password" id="password" name="password" value="<?php
                if (isset($valsToValadate["password"])) {
                    echo $valsToValadate["password"];
                } else {
                    echo "";
                }
                ?>"><br>
                <label for="PasswordComfirm"> Confirm Password:</label> <br>
                <input type="password" id="PasswordComfirm" name="PasswordComfirm" value="<?php
                if (isset($valsToValadate["PasswordComfirm"])) {
                    echo $valsToValadate["PasswordComfirm"];
                } else {
                    echo "";
                }
                ?>"><br>
                <input type="hidden" id="ID" name="ID" value="<?php echo $_SESSION["userID"]; ?>">
                <input type="hidden" id="tokenEUR" name="tokenEUR" value="<?php echo $tokenEUR; ?>">
                <input type="submit" name="submitPR" class="button green" value="Save">

            </form>
        </div>
    </div>

s
    <?php footer(); ?>
</body>

</html>