<?php
	function milf_connect() {
		$connection = mysql_connect(
			"projects.cs.illinois.edu",
			"nikitin2_mysql",
			"abcd1234");
			
		if (!$connection)
			die("Unable to select database: " . mysql_error());
		
		mysql_select_db("nikitin2_milf3", $connection) or die("Unable to select database: " . mysql_error());
		
		return $connection;
	}
	
	function milf_query($query, $connection) {
		$result = mysql_query($query, $connection);
		if (!$result) {
			die("Database access failed: " . mysql_error());
		}
		return $result;
	}
?>