<?php
	require_once("header.php");
	require_once("user_plate.php");
	require_once("db.php");	
	$connection = milf_connect();
	echo "<br />";
	echo "<a href='index.php'><button class='mn-button ui-button-text'>Back Home</button></a>";

	
	if (isset($_GET['id'])) {
		
		if (isset($_POST['change_password'])) {
			$query = sprintf("SELECT * FROM User WHERE id=%d AND password='%s'", $_GET['id'], $_POST['oldPass']);
			$result = milf_query($query, $connection);
			
			if (mysql_num_rows($result)) {
				$query = sprintf("UPDATE User SET password='%s' WHERE id=%d",
							 $_POST['newPass'], $_GET['id']);
				milf_query($query, $connection);
				echo "<script type='text/javascript'>alert('Password Updated.');</script>";
			}
			else
				echo "<script type='text/javascript'>alert('Wrong password.');</script>";
		}

		$id = $_GET['id'];
	 		
		$query = "SELECT * FROM User WHERE id=$id";
		$result = milf_query($query, $connection);
		
		if (mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
		
			echo "<h1>User Profile</h1>";
			
			if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id) {
				echo "Username: " . $username . "<br /><br />";
				echo "<form action='user.php?id=$id' method='post'>";
				echo "<input type='hidden' name='change_password' value='true' />";
				echo "Old Password: <input type='password' name='oldPass' size='10' /><br />";
				echo "New Password: <input type='password' name='newPass' size='10' /><br />";
				echo "<button type='submit'>Change Password</button></form>";
			}
				echo "<h2>Comment History</h2>";
				$query = "
				SELECT * 
				FROM Comment, Meal
				WHERE Comment.meal_id = Meal.id AND Comment.user_id=$id
				ORDER BY date DESC
				";
				$result = milf_query($query, $connection);
				
				while ($row = mysql_fetch_assoc($result)) {
					echo "<div id='result' style='clear:left; height:85px'>";
					printf("<img src='%s' width='100' height='100' style='float:left'>", $row['img']);
					echo "<div id='resultInfo' style='margin-left:110px'>";
					printf("<a href='meal.php?id=%d'><b><font size='4'>%s</font></b></a><br /><br />", 
						   $row['id'], $row['title']);
					printf("What you said about this meal:<br /><b>\"%s\"</b> on %s", $row['value'], $row['date']);
					echo "</div>";
					echo "</div>";
					echo "<br />";				
			}
		}
	}

	mysql_close($connection);		
	require_once("footer.php");	
?>