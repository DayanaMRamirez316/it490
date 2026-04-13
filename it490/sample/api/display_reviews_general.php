
<!DOCTYPE html>
<html>
	<?php include_once('../app/navBar.php'); ?>
	<h1>BROWSE REVIEWS!</h1>
	<form method="GET">
		<label>Search:</label>
		<input type='text' name='search' value="<?php echo htmlspecialchars($search); ?>"></input>
		<input type='submit'>
	</form>
</html>

<?php
if (!isset($_SESSION['token']) || empty($_SESSION['token']))
{
	header("Location: ../loginPage.php");
	exit();
}


require_once('../app/path.inc');
require_once('../app/get_host_info.inc');
require_once('../app/rabbitMQLib.inc');

$client = new rabbitMQClient("../app/testRabbitMQ.ini","testServer");

function getReviews($search){
	global $client;
	$request = array();
	$request['type'] = 'get_all_reviews';
	$request['search_string'] = $search;
	$result = $client->send_request($request);
	return $result;
}
$search = isset($_GET['search']) ? $_GET['search'] : '';
$response = getReviews($search);

if($response['returnCode'] == 1 && !empty($response['array'])){
	foreach($response['array'] as $review){
		if($review['is_private'] == 1){
			continue;
		}
		echo "<div style='border-bottom: 1px solid #ccc; padding: 10px;'>";
		echo "<p>";
		//review id?
		//echo <label>Review ID: </label>;
		//echo $review['review_id'];
		//echo <br>;
		
		//show game id first
		echo "<label>Game ID:  </label>";
		echo $review['game_id'];
		echo "<br>";
		//game name second
		echo "<label>Game Name:  </label>";
		echo htmlspecialchars($review['game_name']);
		echo "<br>";
		//new line for review id and email hyperlink to profilePage.php
		echo "<label>Reviewer ID:  </label>";
		$id = $review['reviewer_id'];
		echo $id;
		echo " - ";
		echo "<label>Reviewer Email:  </label>";
		echo "<a href='../profilePage.php?user_id=$id'>" . $review['reviewer_email'] . "</a>";
		echo "<br>";
		//new line for rating
		echo "<label>Rating:  </label>";
		echo $review['rating'];
		echo "<br>";
		//new line for actual review hide if its private
		echo "<label>Review: </label>";
		//This logic is not needed because it should be displayed if public and not if private 
		//if($review['is_private'] == 0){
		//	echo htmlspecialchars($review['text']);
//			$pubOrPriv = "Public";
		//}else{
		//	echo "Cannot show private reviews";
		//	$pubOrPriv = "Private";

	//	}
		//bellow say if it private or not
		echo htmlspecialchars($review['text']);
		echo "<br>";
		//echo "<label>Public/Private: </label>";
		//echo "$pubOrPriv";
		echo "</p>";
		echo "</div>";
	}
}else{
	echo "<p> no reviews found</p>";
}

?>
