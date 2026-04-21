#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

$mydb = new mysqli('127.0.0.1','deployer','deployPwd!','deployDB');

if ($mydb->errno != 0)
{
	echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	exit(0);
}

echo "successfully connected to database".PHP_EOL;

function transmitPackage(rabbitMQClient $client) {
  global $mydb;
  $query = "SELECT ROUND(MAX(version) + 0.01, 2) AS newVer FROM deployment_packages";
  $stmt = $mydb->prepare($query);
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $newVer = $row['newVer'];
  $filename = "version_$newVer.tar";

  $request = array();
  $request['type'] = "package";
  $request['version'] = "newVer";
  $response = $client->publish($request, "dev");

  $query = "INSERT INTO deployment_packages (packageName, version, status, created_by) VALUES (?, ?, \"untested\", \"mgb46\")";
  $stmt = $mydb->prepare($query);
  $stmt->bind_param('ss', $filename, $newVer);
  $stmt->execute();
  return $response;
}

function transmitDeploy(rabbitMQClient $client) {
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
  case "deploy":
    $response = transmitDeploy($client);
}
echo "client received response: ".PHP_EOL;
print_r($response);
echo "\n\n";

echo $argv[0]." END".PHP_EOL;
