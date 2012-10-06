<?php
	$error = false;
	
	require_once("db.php");

	if (isset($_POST['new_username'])) {
		$connection = milf_connect();
				
		$uname=$_POST['new_username'];
		$passwd=$_POST['new_password'];
		
		$query = "SELECT * FROM User WHERE username='$uname'";		
		$result = milf_query($query, $connection);
		
		if (mysql_num_rows($result)) {
			$error = true;
		} else {
			$query = "INSERT INTO User(username, password) VALUES('$uname', '$passwd')";			
			$result = milf_query($query, $connection);
			
			session_start();
			$_SESSION['username'] = $uname;
			$_SESSION['user_id'] = mysql_insert_id();

			mysql_close($connection);
			header("location:index.php");	
		}
		mysql_close($connection);
	}	
	require_once("header.php");
	echo "<br />";
	echo "<a href='index.php'><button class='mn-button ui-button-text'>Back Home</button></a>";
	if ($error) {
		echo "That username is already in use";
	}
	echo "<h1>Registration Page</h1>";

	echo "<form action='register.php' method='post'>";
	echo "Username: <input type='text' size='20' name='new_username' /><br />";
	echo "Password: <input type='password' size='20' name='new_password' /><br />";
	echo "<input type='submit' value='Register' />";
	echo "</form>";
	echo "<br \>";
	
	require_once("footer.php");	
?>