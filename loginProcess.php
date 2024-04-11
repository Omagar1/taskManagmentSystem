<?php

function checkLoginData($uname, $pword, $conn) {
    //echo "checkLoginData Ran </br>"; //test 
    if ($uname != "" Or $pword != ""){
        try{
            $qry = "SELECT ID, username, `password`FROM user WHERE username = :uname;";
            $stmt = $conn->prepare($qry);
            $stmt->bindParam('uname', $uname, PDO::PARAM_STR); // asiging varibles to SQL statement 
            $stmt->execute();
            $count = $stmt->rowCount(); 
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            //echo $count; //test
            //echo "password in login: ". $pword. "</br>"; //test
            //var_dump (password_verify($pword, $row["password"]));//test
            if(!empty($row) And password_verify($pword, $row["password"]) == 1 And $count == 1) { //checks if there is a qry produced a username and Hassed passwords match  
                // seting values used in other code
                $_SESSION['userID'] = $row['ID'];
                //echo  $_SESSION['userID']; // test
                $_SESSION['username'] = $row['username'];
                $_SESSION["loggedIn"] = true;
                //echo "USER LOGGED IN"; //test
                header("location: mainPage.php");
                return ""; // to stop vs code being mad that there's no return value  
            } else {
                //echo "USER NOT LOGGED IN"; //test

                
                $msg = "Invalid Username or Password!";
                return $msg;
            }
        } catch (PDOException $e) {
            //echo "Error : ".$e->getMessage();// dev error mesage
            return "error";
        }
    }else {
        return "Both fields are required!";
        
    };
}

function resetPassword(){
    //use email to reset password
}
?>

