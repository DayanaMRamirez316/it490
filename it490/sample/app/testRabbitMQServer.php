#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function doLogin($email,$password)
{
	if($email == "test" && $password == "test")
		return true;
    // lookup username in databas
    // check password
	if ($email == "test" && $password == "test")
		return true;
	else
		return false;
    //return false if not valid
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  $type=strtolower($request['type']);
  switch($type)
  {
  case "login":
	$ok = doLogin($request['email'], $request ['password']);
	if ($ok) {
	    return array(
		   "returnCode" => 1,
       		   "message" => "Login accepted"
	    );
	}
	else{
	    return array(
	           "returnCode" => 0,
		   "message" => "Login denied"
	    );
	}
  }
}
$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

