<link rel="stylesheet" type="text/css" href="default.css">
<style>
body, html {
	width: 100%;
	height: 4%;
	margin: 5;
	padding: 5;
}
</style>
<?php
// db_cred.php defines $username, $password and $database
require("db_cred.php");

// Opens a connection to MySQL server
$con = mysql_connect ('localhost', $username, $password);
if (!$con) {
  die('Not connected : ' . mysql_error());
}

// Set the active MySQL database
$db_selected = mysql_select_db($database, $con);
if (!$db_selected) {
	die ('Can\'t use db : ' . mysql_error());
}

// SQL query to get business name, address, co-ordinates and last inspection date.
$query = "SELECT distinct(substring(datetext,1,4)) AS Year FROM `violations` ORDER BY Year";
$result = mysql_query($query);
if (!$result) {
	die('Invalid query: ' . mysql_error());
}

echo "<DIV class=\"filter\">";

// Create Year filter
echo "<DIV class=\"select-block\">";
echo "<SELECT name=\"year\">";
echo "<OPTION value=\"-1\">Year</OPTION>";
while ($row = @mysql_fetch_assoc($result)) {
	echo "<OPTION value=\"" . $row['Year']  . "\">";
	echo $row['Year'];
	echo "</OPTION>";
}

// Create Apply Filter button
echo "</SELECT><BUTTON type=\"button\">Apply Filter</BUTTON>";
echo "</DIV></DIV>";
?>
