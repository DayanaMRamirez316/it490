<?php

session_start();
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


?>


<!DOCTYPE html>
<html>


</html>

<?php

echo "Reviewing Game: " .  htmlspecialchars($_POST['name']) . " | Genre: " . htmlspecialchars($_POST['genre']) . " | Released on: " . htmlspecialchars($_POST['released']);

echo "<form action='send_review.php' method='POST'>";

echo "<label>Enter review score (1-100):</label>";
echo "<br>";
echo "<input type='number' name='reviewScore' min='1' max='100' required>";

echo "<br>";

echo "<label>Enter Full Review Here:</label>";
echo "<br>";
//echo "<input type='text' name='reviewText' style='width: 700px; height: 250px;'>";
echo "<textarea name='reviewText' rows='30' cols='100' maxlength='5000' required></textarea>";

echo "<br>";
echo "<input type='checkbox' name='is_private' value='1'> Make Review Private (Only visible to you.)</label>";
echo "<br>";
echo "<input type='submit' value='Submit Review'>";

$name = htmlspecialchars($_POST['name']);
$genre = $_POST['genre'];
$released = $_POST['released'];

echo "<input type='hidden' name='name' value='$name'>";
echo "<input type='hidden' name='released' value='$released'>";
echo "<input type='hidden' name='genre' value='$genre'>";

echo "</form>";
?>
