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
	echo "<form action='advanced.php' method='get'>";
	echo "Ingredients (e.g. 'chicken, nuts'):";
	echo "<input name='ingredients' type='text' size='40' value='$search' />";
	echo "<button type='submit'>Search</button><BR /><BR />";
	echo "Cuisine: ";
	echo "<input name='cuisine' type='text' size='10' /> &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp\n";
	echo "Calorie min: ";
	echo "<input name='cal_min' type='text' size='4' /> &nbsp&nbsp&nbsp&nbsp\n";
	echo "Calorie max: ";
	echo "<input name='cal_max' type='text' size='4' /><BR />\n";
	echo "</form>\n";
	echo "<BR />\n";
	echo "</div>\n";
	
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
			
			echo "<div id='results' style='margin-left:25px'>";
			echo "Results  " . ($first+1) . ".." . ($curr_last+1) . " out of $total <br />";		
			
			$get_args = "ingredients=$search";
			if ($page != 1) {
				echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=1'><button>First</button></a>";
				echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=" . ($page-1) ."'><button>Prev</button></a>";
			}
			
			if ($page != $last_page) {
				echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=" . ($page+1) ."'><button>Next</button></a>";
				echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=$last_page'><button>Last</button></a>";
			}
			
			$search_tokens = preg_split("/, */", $search);
			$search_tuple = "('" . implode("', '", $search_tokens) . "')";
			
			$cal_min = 0;
			$cal_max = PHP_INT_MAX;
			$cuisine = '';
			
			if(isset($_GET['cal_min']) && strlen($_GET['cal_min']) > 0)
				$cal_min = $_GET['cal_min'];
			if(isset($_GET['cal_max']) && strlen($_GET['cal_max']) > 0)
				$cal_max = $_GET['cal_max'];
			if(isset($_GET['cuisine']) && strlen($_GET['cuisine']) > 0)
				$cuisine = "AND cuisine='{$_GET['cuisine']}'";
			
			$view_query = 
			"CREATE OR REPLACE VIEW IngredientMatches AS
			SELECT meal_id AS id, count(*) AS matches
			FROM Ingredients
			WHERE ingredient IN $search_tuple
			GROUP BY id
			ORDER BY matches DESC
			LIMIT $first, $limit";
			
			$query = 
			"SELECT Meal.id, title, description, img, ingredients, AVG(rating) as rating, matches
			FROM Meal LEFT JOIN Ate ON Meal.id=Ate.meal_id, IngredientMatches
			WHERE Meal.id IN (SELECT id FROM IngredientMatches)
			AND calories BETWEEN $cal_min AND $cal_max
			$cuisine
			GROUP BY Meal.id";
			
			$destroy_view = 
			"DROP VIEW IngredientMatches";
			
			milf_query($view_query, $connection);
			$result = milf_query($query, $connection);
			milf_query($destroy_view, $connection);
			
			echo mysql_num_rows($result);
			
			if (!mysql_num_rows($result)) {
				echo "<h2>No Results</h2>";	
			} else {
				echo "<br /><br />";
				while ($meal = mysql_fetch_assoc($result)) {
					$rating = "unrated";
					if ($meal['rating'] != 0)
						$rating = number_format($meal['rating'], 2);
					
					printf("<img src='%s' width='100' height='100'><br />", $meal['img']);
					printf("<a href='meal.php?id=%d'><b><font size='4'>%s</font></b></a><br />", 
						   $meal['id'], $meal['title']);
					if ($meal['description'] != '')
						printf("<i>%s</i><br />", $meal['description']);
					if ($meal['ingredients'] != '')
						printf("%s<br />", implode(", ", explode("|",$meal['ingredients'])));
					printf("Rating: %s<br />", $rating);
					printf("Matches: %s<br /><br />", $meal['matches']);
				}
				
				if ($page != 1) {
					echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=1'><button>First</button></a>";
					echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=" . ($page-1) ."'><button>Prev</button></a>";
				}
				
				if ($page != $last_page) {
					echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=" . ($page+1) ."'><button>Next</button></a>";
					echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=$last_page'><button>Last</button></a>";
				}
				
				echo "<br />";
				echo "Results  " . ($first+1) . ".." . ($curr_last+1) . " out of $total <br /><br />";
				echo "</div>";
			}
		}
	}
	mysql_close($connection);
	require_once("footer.php");
	?>