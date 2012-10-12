<?php
	require_once("header.php");
	require_once("user_plate.php");
	require_once("db.php");
	$connection = milf_connect();
	echo "<br />";
	echo "<a href='index.php'><button>Back Home";
	echo "</button></a>";
	
	if (isset($_POST['delete_comment'])) {
		$query = "DELETE FROM Comment WHERE id='{$_POST['delete_comment']}'";
		milf_query($query, $connection);
	}
	if (isset($_POST['delete_rating']) && isset($_SESSION['user_id']) && isset($_GET['id'])) {
		$query = "DELETE FROM Ate WHERE user_id='{$_SESSION['user_id']}' AND meal_id='{$_GET['id']}'";
		milf_query($query, $connection);

		/* RATING UPDATE */
		
		$query = "SELECT ingredient FROM Ingredients WHERE meal_id={$_GET['id']}";
		$result = milf_query($query, $connection);
		$ing_list = array();
		while ($i = mysql_fetch_array($result))
			$ing_list[] = $i[0];	
		
		
		$tuple = "('" . implode("', '", $ing_list) . "')";				
		$query = "SELECT * FROM Likes WHERE user_id={$_SESSION['user_id']} AND ingredient IN $tuple";
		$result = milf_query($query, $connection);
		$rating = $_POST['delete_rating_value'];
		while ($like = mysql_fetch_assoc($result)) {
			$old_rating = $like['average'];
			$old_count = $like['times'];
			$new_count = $old_count-1;
			if ($new_count != 0)
				$new_rating = ($old_rating * $old_count - $rating) / ($old_count-1);
			else
				$new_rating = 0;
			
			$query =
			"UPDATE Likes
			SET average=$new_rating, times=$new_count
			WHERE user_id={$_SESSION['user_id']} AND ingredient='{$like['ingredient']}'";
			
			milf_query($query, $connection);		
		}		
	}
	
	if (isset($_POST['new_comment'])) {
		$query = sprintf("INSERT INTO Comment(user_id, meal_id, value) VALUES (%d, %d, '%s')",
								$_SESSION['user_id'], $_GET['id'], $_POST['new_comment']);
		$result = milf_query($query, $connection);
	}
	if (isset($_POST['new_rating'])) {
		$query = sprintf("INSERT INTO Ate(user_id, meal_id, rating) VALUES (%d, %d, %d)",
								$_SESSION['user_id'], $_GET['id'], $_POST['new_rating']);
		$result = mysql_query($query, $connection);
		
		/* RATING UPDATE */		

		$query = "SELECT ingredient FROM Ingredients WHERE meal_id={$_GET['id']}";
		$result = milf_query($query, $connection);
		$ing_list = array();
		while ($i = mysql_fetch_array($result))
			$ing_list[] = $i[0];	
		
		$rating = $_POST['new_rating'];
		$new_ratings = array_fill(0, count($ing_list), $rating);
		$new_counts = array_fill(0, count($ing_list), 1); 		
		
		$tuple = "('" . implode("', '", $ing_list) . "')";				
		$query = "SELECT * FROM Likes WHERE user_id={$_SESSION['user_id']} AND ingredient IN $tuple";
		$result = milf_query($query, $connection);
		while ($like = mysql_fetch_assoc($result)) {
			$i = array_search($like['ingredient'], $ing_list);
			$old_rating = $like['average'];
			$old_count = $like['times'];
			$new_rating = ($old_rating * $old_count + $rating) / ($old_count+1);
			$new_count = $old_count+1;
			
			$new_ratings[$i] = $new_rating;
			$new_counts[$i] = $new_count;
		}
		
		for ($i = 0; $i < count($ing_list); $i++) {
			$query =
			"INSERT INTO Likes(user_id, ingredient, average, times)
			VALUES ({$_SESSION['user_id']}, '{$ing_list[$i]}', {$new_ratings[$i]}, {$new_counts[$i]})
			ON DUPLICATE KEY UPDATE
			average={$new_ratings[$i]}, times={$new_counts[$i]}";
			
			milf_query($query, $connection);
		}
		
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
	
			$query2 = "SELECT ingredient FROM Ingredients WHERE meal_id={$row['id']}";
			$result2 = milf_query($query2, $connection);
			$ing_list = array();
			while ($i = mysql_fetch_array($result2))
			$ing_list[] = $i[0];				
	
			echo "<div align='center'>";
			echo "<h1><font color ='darkblue'>{$row['title']} ($rating)</font></h1>"; 
			echo "</div>";
			
			echo "<table align='center'><tr><td>";
			echo "<div align='center' id='main'>";
			echo "<div id='picture'>";
			echo "<img src='{$row['img']}' />"; 
			echo "</div></td>";
			/* Google API Table
			echo "<div id='mealTable'></div>";
			echo "<script type='text/javascript'>";
			echo "var ingredients = '" . implode(", ", explode("|", $row['ingredients'])) . "';";
			echo "drawMealTable(ingredients);";
			echo "</script>";
			*/
			
			echo "<td>";
			echo "<div id='info' style='width:1000px'>";
			echo "
			
			<div id='accordion' class='ui-accordion'>
			<h3><a href='#'>Ingredients</a></h3>
			<div>
			<p>" .
			implode(', ', $ing_list) . 
			"</p>
			</div>
			<h3><a href='#'>Description</a></h3>
			<div>
			<p>";
			if ($row['description'] != '')
				echo $row['description'];
			else
				echo "No description.";
			echo "</p>
			</div>
			<h3><a href='#'>Requirements</a></h3>
			<div>";
			foreach (explode("|", $row['requirements']) as $i)
				echo "$i<br/>";		 
			echo "</p>
			</div>
			<h3><a href='#'>Cuisine/Calories</a></h3>
			<div>
			<p> {$row['cuisine']}, {$row['calories']}
			</p>
			</div>
			<h3><a href='#'>Preparation</a></h3>
			<div>
			<p>";
			foreach (explode("|", $row['preparation']) as $i)
				echo "<p>$i</p>";				
			echo "</p>
			</div>
			</div>
			
			";
			echo "</div>"; // End info div
			echo "</td></tr></table>";
			echo "</div>"; // End main div
			
			echo "<br /><br />";
			echo "<div align='center'>";	
			if (isset($_SESSION['username'])) {
				$connection = milf_connect();
				$query = "SELECT * FROM Ate where user_id={$_SESSION['user_id']} AND meal_id=$id";
				$result = milf_query($query, $connection);
				
				$row = mysql_fetch_assoc($result);
				
				if ($row) {
					$rating = $row['rating'];
					echo "You rated this meal at: $rating";
					echo "<form action='{$_SERVER['REQUEST_URI']}' method='post' name='rating'>";
					echo "<input type='hidden' name='delete_rating' value='true' />";
					echo "<input type='hidden' name='delete_rating_value' value='{$row['rating']}' /></form>";
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
				echo "Posted by: <a href='user.php?id={$row['user_id']}'>{$row['username']}</a> ";
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