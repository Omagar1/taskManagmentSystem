<?php
// require "functions.php";
// require "loginProcess.php"; 
function createUser($uname, $email, $pword, $con){
    try{
        //checkemail(); to be added when email functionality is added
        // seting up values for user table
        $newCollabCode = generateCollabCode($con);
        $pwordHashed =  password_hash($pword, PASSWORD_DEFAULT);
        
        //echo "test:"  . $pword; //test 
        // teste test 
        //echo "test hash:"  . $pwordHashed; //test
        // creating user in DB 
        $qry = "INSERT INTO user (username, `password`, email, collabCode) VALUES (?,?,?,?);"; 
        $stmt = $con->prepare($qry);
        $stmt->execute([$uname, $pwordHashed, $email, $newCollabCode]);
        $newUserID = $con->lastInsertId();
        // setting up user's genral tasklist
        $qry = "INSERT INTO tasklist (`name`, ownerID) VALUES (?,?);"; 
        $stmt = $con->prepare($qry);
        $stmt->execute(["Genral", $newUserID]);

        // logs users in after sign up
        //echo "password in signUp: ". $pwordHashed. "</br>"; //test
        return checkLoginData($uname, $pword, $con);
    } catch(PDOException $e){
        echo "Error : ".$e->getMessage();// dev error mesage
        return "error";
    }
    
}
    

?>
