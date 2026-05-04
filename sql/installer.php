#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function Install($bundleName, $version, $bundlePath){
	//download bundle from deployment server
	//extract it
	//copy and direct it to /var/www/sample
	//run command to restart apache2
	//send message that install was succesful

	//deployment server ip
	$server = "100.125.53.7";
	//your username and password
	$user="";
	$passWd="";

	//download
	$conn = ftp_connect($server);
	if(!$conn){
		return array("returnCode" => 0, "message" => "FTP connection failed");
	}

	if(!ftp_login($conn, $user, $passWd)){
		return array("returnCode" => 0, "message" => "FTP login failed");
	}

	ftp_pasv($conn, true);

	if(!ftp_get($conn, "/tmp/update.tar", $bundlePath, FTP_BINARY)){
		return array("returnCode" => 0, "message" => "download failed");
	}

	ftp_close($conn);
	
	//extract
	$extract = "/tmp/install_" . time();
	mkdir($extract);
	exec("tar -xf /tmp/update.tar -C " . $extract);
	exec("cp -r " . $extract . " /* /var/www/sample/");
	exec("sudo systemctl restart apache2");
	exec("rm -rf /tmp/update.tar" . $extract);

	return array("returnCode" => 1, "message" => "Install successful!");
}

function requestProcessor($request){
	if($request['type'] == 'install'){
		return Install(
			$request['bundleName'],
			$request['version'],
			$request['bundlePath']
		);
	}
	return array("returnCode" => 0, "message" => "Unknown request type");
}
$server = new rabbitMQServer("installer.ini","deployHost");
$server->process_requests('requestProcessor');

?>
