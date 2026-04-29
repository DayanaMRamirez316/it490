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
<form action="<?php echo ($_SERVER["PHP_SELF"])?>" method="POST">
                <label>Search</label>
                <input type="search" name="search">
                <input type="submit">
        </form>
</html>

<?php
require_once('../app/path.inc');
require_once('../app/get_host_info.inc');
require_once('../app/rabbitMQLib.inc');
require_once('../../logging/sendLog.php');

$searchInput ="";
$games = array();

print_r($_POST);
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search"]) && $_POST["search"] !="" )
{
	$searchInput = $_POST["search"];
	
	$client = new rabbitMQClient("../app/testRabbitMQ.ini","testServer");
	//send api request
	$request = array();
	$request['type'] = 'listGames';
	$request['search'] = $searchInput;
	$response = $client->send_request($request);

	if($response['returnCode'] == 1){
		$games = $response['games'];
		echo "<h2>Video Game List:</h2>";
		echo "<ul>";

		foreach ($games as $game) {
			echo "<li>";

			//$dataName = $game['name'];
			$name = htmlspecialchars($game['name']);
        		$released = htmlspecialchars($game['released']);	
			$game_id = $game['id'];
			echo "<a href='view_game.php?game_id=" . urlencode($game_id) . "'>$name</a> || ";
	
			echo "Released: $released || ";
	
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
        	 	   		echo  htmlspecialchars($platform['platform']['name']) . " ";
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
		
	}else{
		sendLog("ERROR", "listGames request failed", "listGames.php", "webserver");
		echo "something went wrong";
	}
?>
