#!/usr/bin/php
<?php
//this code is meant to be run as a cron job on the databse VM
//it's purpose is to clean up expired sessions in the Sessions table
//could be replaced later with a cron job that calls mysql directly but I wanted to use php for now

require_once('../logging/sendLog.php');

$mydb = new mysqli('127.0.0.1','userInfo','theBestPassword','data');

if ($mydb->errno != 0)
{
        //echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	sendLog("ERROR", "failed to connect to database: " . $mydb->error, "cleanup_sessions_cron.php", "VM4");
        exit(0);
}

$query = "DELETE FROM Sessions WHERE expires < NOW()";

$mydb->query($query);

$mydb->close();
