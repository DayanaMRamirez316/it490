#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
/**
 * Handles all deployment server main functions
 * 
 * Packaging, deploying, and rollback are all handled in this document, by calling different functions.
 * All functions use RMQ to send commands to the various clusters
 * 
 */
//this will print the bashscript output for debugging
function bashReport($output) {
  foreach ($output as $line) {
		echo "$line \n";
	}
}
// Called upon for connection to mySQL database
function dbConnect() {
  $mydb = new mysqli('127.0.0.1','deployer','deployPwd!','deployDB');
  //stop the script if database connection fails and then print the error
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
 * @return void
 */
function sendPackage(rabbitMQClient $client, mysqli $mydb) {
  //get the next deployment version number
  $query = "SELECT ROUND(MAX(version) + 0.01, 2) AS newVer FROM deployment_packages";
  $stmt = $mydb->prepare($query);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $newVer = $row['newVer'];
  //build the filename using the new version
  $filename = "version_$newVer.tar";

  //create package request to send through RabbitMq
  $request = array();
  $request['type'] = "package";
  $request['version'] = "$newVer";
  //send the package request to the VMs listening
  $response = $client->publish_to_exchange($request, "dev");

  //save the new package entry in deployment database 
  $query = "INSERT INTO deployment_packages (packageName, version) VALUES (?, ?)";
  $stmt = $mydb->prepare($query);
  $stmt->bind_param('ss', $filename, $newVer);
  $stmt->execute();
  //wait for the package to be created before allowing deploy
  sleep(5);

  //combin parts of the package to be in one wrapped deployment archive
  exec("/bin/bash /opt/it490/deployment/deploy_wrap.sh wrap $newVer", $output, $code);
  bashReport($output);
  if ($code != 0) return;
}
/**
 * Handles deployment to cluster of VMs
 * 
 * Sends a JSON of type "deploy" through RMQ to all listening specified environment VMs,
 * sends version archive and extracts. Also creates rollback
 * @param rabbitMQClient $client The RMQ client, with it's various functions
 * @param string $version The version to deploy
 * @param string $env The enviorment cluster where to deploy package to
 * @return void
 */
function sendDeploy(rabbitMQClient $client, $version, $env) {
  //unwrap the deployment archive before sending deploy request
  if ($version != "rollback") {
    exec("/bin/bash /opt/it490/deployment/deploy_wrap.sh unwrap $version", $output, $code);
    bashReport($output);
    if ($code != 0) return;
  } else echo 'rollback: skipping unwrap'; //skip unwrap if it is a rollback deployment 
  //build the deploy request 
  $request = array();
  $request['type'] = "deploy";
  $request['version'] = "$version";
  //send deploy request to selected environment
  $response = $client->publish_to_exchange($request, $env);
  return $response;
}
//store data to database
//mark deployment version to as passed in database
function markPass($mydb, $version){
	//update deployment_packages
	$query = "UPDATE deployment_packages SET status = 'passed' WHERE version = ?";
	$stmt = $mydb->prepare($query);
	$stmt->bind_param('s', $version);
	$stmt->execute();
}

//trigger rollback if marked as failed
function markFail($mydb, $client, $version){
	//update deployment_packages
	$query = "UPDATE deployment_packages SET status = 'failed' WHERE version = ?";
	$stmt = $mydb->prepare($query);
	$stmt->bind_param('s', $version);
	$stmt->execute();

	sendDeploy($client, "rollback", "qa");
}

//the command loop for deployment actions
function run() {
  //keep prompting until the user chooses to quit.
  $stop = false;
  while (!$stop) {
    global $argv, $mydb, $client;
    //read user input 
    $input = readline("Enter a command: (enter -l for list of acceptable commands) ");
    //make the command input case-insensitive and trim the dash for easier parsing
    $command = strtolower(ltrim($input, '-'));

    switch ($command) {
      case "p": //package files from VMs 
        $response = sendPackage($client, $mydb);
        break;
      case str_starts_with($command, "d"): //deploy selected version to environment
        //split the command into status and version
        $params = explode(" ", $command);
        if (count($params) != 3) {
          echo "Need 2 parameters: [version] [environment]";
          break;
        }
        $version = $params[1];
        $env = $params[2];
        $response = sendDeploy($client, $version, $env);
        break;
      case str_starts_with($command, "m"): //mark
        $params = explode(" ", $command);
        if (count($params) != 3) {
          echo "Need 2 parameters: [p/f] [version]";
          break;
        }
        $status = $params[1];
        $version = $params[2];
        if ($status == "f") markFail($mydb, $client, $version);
        else if ($status == "p") markPass($mydb, $version);
        else echo "
          incorrect syntax
          -m  --  mark [p/f] [version]: marks specified packaged either pass or fail, rollbacks if fail";
        break;
      case "l": //list
        echo "List of suitable commands:
              -p  --  pack files from VMs
              -d  --  deploy [version] [env]: deploys specified version package to environment
              -m  --  mark [status] [version]: marks specified packaged either pass or fail, rollbacks if fail
              -l  --  list commands
              -q  --  quit
             ".PHP_EOL;
        break;
      case "q"; //quit deployment command loop
        $stop = true;
        continue 2;
      default: //catch invalid commands
        echo "Enter a valid command please (-l for list of commands)";
        break;
    }
  }
}

$mydb = dbConnect();
$client = new rabbitMQClient("deploy.ini","testServer");
//start the deployment command loop
run();

echo "Bye".PHP_EOL	;
?>
