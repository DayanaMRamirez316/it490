<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/app/validateSession.php');
if (!isset($_SESSION['token']) || empty($_SESSION['token']))
{
	header("Location: /loginPage.php");
	exit();
}

if (!toValidateSessionTokens($_SESSION['token'])) 
{
	session_unset();
	session_destroy(); 
	header("Location: /loginPage.php");
	exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Ariel, sans-serif;
            background-color: #f0f0f0;
        }
        h1 {
            color: #333;
        }
        </style>
        </head>
        <body>
            <?php include_once(__DIR__ . '/app/navBar.php'); ?>
            <h1> Welcome </h1>
	</form>
</body>
    </head>
</html>
