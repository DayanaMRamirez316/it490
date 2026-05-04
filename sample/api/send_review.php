<?php
session_start();
require_once('../app/path.inc');
require_once('../app/get_host_info.inc');
require_once('../app/rabbitMQLib.inc');
require_once('../app/phpValidation.php');
require_once('../app/validateSession.php');

if (!isset($_SESSION['token']) || empty($_SESSION['token']))
{
        header("Location: /loginPage.php");
        exit();
}

if(!isset($_POST['name'])){
	header("Location: /api/listGames.php");
	exit();
}

$game_name = trim($_POST['name']);
$genre = trim($_POST['genre']);
$release = trim($_POST['released']);
$user_id = trim($_SESSION['user_id']);
$rating = trim($_POST['reviewScore']);
$reviewText = trim($_POST['reviewText']);
$is_private = isset($_POST['is_private']) ? 1 : 0; //is it checked or not. Checked= 1 

if ($rating > 100 || $rating < 0){
        $_SESSION["message"] = "There was an error with your review: Please enter a review score between 1 and 100";
        header ("Location: /api/listGames.php");
        exit();
}

if (strlen($reviewText) > 500){
        $_SESSION["message"] = "There was an error with your review: Please enter a review that is 5000 chars or less";
        header ("Location: /api/listGames.php");
        exit();
}

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");

$request = array();
$request ['type'] = "new_review";
$request ['user_id'] = $user_id;
$request ['game'] = $game_name;
$request ['rating'] = $rating;
$request ['reviewText'] = $reviewText;
$request ['genre'] = $genre;
$request ['release_date'] = $release;
$request ['is_private'] = $is_private;

$response = $client->send_request($request);

if (isset($response['returnCode']) && (int)$response['returnCode'] === 1){
        $_SESSION["message"] = "You have sucessfully reviewed " . $request['game'];
        header ("Location: /api/listGames.php");
        exit();
}
else {
        header("Location: /index.php");
        exit();
}

