<?php
//https://stackoverflow.com/questions/6249707/check-if-php-session-has-already-started
//start if not started yet
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//make sure youre logged in before profile page access 
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id']))
{
    header("Location: /loginPage.php");
    exit();
}

require_once(__DIR__ . '/app/path.inc');
require_once(__DIR__ . '/app/get_host_info.inc');
require_once(__DIR__ . '/app/rabbitMQLib.inc');
require_once(__DIR__ . '/app/validateSession.php');

$client = new rabbitMQClient("testRabbitMQ.ini","testServer");


//$userId = $_SESSION['user_id']; //get the userId from the person thats logged in 
//database request!! for the reviews
// view another profile page if userid is in url, and if not, its their own page 
if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $profileID = (int)$_GET['user_id'];
} else {
    $profileID= (int)$_SESSION['user_id'];
}
$viewerId = (int)$_SESSION['user_id']; //the person thats logged in and viewing the profile page, if its their own page, then viewerId and profileID are the same, if its another profile, then they are different
$myAccount = ((int)$_SESSION['user_id'] === $profileID); //is this my account being viewed or another profile
$request = ['type' => 'get_profile_all',
	'user_id' => $profileID,
	'follow_id' => $profileID,
    'viewer_id' => (int)$_SESSION['user_id']
];
$response = $client->send_request($request);

if(isset($response['returnCode']) && $response['returnCode'] == '0'){
        header("Location: /profilePage.php");
    	exit();
    }
//if reviews is in the array then store to reviews
if (isset($response ['array']) && is_array($response['array'])) 
    {
        $reviews = $response['array'];
        } else {
    $reviews = [];
}

$followStatus = false;

if (!$myAccount && $_SESSION['user_id'] != $profileID) { //if its not my account, then check if im following the profile or not because the issue is clicking profile href is checking if i followed myself 
    
    if(isset($response['followCode']) && $response['followCode'] == '1'){
        $followStatus = true; //user follows the profile
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Profile</title>
       </head>
       <body>
           <?php include_once(__DIR__ . '/app/navBar.php'); ?>
           <h1> <?php if  ($myAccount): ?>
                My Profile
                <?php else: ?>
                User Profile
                <?php endif; ?>
           </h1>
           <p>viewing user id: <?php echo $profileID; ?></p>
           <p> owner mode: <?php echo $myAccount ? '1' : '0'; ?></p>
          <!-- <p><strong>Email:</strong> <?php echo ($_SESSION['email']); ?></p>-->
           <?php if ($myAccount): ?>
           <p><strong>Email:</strong> <?php echo ($_SESSION['email']); ?></p>
	   <?php else:?>
	   <p><strong>Email:</strong> <?php echo ($response['email']); ?></p>
            <!-- other profile would show follow button, but if mine, don't show it -->
             <form method = "POST" action="/app/followUser.php">
                <input type="hidden" name="follow_id" value="<?php echo $profileID; ?>">
                <button type="submit"><?php echo $followStatus ? 'Unfollow' : 'Follow'; ?></button> 
            </form>
           <?php endif; ?>
        <?php if ($myAccount): ?>
              <p><strong>Username:</strong>Not implemented yet </p>
           <?php endif; ?>
           <h2> Reviews </h2>
           <?php if (empty($reviews)): ?>
               <p>Your Reviews Will Appear Here When You Submit a Review!</p>
           <?php else: ?>
                   <?php foreach ($reviews as $review): ?>
                    <?php 
                    if (!$myAccount && $review['is_private'] == 1) { continue;}//if its not my account and the review is private, then skip showing the review, if its my account, show all reviews including private ones 
                    ?>
                       <div class="reviewCard">
                           <strong>Game:</strong> <?php echo $review['game']; ?><br>
                           <strong>Rating:</strong> <?php echo $review['rating']; ?>/100<br>
                           <strong>Comment:</strong> <?php echo $review['text']; ?> <br>
                           <strong>Date:</strong> <?php echo $review['created']; ?> </div>

                            <?php if ($myAccount && $review ['is_private'] == 1): ?>
                            <em>This Review is Private</em>
                            <?php endif; ?>
				
                           <?php if ($myAccount): ?><!-- only show the button to make private or public if its my account, if its not my account, don't show the button -->
                            <form method="POST" action="/app/makePrivateReview.php">
                                <input type="hidden" name="game" value="<?php echo $review['game']; ?>">
                                <!-- If text is private: "Make Public" and vice versa.  -->
                                <button type="submit"><?php echo $review['is_private'] == 1 ? 'Make Public' : 'Make Private'; ?></button> 
                            </form> 
                            <?php endif; ?>
<?php echo "<br>" ?>
                          
                   <?php endforeach; ?>
           <?php endif; ?>
       </body>
</html>

