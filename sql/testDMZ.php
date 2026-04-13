<?php
require_once('rabbitMQLib.inc');
$client = new rabbitMQClient("dmz.ini","testServer");
$request = array("type" => "recomendGenre", "genreList" => "board-games,action");
$response = $client->send_request($request);
var_dump($response);
?>
