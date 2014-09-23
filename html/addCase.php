<?php
require('headlessheader.php');
try {
    $claimantID = $_POST['claimantID'];
    $status1;

    // If an existing claimant was not associated with this case, the user opted
    // to add a new claimant; insert a new claimant into the database and
    // retrieve the claiman's ID so the claimant can be associated with the case
    if ($claimantID == null) {
        $status1 = $db->interact('INSERT INTO `Person` (`IsVeteran`) VALUES (0);', array());
        $claimantID = $conn->lastInsertId();
    }

    // Insert the new case into the database and associate the chosen claimant
    // (or the new claimant if no existing claimant was chosen) with the case
    $status2 = $db->interact('INSERT INTO `Case` (`Name`, `ClaimantID`) VALUES (:name, :claimantID)', array(':name' => $_POST['newCaseName'], ':claimantID' => $claimantID));
    $caseID = $conn->lastInsertId();
    if ($status2) {
        // The case ID is used to link to the case page from the case list
        echo $caseID;
    } else {
        echo "ERROR2!!!";
    }

} catch (PDOException $e) {
    echo $e->getMessage();
    echo "ERROR!!!";
}
?>
