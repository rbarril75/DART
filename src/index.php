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
	$cal_min_input = "";
	$cal_max_input = "";
	$cuisine_input = "";
	
	if (isset($_GET['ingredients']))
		$search = $_GET['ingredients'];
	if(isset($_GET['cal_min']))
		$cal_min_input = $_GET['cal_min'];
	if(isset($_GET['cal_max']))
		$cal_max_input = $_GET['cal_max'];
	if(isset($_GET['cuisine']))
		$cuisine_input = $_GET['cuisine'];
	
	echo "<div align='center'>";
	echo "<h1><font face='Fantasy'>Multitudinous Information on Luscious Foodstuffs</font></h1>";
	echo "<form action='index.php' method='get'>";
	echo "Ingredients (e.g. 'chicken, nuts'):";
	echo "<input name='ingredients' type='text' size='40' value='$search' />";
	echo "<button type='submit'>Search</button><BR /><BR />";
	echo "Cuisine: ";
	echo "<input name='cuisine' type='text' size='10' value='$cuisine_input'/> &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp\n";
	echo "Calorie min: ";
	echo "<input name='cal_min' type='text' size='4' value='$cal_min_input'/> &nbsp&nbsp&nbsp&nbsp\n";
	echo "Calorie max: ";
	echo "<input name='cal_max' type='text' size='4' value='$cal_max_input'/><BR />\n";
	echo "</form>\n";
	echo "<BR />\n";
	echo "</div>\n";
	
	if ($search != "" || $cal_min_input != "" || $cal_max_input != "" || $cuisine_input != "") {		
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
			
			$cal_min = 0;
			$cal_max = PHP_INT_MAX;
			$cuisine = '';
			
			if (strlen($cal_min_input) > 0)
				$cal_min = $cal_min_input;
			if (strlen($cal_max_input) > 0)
				$cal_max = $cal_max_input;
			if (strlen($cuisine_input) > 0)
				$cuisine = "AND cuisine='$cuisine_input'";
				
			echo "<div id='results' style='margin-left:25px'>";
			echo "Results  " . ($first+1) . ".." . ($curr_last+1) . " out of $total <br />";		
			
			$get_args = "ingredients=$search&cal_min=$cal_min_input&cal_max=$cal_max_input&cuisine=$cuisine_input";
			if ($page != 1) {
				echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=1'><button>First</button></a>";
				echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=" . ($page-1) ."'><button>Prev</button></a>";
			} else {
				echo "<button disabled='disabled'>First</button>";
				echo "<button disabled='disabled'>Prev</button>";
			}
			
			if ($page != $last_page) {
				echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=" . ($page+1) ."'><button>Next</button></a>";
				echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=$last_page'><button>Last</button></a>";
			} else {
				echo "<button disabled='disabled'>Next</button>";
				echo "<button disabled='disabled'>Last</button>";
			}
				

			
			if (strlen($search) > 0) {
				$search_tokens = preg_split("/, */", $search);
				$search_tuple = "('" . implode("', '", $search_tokens) . "')";
				
				$which_table = 'Ingredients';				
				
				if (isset($_SESSION['user_id'])) {
					$weights = array_fill(0, count($search_tokens), 2);
					$query = "SELECT * FROM Likes WHERE user_id={$_SESSION['user_id']} AND ingredient IN $search_tuple";
					$result = milf_query($query, $connection);
					while ($row = mysql_fetch_assoc($result)) {
						$i = array_search($row['ingredient'], $search_tokens);
						if ($row['times'] > 0)
							$weights[$i] = $row['average'];
					}
						
					//for ($i = 0; $i < count($search_tokens); $i++)
					//	echo "<br/>{$search_tokens[$i]} : {$weights[$i]}";
					
					$query =
					"CREATE OR REPLACE VIEW Repeated AS
					SELECT * FROM Ingredients WHERE ingredient='{$search_tokens[0]}'";
					$weights[0]--;
					
					for ($i = 0; $i < count($weights); $i++)
						for ($j = 0; $j < $weights[$i]; $j++)
							$query .= " UNION ALL SELECT * FROM Ingredients WHERE ingredient='{$search_tokens[$i]}'";
	
					//echo "QUERY: <br/> $query";	
	
					milf_query($query, $connection);
					$which_table = "Repeated";
				}
										
				$view_query = 
				"CREATE OR REPLACE VIEW IngredientMatches AS
				SELECT meal_id AS id, count(*) AS matches
				FROM $which_table 
				WHERE ingredient IN $search_tuple
				GROUP BY id
				ORDER BY matches DESC
				";
				
				milf_query($view_query, $connection);
				
				//echo "<br/>$view_query<br/>";				
				
				$view_query = 
				"CREATE OR REPLACE VIEW Filtered AS
				SELECT Meal.id, title, description, img, ingredients, matches, calories, cuisine FROM Meal, IngredientMatches
				WHERE Meal.id = IngredientMatches.id AND
				calories BETWEEN $cal_min AND $cal_max
				$cuisine
				LIMIT $first, $limit";
				
				//echo "<br/>$view_query<br/>";	
				
				milf_query($view_query, $connection);
				
				$query = 
				"SELECT Filtered.id, title, description, img, ingredients, AVG(rating) as rating, matches
				FROM Filtered LEFT JOIN Ate ON Filtered.id=Ate.meal_id
				GROUP BY Filtered.id
				ORDER BY matches DESC";

				//echo "<br/>$query<br/>";				
									
				$result = milf_query($query, $connection);
			}
			else {
				$query = 
				"SELECT Meal.id, title, description, img, ingredients, AVG(rating) as rating
				FROM Meal LEFT JOIN Ate ON Meal.id=Ate.meal_id
				WHERE calories BETWEEN $cal_min AND $cal_max
				$cuisine
				GROUP BY Meal.id
				LIMIT $first, $limit";				
				
				$result = milf_query($query, $connection);
			}
			
			if (!mysql_num_rows($result)) {
				echo "<h2>No Results</h2>";	
			} else {
				echo "<br /><br />";
				while ($meal = mysql_fetch_assoc($result)) {
					$rating = "unrated";
					if ($meal['rating'] != 0)
						$rating = number_format($meal['rating'], 2);
					
					$query2 = "SELECT ingredient FROM Ingredients WHERE meal_id={$meal['id']}";
					$result2 = milf_query($query2, $connection);
					$ing_list = array();
					while ($i = mysql_fetch_array($result2))
						$ing_list[] = $i[0];					
					
					echo "<div id='result' style='clear:left; height:85px'>";
					printf("<img src='%s' width='100' height='100' style='float:left'>", $meal['img']);
					echo "<div id='resultInfo' style='margin-left:110px'>";
					printf("<a href='meal.php?id=%d'><b><font size='4'>%s</font></b></a><br />", 
						   $meal['id'], $meal['title']);
					if ($meal['description'] != '')
							printf("<i>%s%s</i><br />", substr($meal['description'],0,150), (strlen($meal['description'])>149) ? '...' : '');
					if ($meal['ingredients'] != '') {
						printf("<b>Ingredients:</b> ");
						printf("%s<br />", implode(", ", $ing_list));
						}
					printf("<b>Rating:</b> %s<br />", $rating);
					if (strlen($search) > 0)
						printf("<b>Score:</b> %s", $meal['matches']);
					echo "</div>";
					echo "</div>";
					echo "<br /><br/>";
				}
								
				if ($page != 1) {
					echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=1'><button>First</button></a>";
					echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=" . ($page-1) ."'><button>Prev</button></a>";
				} else {
					echo "<button disabled='disabled'>First</button>";
					echo "<button disabled='disabled'>Prev</button>";
				}
				
				if ($page != $last_page) {
					echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=" . ($page+1) ."'><button>Next</button></a>";
					echo "<a href='{$_SERVER['PHP_SELF']}?{$get_args}&page=$last_page'><button>Last</button></a>";
				} else {
					echo "<button disabled='disabled'>Next</button>";
					echo "<button disabled='disabled'>Last</button>";
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