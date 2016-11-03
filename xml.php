<?php
require("db_cred.php");

// Start XML file; create parent node
$doc = new DOMDocument("1.0", "utf-8");
$node = $doc->createElement("markers");
$parnode = $doc->appendChild($node);

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

// Select all the rows in the markers table
$query = "SELECT DISTINCT(business.name), geocode.addr, geocode.lat, geocode.`long`, inspections.score AS rating, inspections.datetext " .
	"FROM geocode " .
	"JOIN (business, inspections) " .
	"ON (geocode.bus_guid = business.guid AND inspections.bus_guid = business.guid) " .
	"GROUP BY business.guid " .
	"ORDER BY inspections.datetext";
$result = mysql_query($query);
if (!$result) {
  die('Invalid query: ' . mysql_error());
}

header("Content-type: text/xml");

// Iterate through the rows, adding XML nodes for each
while ($row = @mysql_fetch_assoc($result)){
  // ADD TO XML DOCUMENT NODE
  $node = $doc->createElement("marker");
  $newnode = $parnode->appendChild($node);
  $newnode->setAttribute("name", utf8_encode($row['name']));
  $newnode->setAttribute("address", $row['addr']);
  $newnode->setAttribute("lat", $row['lat']);
  $newnode->setAttribute("lng", $row['long']);
  $newnode->setAttribute("rating", $row['rating']);
}

echo $doc->saveXML();
?>
