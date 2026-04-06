<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("installer.ini", "deployHost");

$request = array(
	"type" => "install",
	"bundleName" => "update.tar",
	"version" => 1,
	//running deploy.sh created directory called deployment with the update.tar	
	"bundlePath" => "/home/dmr49/deployment/update.tar"
);

$response = $client->send_request($request);
var_dump($response);
?>
