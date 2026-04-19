<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("installer.ini", "deployHost");

$request = array(
	"type" => "install",
	"bundleName" => "package.tar",
	"version" => 1,
	//running deploy.sh created directory called deployment with the package.tar	
	"bundlePath" => "/home/dmr49/deployment/package.tar"
);

$response = $client->send_request($request);
var_dump($response);
?>
