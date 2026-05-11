#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function sendLog($level, $message, $service, $vm)
{
    $client = new rabbitMQClient("logging.ini", "testServer");

    $request = array();
    $request['type'] = "log";
    $request['timestamp'] = date("Y-m-d H:i:s");
    $request['level'] = $level;
    $request['message'] = $message;
    $request['service'] = $service;
    $request['vm'] = $vm;

    $client->publish($request);
}

