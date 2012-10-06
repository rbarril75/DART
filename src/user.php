<?php
	require_once("header.php");
	require_once("user_plate.php");
	require_once("db.php");	
	$connection = milf_connect();
	echo "<br />";
	echo "<a href='index.php'><button class='mn-button ui-button-text'>Back Home</button></a>";

	
	if (isset($_GET['id'])) {
		if (isset($_POST['preference_update'])) {
			$query = sprintf("UPDATE User SET likes='%s', dislikes='%s' WHERE id=%d",
							$_POST['user_likes'], $_POST['user_dislikes'], $_GET['id']);
			milf_query($query, $connection);			
		}

		$id = $_GET['id'];
	 		
		$query = "SELECT * FROM User WHERE id=$id";
		$result = milf_query($query, $connection);

		
		if (mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
		
			echo "<h1>User Info</h1>";
			echo "Username: {$row['username']}<br/>";
			
			if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
				$query = "SELECT * FROM User WHERE id=$id";
				$result = milf_query($query, $connection);
				$row = mysql_fetch_assoc($result);				
				$likes = $row['likes'];
				$dislikes = $row['dislikes'];				
				
				echo "<h2>Preferences (comma separated)</h2>";
				echo "<form action='user.php?id=$id' method='post'>";
				echo "<input type='hidden' name='preference_update' value='true' />";
				echo "Likes: <input type='text' size='50' name='user_likes' value='$likes' /><br/>";
				echo "Dislikes: <input type='text' size='50' name='user_dislikes' value='$dislikes' /><br/>";
				echo "<input type='submit' value='Update' /></form>";
			}
		}
	}

	mysql_close($connection);		
	require_once("footer.php");	
?>