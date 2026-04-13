<?php
session_start();
$error = "";
if (isset($_SESSION["error"])){
    $error = $_SESSION["error"];
    unset($_SESSION["error"]);
}
?>
<!DOCTYPE html>
<html>
   <link rel = "stylesheet" href= "loginPage.css">
<head> 
   <title> Log In </title>
</head>
<body>
    <div class ="loginForm">
    <form id ="validateForm" action ="/app/loginRequest.php" method = "POST">
        <h1> Log In</h1>
        <input name = "email" id ="email" placeholder = "Email"/>
        <input name = "password" id="password" type = "password" placeholder = "Password"/>
        <button type = "submit"> Log In </button>
    </form>  
    <?php if (!empty($error)): ?>
        <p style = "color: black; margin-top:15px;"> 
            <?php echo $error; ?> </p>
    <?php endif; ?>
    </div>
    <div class = "signUp">
    <p> Don't Have an Account? Sign Up Here! <a href = "registration.html"> Sign Up </a><p>
    </div>
</body>

</html>
