<?php
require_once('rabbitMQLib.inc');
$client = new rabbitMQClient("dmz.ini","testServer");
$request = array("type" => "listGames", "search" => "zelda");
$response = $client->send_request($request);
var_dump($response);
?>
