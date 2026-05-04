<?php
/* follow requesting to follow a page
send follow request to rabbitmq for a logged in user and profile being use
*/
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/path.inc');
require_once(__DIR__ . '/get_host_info.inc');
require_once(__DIR__ . '/rabbitMQLib.inc');

if(!isset($_SESSION['user_id']) || empty($_SESSION['user_id']))
{
    header("Location: /loginPage.php");
    exit();
}

if(!isset($_POST['follow_id']) || empty($_POST['follow_id']))
{
    header("Location: /profilePage.php");
    exit();
}

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
$userId = (int)$_SESSION['user_id'];
$followId = (int)$_POST['follow_id'];

$request = [ 'type' => 'follow',
    'user_id' => $userId,
    'follow_id' => $followId
];
$response = $client->send_request($request);

header("Location: /profilePage.php?user_id=$followId");
exit();
?>
