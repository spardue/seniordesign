<?php
/*
   downloads a file where $_GET["id"] exists in the Document table
   insures the correct document type is outputed
*/
require("headlessheader.php");

$stmt = $db->interact("SELECT * FROM Document WHERE ID = ?", array($_GET["id"]));
$file = $stmt->fetch(PDO::FETCH_ASSOC);

$size = $file["size"];
$type = $file["Type"];
$name = $file["Name"];

header("Content-length: $size");
header("Content-type: $type");
header("Content-Disposition: attachment; filename=$name");
echo $file["Data"];


?>

