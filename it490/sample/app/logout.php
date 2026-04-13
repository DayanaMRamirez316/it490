<?php 
session_start(); 
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

if(isset($_SESSION['token'])) {
    $client = new rabbitMQClient("testRabbitMQ.ini","testServer");
    $request = array();
    $request['type'] = "delete_session";
    $request['token'] = $_SESSION['token'];
    $response = $client->send_request($request);
}
session_unset(); 
session_destroy();
header("Location: /loginPage.php");
exit();
?> 
