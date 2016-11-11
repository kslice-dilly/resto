<style>
table.dbtable {
	border-right:1px solid #ccc; border-bottom:1px solid #ccc;
}
table.dbtable th {
	background: #eee; padding:5px; border-left:1px solid #ccc; border-top:1px solid #ccc;
}
table.dbtable td {
	padding:5px; border-left:1px solid #ccc; border-top:1px solid #ccc;
}
</style>
<?php

require("db_cred.php");
// db_cred.php defines $username, $password and $database

// Open a connection to MySQL server
$con = mysql_connect('localhost', $username, $password);
if (!$con) {
	die('Not connected : ' . mysql_error());
}

// Set the active DB
$db_selected = mysql_select_db($database, $con);
if (!$db_selected) {
	die('Can\'t use db : ' . mysql_error());
}

// SQL query to update visitor info
$query = "SELECT hostname,INET_NTOA(remote_addr),referer,firstvisit,lastvisit,`count`,user_agent FROM visits";
$result = mysql_query($query);
if (!$result) {
	die('Invalid query : ' . mysql_error());
}

echo '<h3>Unique Visitors by IP</h3>';
echo '<table class=dbtable cellpadding=1 cellspacing=2>';
echo '<tr><th>Hostname</th><th>Address</th><th>Referer</th><th>First Visit</th>';
echo '<th>Last Visit</th><th>Count</th><th>User Agent</th></tr>';
while ($row = mysql_fetch_row($result)) {
	echo '<tr>';
	echo '<td>' . $row[0] . '</td>';
	echo '<td>' . $row[1] . '</td>';
	echo '<td>' . $row[2] . '</td>';
	echo '<td>' . $row[3] . '</td>';
	echo '<td>' . $row[4] . '</td>';
	echo '<td>' . $row[5] . '</td>';
	echo '<td>' . $row[6] . '</td>';
	echo '</tr>';
}
echo '</table><br />';

?>
