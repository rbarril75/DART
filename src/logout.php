<?php
	session_start();
	session_destroy();
	$return_url = "index.php";
	if (isset($_GET['return']))
		$return_url = $_GET['return'];
	header("location:$return_url");
?>