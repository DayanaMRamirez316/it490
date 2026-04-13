<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('phpValidation.php');

$error = validateLogin();
if ($error != "") {
	$_SESSION["error"] = $error;
	header("Location: /loginPage.php");
	exit();
}

$email = trim($_POST['email']);
$password = trim($_POST['password']);
$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
/*
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "test message";
}
*/
$request = array();
$request ['type'] = "login";
$request ['email'] = $_POST['email'];
$request ['password'] = $_POST['password'];
$response = $client->send_request($request);

if (isset($response['returnCode']) && (int)$response['returnCode'] === 1){
	if (isset($response["token"])) {
		$_SESSION['token'] = $response["token"];
		$_SESSION['user_id'] = $response["user_id"];
		$_SESSION['email'] = $response['email'];
	}
	header ("Location: /index.php");
	exit();
}
else {
	$_SESSION['error'] = "Invalid email or password.";
	header("Location: /loginPage.php");
	exit();
}
