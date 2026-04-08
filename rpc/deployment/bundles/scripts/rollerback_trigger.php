<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$client = new rabbitMQClient("testRabbitMQ.ini", "deploymentServer");

$request = array();
$request['type'] = "rollback";
$request['version'] = "v1.0";
$request['destination'] = "production";

$response = $client->send_request($request);
print_r($response);