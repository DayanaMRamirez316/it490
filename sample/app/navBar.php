<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
    <link rel = "stylesheet" href= "navBar.css">

<header>
    <nav class="navbar">
        <ul class="nav-list">
            <li><a href="/index.php">Home</a></li>
	    <li><a href="/api/listGames.php">Search Games</a></li>
	    <li><a href="/api/recommendations.php">Your Recommended</a></li>
	    <li><a href="/api/display_reviews_general.php">Browse Reviews</a></li>
	    <li><a href="/api/following.php">Your Following</a></li>
        </ul>
        <ul class = "nav-menu">
<!-- session is up and user_id exists 
then show navabar with profile link and logout -->
<?php
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>  
<li><a href="/profilePage.php">Profile</a></li>
<li> <form action="/app/logout.php" method = "POST">
		<button type ="submit"> Log Out</button> </form></li>
        <?php endif; ?>
        </ul>
    </nav>
</header>
