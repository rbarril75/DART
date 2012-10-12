<?php
	$return_url = "index.php";
	if (isset($_SERVER['REQUEST_URI'])) {
		$return_url = $_SERVER['REQUEST_URI'];
	}
	
	echo "<div style='margin-left:25px'>";
	if (!isset($_SESSION['username'])) {
		echo "<form action='login.php?return=$return_url' method='post'>";
		echo "Username: <input type='text' name='username' size='10' />   ";
		echo "Password: <input type='password' name='password' size='10' />   ";
		echo "<button type='submit'>Log In</button>    ";
		echo "<a href='register.php'><button>Register</button></a></form>";	
	} else {
		$username = $_SESSION['username'];
		$user_id = $_SESSION['user_id'];
		
		echo "Welcome, <a href='user.php?id=$user_id'>$username</a> <a href='logout.php?return=$return_url'>Log Out</a>";
	}
	echo "</div>";
	echo "<br />";
?>
