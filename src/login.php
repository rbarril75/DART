<?php
	require_once("db.php");

	$return_url = "index.php";
	if (isset($_GET['return']))
		$return_url = $_GET['return'];
	header("location:$return_url");

	if (!isset($_POST['username'])) {
		header("location:$return_url");
	} else {
		$username = $_POST['username'];
		$password = $_POST['password'];
		
		$connection = milf_connect();
	
		$query = "SELECT * FROM User WHERE username='$username' AND password='$password'";
		$result = milf_query($query, $connection);
		mysql_close($connection);	
		
		if (!$result) {
			die("Database access failed: " . mysql_error());
		} elseif (mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
	
			session_start();
			$_SESSION['username'] = $row['username'];
			$_SESSION['user_id'] = $row['id'];
			
			header("location:$return_url");
		} else {
			header("location:$return_url");
		}
	}
?>