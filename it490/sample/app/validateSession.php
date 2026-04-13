<?php 
require_once(__DIR__ . '/path.inc');
require_once(__DIR__ .  '/get_host_info.inc');
require_once(__DIR__ . '/rabbitMQLib.inc'); 

function toValidateSessionTokens($token)
{
	$client = new RabbitMQClient(__DIR__ . "/testRabbitMQ.ini", "testServer");
	$request = array(); 
	$request['type'] = "validate_session";
	$request['sessionId'] = $token;
	$response = $client->send_request($request);

	return (isset($response['returnCode']) && (int)$response['returnCode'] == 1);
}
?>
