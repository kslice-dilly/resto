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

$query = "SELECT DISTINCT(business.name), geocode.addr, geocode.lat, geocode.`long`, inspections.score AS rating, " .
	"CONCAT(SUBSTRING(inspections.datetext, 1, 4), '-', SUBSTRING(inspections.datetext, 5, 2), '-', SUBSTRING(inspections.datetext, 7)) AS datetext " .
	"FROM geocode " .
	"JOIN (business, inspections) " .
	"ON (geocode.bus_guid = business.guid AND inspections.bus_guid = business.guid) " .
	"WHERE geocode.lat != 0 " .
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
  $newnode->setAttribute("last_inspection", $row['datetext']);
// @todo refactor this so that the guid is used in the query  
  $inner_query = "SELECT description FROM violations JOIN (business, inspections) ON (inspections.bus_guid = business.guid AND violations.bus_guid = business.guid) WHERE violations.description != '' AND business.name= '" . utf8_encode($row['name']) . "' GROUP BY business.guid ORDER BY inspections.datetext";
  $inner_result = mysql_query($inner_query);
  if (mysql_num_rows($inner_result) > 0) {
    $node = $doc->createElement("violation");
    $newnode = $newnode->appendChild($node);
    while ($inner_row = @mysql_fetch_assoc($inner_result)) {
      $newnode->setAttribute("desc", utf8_encode($inner_row['description']));
    }
  }
}
;

echo $doc->saveXML();
?>
