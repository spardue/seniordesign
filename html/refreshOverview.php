<?php
require('headlessheader.php');

// Retrieve the claimant information from the database
$query = $db->interact("SELECT * FROM `Person` WHERE `ID` = :id",
    array('id' => $_GET["id"]));
$claimant = $query->fetch();

// We will format and return only the claimant's contact information
$values = array(
    "Name" => $claimant["LastName"] . ", " . $claimant["FirstName"],
    "Address" => $claimant["Address"] . ", " . $claimant["City"] . ", " . $claimant["State"] . " " . $claimant["ZIP"],
    "MailingAddress" => $claimant["MailingAddress"] . ", " . $claimant["MailingCity"] . ", " . $claimant["MailingState"] . " " . $claimant["MailingZIP"],
    "HomePhone" => $claimant["HomePhone"],
    "BusinessPhone" => $claimant["BusinessPhone"],
    "CellPhone" => $claimant["CellPhone"],
    "Fax" => $claimant["Fax"],
    "Email" => $claimant["Email"]
);

// Convert the array of claimant contact information into an object
$object = new stdClass();
foreach ($values as $key => $value) {
    $object->$key = $value;
}

// Encode the object that holds the claimant contact information in JSON format
// and echo it for later use
echo json_encode($object);

?>
