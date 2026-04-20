#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function doPack($version) {
	$success = true;
	exec("/bin/bash /opt/it490/deployment/package.sh $version", $output, $code);
	foreach($output as $line) {
		echo "$line \n";
	}
	if ($code != 0) $success = false;
	return $success;
}

function doDeploy($version) {
	$success = true;
	exec("bin/bash /opt/it490/deployment/deploy.sh $version", $output, $code);
	foreach ($output as $line) {
		echo "$line \n";
	}
	if ($code != 0) $success = false;
	return $success;
}

function requestProcessor($request) {
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type'])) return "ERROR: unsupported message type";
  $type=strtolower($request['type']);	
  switch($type) {
  	case "package":
	  $ok = doPack($request['version']);
	  if ($ok) {
	    return array(
		  "ok" => true,
       	  "message" => "Successfully Deployed"
	    );
	  }
	  else {
		return array(
	      "ok" => false,
		  "message" => "Error with Deployment"
		);
	  }
	  break;
	case "deploy":
	  $ok = doDeploy($request['version']);
	  if ($ok) {
	    return array(
		  "ok" => true,
       	  "message" => "Successfully Deployed"
	    );
	  }
	  else {
		return array(
	      "ok" => false,
		  "message" => "Error with Deployment"
		);
	  }
	  break;
  }
}
$server = new rabbitMQServer("deploy.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

