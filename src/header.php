<?php
	session_start();
?>
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="style.css" />
	<link rel="stylesheet" type="text/css" href="jquery-ui-1.8.16.custom.css" />
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" /> 
    
    <script type='text/javascript'>
      google.load('visualization', '1', {packages:['table']});
      //google.setOnLoadCallback(drawMealTable);
      
      function drawMealTable(ingredients) {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Ingredients');
        data.addRows(1);
		data.setCell(0, 0, ingredients, null, {style:'text-align:center;'});
	
        var table = new google.visualization.Table(document.getElementById('mealTable'));
        table.draw(data, {sort: 'disable', width:600, allowHtml:true});
      }

	$(document).ready(function() {
		$("#accordion").accordion({autoHeight: false, collapsible: true, active: false});
	});
    </script>

	<style>
		table {border-collapse: collapse;}
	</style>
</head>
<body>