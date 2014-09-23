<?php
/*
   Fills a document $_GET["formID"]
*/
include_once("headlessheader.php");
include_once("../fillTemplate.php");


switch ($_GET["formID"]) {
    case "va_client_attorney_agreement":
        fillRtf("../formTemplates/rtf/VA_CLIENT_ATTORNEY_AGREEMENT.rtf",
            "VA_CLIENT_ATTORNEY_AGREEMENT.rtf",
            $_GET["caseID"],
            $db
        );
        break;
    case "21-22a":
        fillPdf("../formTemplates/pdf/vba-21-22a.pdf",
            "../formTemplates/fdf/vba-21-22a.fdf",
            "21-22a.pdf",
            $_GET["caseID"],
            $db,
            function ($filled) use ($db) { //custom code to properly fill 21-22a
                $case = $db->get1("SELECT * FROM `Case` WHERE ID = ?", array($_GET["caseID"]));
                $personID = $case["ClaimantID"];
                $person = $db->get1("SELECT * FROM `Person` WHERE ID =?", array($personID));
                if ($person["IsVeteran"] == 1) {
                    $veteranName = $person["LastName"] . " - " . $person["FirstName"] . " - " . $person["MiddleName"];
                } else {
                    $veteranName = $person["VeteranName"];
                }
                $filled = str_replace('$veteranName', $veteranName, $filled);
                $bos = $db->get1("SELECT * FROM `MilitaryService` WHERE `PersonID` = ?", array($personID));

                $bos = $bos["BranchOfService"];
                $key = null;
                if (stripos($bos, "army") !== false){
                    $key = 'bos-army';
                }
                else if (stripos($bos, "navy") !== false) {
                    $key = 'bos-navy';
                }
                else if (stripos($bos, "marine") !== false) {
                    $key = 'bos-marine';
                }
                else if (stripos($bos, "coast") !== false) {
                    $key = 'bos-coast';
                }
                else if (stripos($bos, "air") !== false) {
                    $key = 'bos-air';
                } else {
                    $key = 'bos-other';
                }
                if ($key === 'bos-other') {
                    $filled = str_replace('$otherBOS', $bos,  $filled); 
                } else {
                    $filled = str_replace('$otherBOS', "", $filled); 
                }
                $filled = str_replace($key, "1", $filled);
                $filled = preg_replace("/bos-[a-z]+/", "", $filled);

                $q = $db->interact("SELECT ServiceNumber FROM `MilitaryService` WHERE `PersonID` = ?", array($personID));
                $qArray = $q->fetchAll(PDO::FETCH_ASSOC);
                $serviceNums =  "";
                foreach($qArray as $row) {
                    if ($serviceNums !== "") {
                        $serviceNums.=",";
                    }
                    $serviceNums.=$row["ServiceNumber"];
                }
                $filled = str_replace('$serviceNumbers', $serviceNums, $filled);
                error_log($serviceNums);

                



                return $filled;
            }
        );
        break;
    case "VA-3288":
        fillPdf("../formTemplates/pdf/VA-Form-3288.pdf",
            "../formTemplates/fdf/VA-Form-3288.fdf",
            "VA-3288.pdf",
            $_GET["caseID"],
            $db,
            null
        );
        break;
    case "VA-21-4142":
        fillPdf("../formTemplates/pdf/vba-21-4142.pdf",
            "../formTemplates/fdf/vba-21-4142.fdf",
            "VA-21-4142.pdf",
            $_GET["caseID"],
            $db,
            null
        );
        break;

    default:
        echo "Error: No form exists with given ID.";
}


?>
