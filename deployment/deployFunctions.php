#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
/**
 * Handles all deployment server main functions
 * 
 * Packaging, deploying, and rollback are all handled in this document, by calling different functions.
 * All functions use RMQ to transmit commands to the various clusters
 * 
 */

// Called upon for connection to mySQL database
function dbConnect() {
  $mydb = new mysqli('127.0.0.1','deployer','deployPwd!','deployDB');
  
  if ($mydb->errno != 0) {
	  echo "failed to connect to database: " . $mydb->error . PHP_EOL;
	  exit(0);
  }

  echo "successfully connected to database" . PHP_EOL;
  return $mydb;
}

/**
 * Handles packaging of VMs
 * 
 * Sends a JSON of type "package" through RMQ to all listening PROD VMs,
 * creates new version number for package, and inserts new fields in DB
 * @param rabbitMQClient $client The RMQ client, with it's various functions
 * @param mysqli $mydb The database client, with it's various functions
 * @return string[] Returns an array of strings, formatted in JSON
 */
function transmitPackage(rabbitMQClient $client, mysqli $mydb) {
  $query = "SELECT ROUND(MAX(version) + 0.01, 2) AS newVer FROM deployment_packages";
  $stmt = $mydb->prepare($query);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $newVer = $row['newVer'];
  $filename = "version_$newVer.tar";

  $request = array();
  $request['type'] = "package";
  $request['version'] = "$newVer";
  $response = $client->publish_to_exchange($request, "dev");

  $query = "INSERT INTO deployment_packages (packageName, version, status, created_by) VALUES (?, ?, \"untested\", \"mgb46\")";
  $stmt = $mydb->prepare($query);
  $stmt->bind_param('ss', $filename, $newVer);
  $stmt->execute();

  usleep(200000);

  exec("/bin/bash /opt/it490/deployment/deploy_wrap.sh wrap $newVer", $output, $code);
  foreach ($output as $line) {
		echo "$line \n";
	}
  if ($code != 0) return;
  return $response;
}

function transmitDeploy(rabbitMQClient $client, $version) {
  exec("/bin/bash /opt/it490/deployment/deploy_wrap.sh unwrap $version", $output, $code);
  foreach ($output as $line) {
		echo "$line \n";
	}
  if ($code != 0) return;
  $request = array();
  $request['type'] = "deploy";
  $request['version'] = "$version";
  $response = $client->publish_to_exchange($request, "dev");
  return $response;
}

function run() {
  $stop = false;
  while (!$stop) {
    global $argv, $mydb, $client;
    $input = readline("Enter a command: (enter -l for list of acceptable commands) ");
    $command = strtolower(ltrim($input, '-'));

    switch ($command) {
      case "p": //pack
        $response = transmitPackage($client, $mydb);
        break;
      case "d": //deploy
        $version = $argv[2];
        $response = transmitDeploy($client, $version);
        break;
      case "l":
        echo "List of suitable commands:
              -p  --  pack files from VMs
              -d  --  deploy specified package to all VMs (includes version number parameter)
              -l  --  list commands
              -q  --  quit
             ".PHP_EOL;
        break;
      case "q";
        $stop = true;
        continue 2;
      default:
        echo "Enter a valid command please (-l for list of commands)";
        break;
    }
  }
}

$mydb = dbConnect();
$client = new rabbitMQClient("deploy.ini","testServer");

run();

echo "Bye".PHP_EOL	;
?>
