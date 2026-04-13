<?php 
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
function validateLogin()
{
    $emailError="";
    $passwordError="";

    if(empty($_POST["email"])){
        $emailError = "Please enter an email or password.";
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailError = "Email or password is invalid";
            //https://www.w3schools.com/php/php_form_url_email.asp
        }
    }
    //password validation
    if(empty($_POST["password"])){
        $passwordError = "Please enter your password.";
    } else {
        $password = (string)$_POST["password"];
        $passwordRegex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
        if (!preg_match($passwordRegex, $password)) {
            $passwordError = "Email or password is invalid.";
        }
    }
    if ($emailError != "")
        return $emailError;
    if ($passwordError != "")        
       return $passwordError;
    return "";
}  

   
