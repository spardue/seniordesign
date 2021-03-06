<?php
include_once("config.php");


/**
 * Replaces any $tag in the input file with corresponding data from the database and returns a string of the filled input file.
 * $tag to data mapping is determined by the Mappings table.
 * If no entry for a $tag exists in the table, that $tag is ignored. It will remain in the output file as "$tag". A line
 *  will be echoed indicating such.
 * FILES DON'T HAVE TO BE FDF FILES. RTF, ETC WILL WORK.
 *
 * $inputfile - Any file with $tags. Not altered.
 * $personid - Corresponds to 'PersonID' in most tables, 'ClaimantID' in the 'Case' table, or 'ID' in the 'Person' table
 * $db - $db object from header for db interaction.
 */
function fillTemplate($filled, $caseID, $db)
{
    /*
    // Pull fdf text into a string
    if ($preFilled !== null) {
        $myfile = fopen($inputfile, "r") or die("Unable to open file!");
        $filetext = fread($myfile, filesize($inputfile));
        fclose($myfile);
    } else {
        $filetext = $preFilled;
    }
    */
    $filetext = $filled;

    $personid = $db->get1("SELECT * FROM `Case` WHERE ID = ?", array($caseID))["ClaimantID"];


    // Extract $tags from fdf
    preg_match_all('/\$\w*/', $filetext, $matches, PREG_OFFSET_CAPTURE, 3);
    $metadata = array();

    // Simplify array format
    foreach ($matches[0] as $val) {
        array_push($metadata, $val[0]);
    }

    $dataToInsert = array();
    // Pull appropriate data from the db based on the each $tag's mapping in the Mappings table
    foreach ($metadata as $tag) {
        $mapping = $db->interact("SELECT * FROM `Mappings` WHERE `Tag` = :tag", array('tag' => $tag))->fetch();

        if (count($mapping) == 1) { // Indicates a tag wasn't returned
            //echo "\nMetadata tag '".$tag."' does not exist! It will be not be replaced in or removed from the generated file.\n";
        } else {
            if ($mapping['Direct'] != "") { // There is direct text data
                $dataToInsert[$tag] = $mapping['Direct'];
            } else { // There is table and column data
                // Lookup by ID if Person table or by PersonID otherwise
                if ($mapping['MTable'] == "Person") {
                    $idname = "ID";
                } else {
                    $idname = "PersonID";
                }
                $info = $db->interact("SELECT `" . $mapping['MCol'] . "` FROM `" . $mapping['MTable'] . "` WHERE `" . $idname . "` = :id", array('id' => $personid))->fetch();
//                echo "\n".$tag."  ".$info[$mapping['MCol']]."\n"; // debug print
                $dataToInsert[$tag] = $info[$mapping['MCol']];
            }
        }
    }

    // Replace the tags with data in the fdf text
    foreach ($dataToInsert as $tag => $data) {
//        echo $tag." => ".$data."\n"; // debug print
        $filetext = str_replace($tag, $data, $filetext);
    }

    return $filetext;
}

/*
   Fills a rtf file using the metadata filling system
   outputs the RTF file for downloading
*/
function fillRtf($rtfTemplatePath, $name, $caseID, $db)
{
    $filled = file_get_contents($rtfTemplatePath);
    $filled = fillTemplate($filled, $caseID, $db);
    header("Content-length: " . strlen($filled));
    header("Content-type: text/plain");
    header("Content-Disposition: attachment; filename=$name");
    echo $filled;
}

/*
   Fills a pdf file using the metadata filling system, but with the $customReplace function if given
   also outputs the pdf file for downloading
*/
function fillPdf($pdfPath, $fdfPath, $name, $caseID, $db, $customReplace = null)
{
    $filled = file_get_contents($fdfPath);
    if ($customReplace != null) {
        $filled = $customReplace($filled);
    }
    $filled = fillTemplate($filled, $caseID, $db);
    $tmpFdf = tempnam(null, "va");
    $tmpPdf = tempnam(null, "va");
    file_put_contents($tmpFdf, $filled);
    exec("pdftk $pdfPath fill_form $tmpFdf output $tmpPdf");

    $generatedPdf = file_get_contents($tmpPdf, "rb");

    $length = strlen($generatedPdf);
    header("Content-length: " . strlen($generatedPdf));

    header("Content-type: application/pdf");
    header("Content-Disposition: attachment; filename=$name");
    echo $generatedPdf;
}


?>
