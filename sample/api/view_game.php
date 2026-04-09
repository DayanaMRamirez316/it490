<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
include_once(__DIR__ . '/../app/navBar.php'); 
require_once('../app/validateSession.php');
if (!isset($_SESSION['token']) || empty($_SESSION['token']))
{
	header("Location: /loginPage.php");
	exit();
}

if (isset($_SESSION['message']))
{
        $message = $_SESSION['message'];
	echo "<p>$message</p>";
        unset($_SESSION['message']);
	unset($message);	
}

?>

<!DOCTYPE html>

<html>

</html>

<?php
require_once('../app/path.inc');
require_once('../app/get_host_info.inc');
require_once('../app/rabbitMQLib.inc');


$searchInput ="";
if($_SERVER["REQUEST_METHOD"] == "GET" && $_GET["game_id"] !="" )
{
	if(isset($_GET["game_id"])){
		
		$game_id = urlencode($_GET["game_id"]);
	}

	//get game details using $game_id
	$client = new rabbitmqClient("testRabbitMQ.ini","testServer");
	
	$request = array();
	$request['type'] = 'gameDetails';
	$request['gameId'] = $game_id;
	$response = $client->send_request($request);

	if($response['returnCode'] == 1){
		//display 
		$game = $response;

	}
	
	echo "<ul>";

    	echo "<li>";
	
	$name = htmlspecialchars($game['name']); 
	echo "$name | ";
	
	echo "Released: " . htmlspecialchars($game['released']) . " | ";
	$released =  htmlspecialchars($game['released']); 
	
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

    	echo "| Platforms: ";

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
	echo "<br>"; 


	if (!empty($game['background_image'])) {
    	$image = htmlspecialchars($game['background_image']);
    	echo "<img src='$image' alt='$name' style='max-width:400px; display:block; margin-top:10px; margin-bottom:10px;'>";
	}


	if (!empty($game['description_raw'])) {
    	$description = htmlspecialchars($game['description_raw']);
    	echo "<p>$description</p>";
	}


	if (!empty($game['metacritic'])) {
    	$metacritic = htmlspecialchars($game['metacritic']);
    	echo "<p><strong>Metacritic:</strong> $metacritic</p>";
	}


	if (!empty($game['tags'])) {
    	echo "<p><strong>Tags:</strong> ";
    	$tagNames = [];
    	foreach ($game['tags'] as $tag) {
        	$tagNames[] = htmlspecialchars($tag['name']);
    	}
    	echo implode(", ", $tagNames);
    	echo "</p>";
	}		
    	echo "</li>";
	

}
?>
