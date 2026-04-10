<?php
session_start();
require_once('../app/path.inc');
require_once('../app/get_host_info.inc');
require_once('../app/rabbitMQLib.inc');
require_once('../app/phpValidation.php');
require_once('../app/validateSession.php');
include_once('../app/navBar.php');

if (!isset($_SESSION['token']) || empty($_SESSION['token']))
{
        header("Location: /loginPage.php");
        exit();
}

$user_id = trim($_SESSION['user_id']);


$client = new rabbitMQClient("testRabbitMQ.ini","testServer");

$request = array();
$request ['type'] = "get_recommendations";
$request ['user_id'] = $user_id;



$response = $client->send_request($request);

if (empty($response['genres'])){

	echo" <p>You have not reviewed enough games for recommendations </p>";

}else{
	
	print_r($response['genres']);
		
       //this code converts an array of genres(which is what is stored in the db) to 
	//an array of genre slugs which is what rawg uses as essentially tags for their 
	//genres when searching the api. Exampe Grand Strategy would be converted 
	//to grand-strategy. I realized later that their list feature actually returns 
	//genre slugs so I could have stored those in the db instead. Ill fix it later 
	 $genreSlugs = [];

	foreach ($response['genres'] as $genre) {
    	$genreSlugs[] = strtolower(str_replace(' ', '-', trim($genre)));
	}

   
        $genreParam = implode(',', $genreSlugs);

	//make connection with rabbit use $genreParam
	$client = new rabbitMQClient("testRabbitMQ.ini","testServer");
	$request = array();
	$request ['type'] = "genres";
	$request ['genre'] = $genreParam;

	$response = $client->send_request($request);
	if($response['returnCode'] == 1 ){
		if(isset($response['genre'])){
			$rawgAPIdata = $response['genre'];
		}
	}else{
		echo "no genres found .";
		exit();
	}

	echo "<h2>Video Game Recommendations:</h2>";
	echo "<ul>";

	foreach ($rawgAPIdata as $game) {

    	echo "<li>";
	
	
	$name = htmlspecialchars($game['name']); 
	$game_id = $game['id'];
	echo "<a href='view_game.php?game_id=" . urlencode($game_id) . "'>$name</a> || ";
	
	$released =  htmlspecialchars($game['released']);

	echo "Released: $released  || ";
	
	if($released == ""){
		$released = "N/A";
	}

	echo "Genres: ";
	$mainGenre = "N/A";
    	if (!empty($game['genres'])) {
        	foreach ($game['genres'] as $genre) {
            	echo htmlspecialchars($genre['name']) . " ";
		}
		$mainGenre = htmlspecialchars($game['genres'][0]['name']);
    	}

    	echo "|| Platforms: ";

    	if (!empty($game['platforms'])) {
        	foreach ($game['platforms'] as $platform) {
            	echo htmlspecialchars($platform['platform']['name']) . " ";
        	}
	}
	 echo "<form action='review_game.php' method='POST' style='display:inline;'>";

    	echo "<input type='hidden' name='name' value='$name'>";
    	echo "<input type='hidden' name='released' value='$released'>";
    	echo "<input type='hidden' name='genre' value='$mainGenre'>";

    	echo "<input type='submit' value='Review Game'>";

    	echo "</form>";	

    	echo "</li>";
}	







}
