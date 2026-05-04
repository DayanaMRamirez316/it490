<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


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
$request ['type'] = "Registration";
$request ['email'] = $_POST['email'];
$request ['password'] = $_POST['password'];
$response = $client->send_request($request);

if(isset($response['returnCode']) && $response['returnCode'] == "1"){
	//aproved
	header("Location: /loginPage.php");
	exit();
}else{
	//denided
	header("Location: /registration.html");
	exit();
	
}
