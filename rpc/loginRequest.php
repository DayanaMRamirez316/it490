<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

session_start();
if (!isset($_SESSION['user'])) 
{
  header("Location:/sample/loginPage.html");
  exit();
}

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
if (isset($argv[1]))
{
  $msg = $argv[1];
}
else
{
  $msg = "test message";
}

$request = array();
$request['type'] = "Login";
$request['email'] = $_POST['email'];
$request['password'] = $_POST['password'];
$response = $client->send_request($request);

if ($response['returnCode'] == 0)
//when login is sucessful, session variables are set and user is brought to index page. 
//If unsucessful, user is brought back to login page with an error message. 
  {
    session_start();
    $_SESSION['user'] = $request['email'];
    $_SESSION['session_key'] = $response['session_key'];

  header("Location:/sample/app/index.php");
  exit();
  } 
  else 
  {
    header("Location:/sample/loginPage.html?error=1");
    exit();
  }

$payload = json_encode($response);
echo $payload;

