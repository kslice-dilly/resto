<?php
// db_cred.php defines $username, $password and $database
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

// SQL query to get business name, address, co-ordinates and last inspection date.
$query = "SELECT DISTINCT(business.name), geocode.addr, geocode.lat, geocode.`long`, inspections.score AS rating, " .
	"CONCAT(SUBSTRING(MAX(inspections.datetext), 1, 4), '-', " .
               "SUBSTRING(MAX(inspections.datetext), 5, 2), '-', " .
               "SUBSTRING(MAX(inspections.datetext), 7)) AS datetext " .
	"FROM geocode " .
	"JOIN (business, inspections) " .
	"ON (geocode.bus_guid = business.guid AND inspections.bus_guid = business.guid) " .
	"WHERE geocode.lat != 0 " .
        "GROUP BY business.guid ";
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
  $inner_query = "SELECT description, violations.datetext FROM violations JOIN (business, inspections) ON (inspections.bus_guid = business.guid AND violations.bus_guid = business.guid AND violations.datetext = inspections.datetext) WHERE violations.description != '' AND business.name= '" . utf8_encode($row['name']) . "' ORDER BY inspections.datetext";
  $inner_result = mysql_query($inner_query);
  if ($inner_result === FALSE) {
      $inner_query = '';
      // do nothing with no violations
  } else if (mysql_num_rows($inner_result) > 0) { // this line throws a warning "expects parameter 1 to be resource, boolean given) @todo fix
    while ($inner_row = @mysql_fetch_assoc($inner_result)) {
      $vnode = $doc->createElement("violation");
      $newnode = $node->appendChild($vnode);
      $newnode->setAttribute("desc", utf8_encode($inner_row['description']));
      $newnode->setAttribute("date", (substr($inner_row['datetext'],0,4) . '-' . substr($inner_row['datetext'],4,2) . '-' . substr($inner_row['datetext'],6)));
    }
  }
}
;
// output XML
echo $doc->saveXML();
// Records visitors to site
require("record_visitor.php");
?>
