#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


function transmitPackage(rabbitMQClient $client) {
  $request = array();
  $request['type'] = "deploy";
  $request['version'] = "1.0";
  $response = $client->publish($request, "dev");
  return $response;
}

$client = new rabbitMQClient("deploy.ini","testServer");
$command = strtolower($argv[1]);

switch ($command) {
  case "pack":
    $response = transmitPackage($client);
}
echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;
