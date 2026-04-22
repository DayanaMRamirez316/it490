#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function sendLogMessage($severity, $message) {

  $client = new rabbitMQClient("logging.ini","testServer");

  $request = array();
  $request['type'] = "log";
  $request['severity'] = "$severity";
  $request['message'] = "$message";

  $response = $client->send_request($request);
}
