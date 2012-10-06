<?php
	require_once("header.php");
	require_once("db.php");
	$connection = milf_connect();	
	
	if (isset($_GET['action']) && $_GET['action'] == 'wrongusername') {
			echo 'Wrong username/password <br />';
	} elseif (isset($_GET['action']) && $_GET['action'] == 'registered') {
			echo 'Thank you for registering!';
			echo $_SESSION['username'];
			echo $_SESSION['user_id'];
	}
	require_once("user_plate.php");
	
	$search = "";
	if (isset($_GET['ingredients']))
		$search = $_GET['ingredients'];
		
	echo "<div align='center'>";
	echo "<h1><font face='Fantasy'>Multitudinous Information on Luscious Foodstuffs</font></h1>";
	echo "<form action='index.php' method='get'>";
	echo "ingredients (e.g. 'chicken, nuts'):";
	echo "<input name='ingredients' type='text' size='40' value='$search' />";
	echo "<input type='submit' /></form>";
	echo "<BR \>";
	echo "</div>";
	
	if ($search != "") {		
		$query = "SELECT COUNT(*) FROM Meal";
		$result = milf_query($query, $connection);
		$row = mysql_fetch_row($result);

		$total = $row[0];	
		
		if ($total == 0) {
			echo "<h2>No Results</h2>";
		} else {				
			$limit = 10;		
			$last_page = intval(($total + $limit - 1) / $limit);
			$page = 1;
			if (isset($_GET['page'])) {
				$page = $_GET['page'];
			}
			if ($page < 1)
				$page = 1;
			if ($page > $last_page)
				$page = $last_page;
	
			$first = ($page - 1) * $limit;
			$curr_last = $first+$limit-1;
			if ($curr_last >= $total)
				$curr_last = $total - 1 ;
			
			echo "Results  " . ($first+1) . ".." . ($curr_last+1) . " out of $total <br />";		
			
			$get_args = "ingredients=$search";
			if ($page != 1) {
				echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=1'>First   </a>";
				echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=" . ($page-1) ."'>Prev   </a>";
			} else {
				echo "First   ";
				echo "Prev   ";
			}
			if ($page != $last_page) {
				echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=" . ($page+1) ."'>Next   </a>";
				echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=$last_page'>Last</a>";
			} else {
				echo "Next   ";
				echo "Last   ";
			}
				
			$query = 
				"SELECT id, title, description, img, ingredients, AVG(rating) as rating FROM Meal LEFT JOIN Ate
				 ON Meal.id=Ate.meal_id
				 GROUP BY id
				 LIMIT $first, $limit";
			
			$result = milf_query($query, $connection);
			
			if (!mysql_num_rows($result)) {
				echo "<h2>No Results</h2>";	
			} else {	
				echo "<table border='1'>";
				echo "<th>Icon</th>";
				echo "<th>Title</th>";
				echo "<th>Description</th>";
				echo "<th>Ingredients</th>";
				echo "<th>Rating</th>";
				
				while ($meal = mysql_fetch_assoc($result)) {
					$rating = "unrated";
					if ($meal['rating'] != 0)
						$rating = number_format($meal['rating'], 2);
					print("<tr>");
					printf("<td><img src='%s' width='100' height='100'></td>", $meal['img']);
					printf("<td><a href='meal.php?id=%d'>%s</a></td>", $meal['id'], $meal['title']);
					printf("<td>%s</td>", $meal['description']);
					printf("<td>%s</td>", implode(", ", explode("|",$meal['ingredients'])));
					printf("<td>%s</td>", $rating);
					print("</tr>");
				}
				echo "</table>";
			}
		}
	}
	mysql_close($connection);
	require_once("footer.php");
?>