#!/usr/bin/php
<?php
/*
testLoginWIthDB.php is the main backend rabbitmq server for the project. 
This file gets requests from the php files called by frontend through rabbit. 
These request types include : login, registration, sessions, reviews, follows, and anythinelse profile related. 
This file queries and updates the mysql database!! 
Note: It does say testLoginWithDB, but obviously it just became way more than login and more so a file full of backend functions.
*/
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');


$mydb = new mysqli('127.0.0.1','userInfo','theBestPassword','data');


if ($mydb->errno != 0)
{
	echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	exit(0);
}

echo "successfully connected to database".PHP_EOL;


function doLogin($email,$password)
//this is the function that handles login. It accepts the email and password and checks if it is correct. 
// If it is correct, it creates a session token for the user and returns it to the frontend.
{
    global $mydb;
    $query = "select id, email, password from Users where Users.email= ?;";
    $stmt = $mydb->prepare($query);
    if ($stmt === false) {
        echo "Failed to prepare statement: " . $mydb->error . PHP_EOL;
        return array("returnCode" => "2", "message" => "Failed to prepare statement");
    }

    $stmt->bind_param('s', $email);  

    if (!$stmt->execute())
{
	echo "failed to execute query:".PHP_EOL;
	echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	return array("returnCode" => "2", "message" => "db error");
    }
     $response = $stmt->get_result();


     if ($response && $response->num_rows > 0) 
    {
        $row = $response->fetch_assoc();
	
        
        if (password_verify($password, $row['password'])) 
	{
	    $token = bin2hex(random_bytes(32));
	    $expiration_date = date("Y-m-d H:i:s", strtotime("+1 hour"));
	    $user_id = $row['id'];

	    $query = "INSERT INTO Sessions (user_id, session_token, expires) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE session_token = ?, expires = ?;";
	    $stmt = $mydb->prepare($query);
	    $stmt->bind_param('issss', $user_id, $token, $expiration_date, $token, $expiration_date);  
    	    $stmt->execute();
            return array("returnCode" => "1", "message" => "Login successful", "token" => "$token", "email" => $row['email'], "user_id" => $row['id']);
        }
    }

    return array("returnCode" => "0", "message" => "Login denied");
}

function doRegister($email, $password)
//funtion to handle registration. accepting email and password and 
//creates a new user in the database if the email is not already taken.
{

    global $mydb;
   
    $query = "SELECT email FROM Users WHERE email = ?";
    $stmt = $mydb->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $response = $stmt->get_result();
   
       if ($response && $response->num_rows > 0) {
        return array("returnCode" => "0", "message" => "user already exists");
    }
   
    $passwordHash = password_hash($password, PASSWORD_BCRYPT); 
    $query = "insert into Users (email, password) values (?, ?);";
    $stmt = $mydb->prepare($query);
    $stmt->bind_param('ss', $email, $passwordHash);
    if (!$stmt->execute())
{
        echo "failed to execute query:".PHP_EOL;
        echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
        return array("returnCode" => "2", "message" => "db error");
    }

    return array("returnCode" => "1", "message" => "successful registration");
}

    function doValidate($sessionID)   
//this is to validate a session token. It accepts a session token and checks if it is valid. 
// If  valid, update the expiration date of the session token and return true. If not, return false. 
// This checks if a user is logged in or not.
  {
	 global $mydb;
	 $query = "SELECT * FROM Sessions WHERE session_token = ? AND expires > NOW()";
	 $stmt = $mydb->prepare($query);
         $stmt->bind_param('s', $sessionID);

	 if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	    return array("returnCode" => 2, "message" => "db error /session not valid");
	 }
	 $response = $stmt->get_result();

	 if ($response && $response->num_rows > 0) {	
	 $stmt->close();
	 $expiration_date = date("Y-m-d H:i:s", strtotime("+1 hour"));
	 $query = "UPDATE Sessions SET expires = ? WHERE session_token = ?";
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('ss', $expiration_date, $sessionID);
	 if (!$stmt->execute()) {
   		 return ["returnCode" => 2, "message" => "failed to update expiration"];
}
        return array("returnCode" => 1, "message" => "valid session");
	 }
	$query = "DELETE FROM Sessions WHERE session_token = ?";
        $stmt = $mydb->prepare($query);
        $stmt->bind_param('s', $sessionID);
        $stmt->execute();

	return array("returnCode" => 0, "message" => "invalid session");
    
 }
function deleteSession($sessionID)
//this logs out the user by deleting the session token from the database. 
// REquest type is delete_session and session token is sent.     
{
	global $mydb;
	$query = "DELETE FROM Sessions WHERE session_token = ?";
	$stmt = $mydb->prepare($query);
	$stmt->bind_param('s', $sessionID);
        $stmt->execute();

	if (!$stmt->execute())
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.php_EOL;
		return array ("returnCode" => 0, "message" => "db error");
	}
	return array ("returnCode" => 1, "message" => "session deleted/ logged out!!!");
}

function newReview($user_id, $game, $rating, $reviewText, $genre, $release, $is_private)
//Accepts the user_id, game name, rating, review text, genre, release date, and whether the review is private or not.
//this makes the review 
{
	global $mydb;
	$query = "INSERT IGNORE INTO Games (game, genre, release_date) VALUES (?, ?, ?)";
	$stmt = $mydb->prepare($query);
        $stmt->bind_param('sss', $game, $genre, $release);
	if (!$stmt->execute())
        {
                echo "failed to execute query:".PHP_EOL;
                echo __FILE__.':'.__LINE__.":error: ".$mydb->error.php_EOL;
                return array ("returnCode" => 0, "message" => "db error");
        }


	$stmt->close();

	$query = "SELECT game_id FROM Games WHERE game = ?";
        $stmt = $mydb->prepare($query);
        $stmt->bind_param('s', $game);

	if (!$stmt->execute())
        {
                echo "failed to execute query:".PHP_EOL;
                echo __FILE__.':'.__LINE__.":error: ".$mydb->error.php_EOL;
                return array ("returnCode" => 0, "message" => "db error");
        }


	$response = $stmt->get_result();
	$row = $response->fetch_assoc();

	$stmt->close();

	$query = "INSERT INTO User_Reviews (user_id, game_id, rating, text, is_private) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating = ?, text = ?, is_private = ? ;";
        $stmt = $mydb->prepare($query);
        $stmt->bind_param('iiisissi', $user_id, $row['game_id'], $rating, $reviewText, $is_private, $rating, $reviewText, $is_private);
        if (!$stmt->execute())
        {
                echo "failed to execute query:".PHP_EOL;
                echo __FILE__.':'.__LINE__.":error: ".$mydb->error.php_EOL;
                return array ("returnCode" => 0, "message" => "db error");
        }

	
	return array ("returnCode" => 1, "message" => "review uploaded");
}


function handlePrivate($user_id, $game)
//this changes whether a review is private or public. 
// Being used from a profile page where a user can make his/her reviews public or private. 
{      
	global $mydb;
	//gets game_id based on game name
	global $mydb;
        $query = "SELECT game_id FROM Games WHERE game = ?";
        $stmt = $mydb->prepare($query);
        $stmt->bind_param('s', $game);

        if (!$stmt->execute())
        {
                echo "failed to execute query:".PHP_EOL;
                echo __FILE__.':'.__LINE__.":error: ".$mydb->error.php_EOL;
                return array ("returnCode" => 0, "message" => "db error");
        }
        $response = $stmt->get_result();
        $row = $response->fetch_assoc();
	
	$stmt->close();

        $query = "SELECT is_private FROM User_Reviews WHERE user_id = ? AND game_id = ?;";
        $stmt = $mydb->prepare($query);
	$stmt->bind_param('ii', $user_id, $row['game_id']);
	$game_id = $row['game_id'];

        if (!$stmt->execute()) {
            echo "Failed to execute query: " . PHP_EOL;
            echo __FILE__ . ':' . __LINE__ . ": error: " . $mydb->error . PHP_EOL;
            return array("returnCode" => 0, "message" => "Database error");
    }

        $response = $stmt->get_result();
    
        if ($response->num_rows > 0) {
            $row = $response->fetch_assoc();
            $currentPrivacy = $row['is_private'];
	
             $newPrivacy = ($currentPrivacy == 0) ? 1 : 0;
	    
	    $stmt->close();

            $query = "UPDATE User_Reviews SET is_private = ? WHERE user_id = ? AND game_id = ?;";
            $stmt = $mydb->prepare($query);
            $stmt->bind_param('iii', $newPrivacy, $user_id, $game_id);

        if (!$stmt->execute()) {
            echo "Failed to execute query: " . PHP_EOL;
            echo __FILE__ . ':' . __LINE__ . ": error: " . $mydb->error . PHP_EOL;
            return array("returnCode" => 0, "message" => "Database error during update");
        }

        return array("returnCode" => 1, "message" => "Review privacy updated successfully");
    } else {
        return array("returnCode" => 0, "message" => "Review not found");
    }
} 


function getReviews($user_id){
//a user will get all the reviews they made and is displayed on their profile page. this includes 
// private ones as well as long as they are the main user of the account. The request type is get_user_reviews.
	global $mydb;
         $query = "SELECT * FROM User_Reviews Join Games ON User_Reviews.game_id = Games.game_id WHERE user_id = ?";
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('i', $user_id);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error /session not valid");
         }
	 $response = $stmt->get_result();

	 $all_rows = $response->fetch_all(MYSQLI_ASSOC);

	 return array("returnCode" => 1, "array" => $all_rows);



}


function getFollowedReviews($user_id){
//this is to accept the request type "get_user_reviews." 
//user_id is sent and it fetches all the reviews from the followed users. 
        global $mydb;
        $query = "
        SELECT 
            User_Reviews.user_id AS reviewer_id,
            Users.email AS reviewer_email,
            User_Reviews.game_id,
            User_Reviews.rating,
            User_Reviews.text,
	    User_Reviews.is_private,
	    Games.game AS game_name
        FROM 
            User_Following
        JOIN 
            User_Reviews ON User_Following.following_id = User_Reviews.user_id
        JOIN 
	    Users ON User_Reviews.user_id = Users.id
	JOIN 
	    Games ON User_Reviews.game_id = Games.game_id
        WHERE 
            User_Following.user_id = ?;
    ";
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('i', $user_id);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error /session not valid");
         }
         $response = $stmt->get_result();

         $all_rows = $response->fetch_all(MYSQLI_ASSOC);

         return array("returnCode" => 1, "array" => $all_rows);



}

function handleFollow($user_id, $follow_id){
//this handled the following and unfollwing of users by accepting the type "follow". 
//you send in a user_id and follow_id (id of the user you want to follow or unfollow). 
// This function adds your follow to the user_following table or removes it if it is 
//in there already
	 global $mydb;
         $query = "SELECT * FROM User_Following WHERE user_id = ? AND following_id = ?";
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('ii', $user_id, $follow_id);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error /session not valid");
         }
         $response = $stmt->get_result();
	 $stmt->close();




	 if ($response->num_rows > 0) {
	 	
		$query = "DELETE FROM User_Following WHERE user_id = ? AND following_id = ?";
                $stmt = $mydb->prepare($query);
		$stmt->bind_param('ii', $user_id, $follow_id);

		if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error /session not valid");
         }

		$stmt->close();
		return array("returnCode" => 1, "message" => "unfollowed");

	 
	 
	 }else{
	        $query = "INSERT INTO User_Following (user_id, following_id) VALUES (?, ?)";
                $stmt = $mydb->prepare($query);
                $stmt->bind_param('ii', $user_id, $follow_id);

                if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error /session not valid");
         }
		$stmt->close();
                return array("returnCode" => 1, "message" => "followed");

	 
	 
	 }

}

function getAll($search)
//this is to get all reviews based on a search string. It accepts the search string and checks if it is in the game name.
{
	
	global $mydb;
         $query = "
        SELECT 
            User_Reviews.user_id AS reviewer_id,
            Users.email AS reviewer_email,
            User_Reviews.game_id,
            User_Reviews.rating,
            User_Reviews.text,
	    User_Reviews.is_private,
	    Games.game AS game_name
        FROM 
            User_Reviews
        JOIN 
	    Users ON Users.id = User_Reviews.user_id
        JOIN 
	    Games ON User_Reviews.game_id = Games.game_id
        WHERE 
	    Games.game LIKE ?";
	
	 $searchTerm = "%" . $search . "%";
    
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('s', $searchTerm);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error");
         }
         $response = $stmt->get_result();

         $all_rows = $response->fetch_all(MYSQLI_ASSOC);

         return array("returnCode" => 1, "array" => $all_rows);


}

function getProfileInfo($user_id){
//this is to get the profile information of a user. It accepts the user_id and returns the email
	global $mydb;
         $query = "SELECT * FROM Users WHERE id = ?";
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('i', $user_id);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error /session not valid");
         }
	 $response = $stmt->get_result();
	 if ($response && $response->num_rows > 0) {

		$row = $response->fetch_assoc();
        	return array("returnCode" => "1", "user_id" => $row['id'], "email" => $row['email'], "joined" => $row['created'] );
	 }

	 return array("returnCode" => "0", "message" => "User does not exist");



}

function getFollowStatus($user_id, $follow_id){
//checks if you are following a user or not. It tells the front end to either show a 
//follow or unfollow button on the profile page. Request type is get_follow_status 
//and you send in the user_id and the follow_id (id of the profile page you are viewing).
	global $mydb;
         $query = "SELECT * FROM User_Following WHERE user_id = ? AND following_id = ?";
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('ii', $user_id, $follow_id);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error");
         }
         $response = $stmt->get_result();
         if ($response && $response->num_rows > 0) {

                $row = $response->fetch_assoc();
                return array("returnCode" => "1", "message" => "following");
         }

         return array("returnCode" => "0", "message" => "Not following");

} 

function getRecommendations($user_id)
//this is to get game recommendations based on the user's reviews.
//gets the average rating of each genre that the user has reviewed and returns the genres 
//that have an average rating of 75 or higher. If there are no genres with an average rating of 75 or higher, 
//it returns the top 3 genres with the highest average rating.
{

	 global $mydb;
         $query = "SELECT Games.genre, AVG(User_Reviews.rating) FROM User_Reviews JOIN Games ON User_Reviews.game_id = Games.game_id WHERE User_Reviews.user_id = ? GROUP BY Games.genre HAVING AVG(User_Reviews.rating) > 75";
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('i', $user_id);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error");
	 }

	 $response = $stmt->get_result();

	 $genres = array();

    	 while($row = $response->fetch_assoc()){
         $genres[] = $row['genre'];
	 }

	 $stmt->close();

	 if(count($genres) == 0){

        	$query = "SELECT Games.genre, AVG(User_Reviews.rating) AS avg_rating FROM User_Reviews JOIN Games ON User_Reviews.game_id = Games.game_id WHERE User_Reviews.user_id = ? GROUP BY Games.genre ORDER BY avg_rating DESC LIMIT 3";

        $stmt = $mydb->prepare($query);
        $stmt->bind_param('i', $user_id);

        if (!$stmt->execute()){
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error /session not valid");
        }

        $response = $stmt->get_result();

        while($row = $response->fetch_assoc()){
            $genres[] = $row['genre'];
        }
    }	 
	 return array("returnCode" => 1, "genres" => $genres);
}

function getProfileALL($user_id, $follow_id, $viewer_id)
//this is to get all the information for a profile page. 
// It accepts the user_id of the profile page, the follow_id (id of the profile page), 
//and the viewer_id (id of the user who is viewing the profile page).
{

        global $mydb;
	$query = "SELECT * FROM User_Reviews Join Games ON User_Reviews.game_id = Games.game_id Join Users ON User_Reviews.user_id = Users.id WHERE User_Reviews.user_id = ?";
	if($viewer_id != $user_id){ $query .= " AND User_Reviews.is_private = 0"; }
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('i', $user_id);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error /session not valid");
         }
         $response = $stmt->get_result();

	 $all_rows = $response->fetch_all(MYSQLI_ASSOC);
	 $stmt->close();
	
 	//this is here to get user email regardless of wether a review exisits or not	 
	 $query = "SELECT email FROM Users WHERE id = ?";
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('i', $user_id);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error");
	 }
	 $response = $stmt->get_result();
	 $row = $response->fetch_assoc();
	 if(empty($row)){
		 echo "EMPTY";
	 	return array("returnCode" => 0);
	 }
	 $email = $row['email'];
	 $stmt->close();

         $query = "SELECT * FROM User_Following WHERE user_id = ? AND following_id = ?";
         $stmt = $mydb->prepare($query);
         $stmt->bind_param('ii', $viewer_id, $follow_id);

         if (!$stmt->execute())
{
            echo "failed to execute query:".PHP_EOL;
            echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
            return array("returnCode" => 2, "message" => "db error");
         }
         $response = $stmt->get_result();
         if ($response && $response->num_rows > 0) {

                $row = $response->fetch_assoc();
                return array("returnCode" => 1, "followCode" => 1, "email" => $email, "array" => $all_rows);
         }

         return array("returnCode" => 1, "followCode" => 0, "email" => $email, "array" => $all_rows);

}
//gets a list of games based on user input ex: sonic - sonic the hedgehog 1992, sonic cd , .....,etc
function getGameList($search){
	//send request to dmz
	$client = new rabbitMQClient("dmz.ini", "testServer");
	$request = array( 'type' => 'listGames', 'search' => $search );
	$response = $client->send_request($request);
		
	if($response['returnCode'] == 0){
		return array("returnCode" => 0, "search" => $search , "message" => "request not found");
	}else if($response['returnCode'] == 1){
		return array("returnCode" => 1, "games" => $response['games']);
	}
}

//when user wants more information about a game ...
function getGameDetails($gameId){
	$client = new rabbitMQClient("dmz.ini", "testServer");
        $request = array( 'type' => 'details', 'search' => $gameId );
        $response = $client->send_request($request);
       // unset($client);
        if($response['returnCode'] == 0){
                return array("returnCode" => 0, "search" => $gameId , "message" => "request not found");
        }
        return array("returnCode" => 1, "search" => $gameId, "array" => $response);

}

//recomendations for recomentions.php file
function getGenre($genre){
	$client = new rabbitMQClient("dmz.ini", "testServer");
        $request = array( 'type' => 'recomendGenre', 'search' => $genre );
        $response = $client->send_request($request);
        //unset($client);
        if($response['returnCode'] != 1){
                return array("returnCode" => 0, "search" => $genre , "message" => "request not found");
        }
        return array("returnCode" => 1, "search" => $genre, "array" => $response);
}

function requestProcessor($request)
//this function processes the requests from the frontend. the type of the request is checked and then it is called. 
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['email'],$request['password']);
    case "Registration":
	    return doRegister($request['email'],$request['password']);
    case "validate_session":
	    return doValidate($request['sessionId']);
     case "delete_session":
	     return deleteSession($request['token']);
     case "new_review":
	     return newReview($request['user_id'], $request['game'], $request['rating'], $request['reviewText'], $request['genre'], $request['release_date'],$request['is_private']);
     case "private":
	     return handlePrivate($request['user_id'], $request['game']);
     case "get_user_reviews":
	     return getReviews($request['user_id']);
     case "get_followed_reviews":
	     return getFollowedReviews($request['user_id']);
     case "follow":
             return handleFollow($request['user_id'], $request['follow_id']);
     case "get_all_reviews":
             return getAll($request['search_string']);
     case "get_profile_info":
	     return getProfileInfo($request['user_id']);
     case "get_follow_status":
             return getFollowStatus($request['user_id'], $request['follow_id']);
     case "get_recommendations":
             return getRecommendations($request['user_id']);
     case "get_profile_all":
             return getProfileAll($request['user_id'], $request['follow_id'], $request['viewer_id']);
     case "listGames":
	     return getGameList($request['search']);
     case "gameDetails":
	     return getGameDetails($request['gameId']);
     case "genres":
	     return getGenres($request['genre']);

  }
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("testRabbitMQ.ini","testServer");

echo "testRabbitMQServer BEGIN".PHP_EOL;
$server->process_requests('requestProcessor');
echo "testRabbitMQServer END".PHP_EOL;
exit();
?>

