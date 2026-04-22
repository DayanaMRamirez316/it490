 #!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');




function doLog($request)

{
    $logLine = "[" . $request['timestamp'] . "] [" . $request['level'] . "] [" . $request['vm'] 
		. "] [" . $request['service'] . "] " . $request['message'] . "\n";

     file_put_contents('/var/log/it490/app.log', $logLine, FILE_APPEND);
}



function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  $type=strtolower($request['type']);
  switch($type)
  {
  
   case "log":
            
	doLog($request);
            return array("returnCode" => 1, "message" => "log written");
    }

    return array("returnCode" => 0, "message" => "unsupported request type");
}

$server = new rabbitMQServer("logging.ini","testServer");

echo "loggingDaemon BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "loggingDaemon END".PHP_EOL;
exit();

