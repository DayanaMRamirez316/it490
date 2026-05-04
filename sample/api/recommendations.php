<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
//ini_set('log_errors', 1);
//ini_set('error_log', '/tmp/php_errors.log');

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
echo "<!-- DEBUG: response type = " . gettype($response) . " --> \n";
if(is_string($response)){
	echo "<!-- DEBUG: response string = " . htmlspecialchars($response) . " -->\n";
}
if(!is_array($response)){
	echo "<p>Error: invalid response from server. got: " . htmlspecialchars(var_export($response, true)) . "</p>";
}



if (empty($response['games']) || !is_array($response['games'])){

	echo" <p>You have not reviewed enough games for recommendations </p>";
	
	print_r($response);

}else{
	echo "<h2>Video Game Recommendations </h2>";
	echo "<ul>";
	//echo "<pre>";
	//echo "Response key: ";
	//print_r(array_keys($response));
	//echo "\n Full response\n";
	//print_r($response);
	//echo "</pre>";

	foreach ($response['games'] as $game) {
		echo "<li>";
	
	
		$name = htmlspecialchars($game['name']); 
		$game_id = $game['id'];
		echo "<a href='view_game.php?game_id=" . urlencode($game_id) . "'>$name</a> || ";
	
		$released =  htmlspecialchars($game['released'] ?? 'TBA');

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
	echo "</ul>";
}	
?>

