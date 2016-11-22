<?php
// db_cred.php defines $username, $password and $database
require("db_cred_rw.php");

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
$query = "INSERT INTO visits (`user_agent`, `referer`, `remote_addr`, `firstvisit`, `lastvisit`, `hostname`) " .
	"VALUES ('" . $_SERVER['HTTP_USER_AGENT'] . "','" . $_SERVER['HTTP_REFERER'] . 
	"',INET_ATON('" . $_SERVER['REMOTE_ADDR'] . "'),NOW(),NOW(),'" . gethostbyaddr($_SERVER['REMOTE_ADDR']) . 
	"') ON DUPLICATE KEY UPDATE `count` = `count` + 1, `lastvisit` = NOW()";
$result = mysql_query($query);
if (!$result) {
	die('Invalid query : ' . mysql_error());
}

?>
