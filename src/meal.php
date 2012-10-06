<?php
	require_once("header.php");
	require_once("user_plate.php");
	require_once("db.php");
	$connection = milf_connect();
	echo "<br />";
	echo "<a href='index.php'><button class='mn-button ui-button-text'>Back Home";
	echo "</button></a>";
	
	if (isset($_POST['delete_comment'])) {
		$query = "DELETE FROM Comment WHERE id='{$_POST['delete_comment']}'";
		milf_query($query, $connection);
	}
	if (isset($_POST['delete_rating']) && isset($_SESSION['user_id']) && isset($_GET['id'])) {
		$query = "DELETE FROM Ate WHERE user_id='{$_SESSION['user_id']}' AND meal_id='{$_GET['id']}'";
		milf_query($query, $connection);
	}
	
	if (isset($_POST['new_comment'])) {
		$query = sprintf("INSERT INTO Comment(user_id, meal_id, value) VALUES (%d, %d, '%s')",
								$_SESSION['user_id'], $_GET['id'], $_POST['new_comment']);
		$result = milf_query($query, $connection);
	}
	if (isset($_POST['new_rating'])) {
		$connection = milf_connect();
		$query = sprintf("INSERT INTO Ate(user_id, meal_id, rating) VALUES (%d, %d, %d)",
								$_SESSION['user_id'], $_GET['id'], $_POST['new_rating']);
		$result = mysql_query($query, $connection);
	}
	if (isset($_GET['id'])) {
		$connection = milf_connect();
		
		$id = $_GET['id'];
			
		$query = "SELECT * FROM Meal WHERE id=$id";
		$result = milf_query($query, $connection);
	
		$query2 = "SELECT AVG(rating), meal_id FROM Ate WHERE meal_id='$id' GROUP BY meal_id";
		$result2 = milf_query($query2, $connection);	
		
		$rating = "Unrated";
		if (mysql_num_rows($result2)) {
			$row2 = mysql_fetch_row($result2);
			$rating = $row2[0];
			$rating = number_format($rating, 2);
		}
		
		if (mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
	
			echo "<div align='center'>";
			echo "<br />";
			echo "<h1><font color ='darkgreen'>Meal Info ($rating)</font></h1>";			
			echo "<img src='{$row['img']}' /><br />";
			echo "<br />";
		  		
			echo "<table border='1'>";
			echo "<tr>";
			echo "<td bgcolor='lightgreen'>Name</td>";
			echo "<td>{$row['title']}</td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td bgcolor='lightgreen'>Ingredients</td>";
			echo "<td>" . implode(", ", explode("|", $row['ingredients'])) . "</td>";
			echo "</tr>";
			echo "</table>";

			echo "<br />";			
			echo "<h3>Description</h3>";
			echo $row['description'];
				
			echo "<h3>Requirements</h3>";			
			foreach (explode("|", $row['requirements']) as $i)
				echo "$i<br/>";			
				
			echo "<h3>Cuisine / Calories</h3>";
			echo "{$row['cuisine']}, {$row['calories']}";
			
			echo "<h3>Preparation</h3>";
			foreach (explode("|", $row['preparation']) as $i)
				echo "<p>$i</p>";			
			
			
			if (isset($_SESSION['username'])) {
				$connection = milf_connect();
				$query = "SELECT * FROM Ate where user_id={$_SESSION['user_id']} AND meal_id=$id";
				$result = milf_query($query, $connection);
				
				$row = mysql_fetch_assoc($result);

				if ($row) {
					$rating = $row['rating'];
					echo "You rated this meal at: $rating";
					echo "<form action='{$_SERVER['REQUEST_URI']}' method='post' name='rating'>";
					echo "<input type='hidden' name='delete_rating' value='true' /></form>";
					echo "<a href='javascript: document.rating.submit();'>Remove Rating</a>";
				} else {	
					echo "<form action='meal.php?id=$id' method='post'>";
	 				echo "Rate this meal: <select name='new_rating'>";
	 				echo "<option value='1'>1</option>";
	 				echo "<option value='2'>2</option>";
	 				echo "<option value='3'>3</option>";
	 				echo "<option value='4'>4</option>";
					echo "<option value='5'>5</option>";
					echo "</select>";
					echo "<input type='submit' value='Rate' /></form>";	
				}
			}			
	
 			echo "<h2>Comments:</h2>";
 			
			$query = "SELECT * FROM User, Comment WHERE meal_id='$id' AND User.id=Comment.user_id";
			$result = milf_query($query, $connection);	
			
			while ($row = mysql_fetch_assoc($result)) {
				$remove_me = "";
				if (isset($_SESSION['user_id']) && $row['user_id'] == $_SESSION['user_id']) {
					echo "<form action='{$_SERVER['REQUEST_URI']}' method='post' name='form{$row['id']}'>";
					echo "<input type='hidden' name='delete_comment' value='{$row['id']}' /></form>";
					$remove_me = "<a href='javascript: document.form{$row['id']}.submit();'>Remove!</a>";
				}
				echo "Posted by: {$row['username']} ";
				echo "$remove_me";
				echo "<br />";
				echo "Date: " . date("F j, Y, g:i a", strtotime($row['date'])) . "<br />";
				echo "Comment: {$row['value']} <br />";
				echo "<hr color='darkblue' style='width:20%;'/>";			
			}			
 			
 			if (isset($_SESSION['user_id'])) {
				echo "<form action='meal.php?id=$id' method='post'>";
				echo "<textarea name='new_comment' rows='4' cols='50'></textarea><br />";
 				echo "<input type='submit' value='Add Comment' />";
				echo "</form>";
			} else {
				echo "<b>Log in to comment</b>";
			}
			echo "</div>";
		}
	}
	
	mysql_close($connection);			
	require_once("footer.php");	
?>