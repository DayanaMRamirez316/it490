<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/path.inc');
require_once(__DIR__ . '/get_host_info.inc');
require_once(__DIR__ . '/rabbitMQLib.inc');

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id']))
{
    header("Location: /loginPage.php");
    exit();
}

//received game from form??? if not, then gooooooooo back to profile page
// because we dont know what review to make private without the game name, 
// nd if it is set, then send the request to rabbit and make the review private
if(!isset($_POST['game']) || empty($_POST['game']))
{
    header("Location: /profilePage.php");
    exit();
}
$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
$request =['type' => 'private', //change the review privacy
'user_id' => (int)$_SESSION['user_id']
,'game' => $_POST['game'] //game reveiw to change 
];
$response = $client->send_request($request);
header("Location: /profilePage.php"); //go back after making review public or private 
exit();
?>