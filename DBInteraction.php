<?php
require_once('../config.php');

class DBInteraction
{
    private $dbConn = null;

    function __construct()
    {
        try {
            $this->dbConn = new PDO("mysql:host=localhost;dbname=va-case-system", DB_USER, DB_PASSWORD);
            $this->dbConn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e->getMessage();
            die();
        }
    }

    public function getConn()
    {
        return $this->dbConn;
    }

    public function interact($mysqlCode, $paramArray)
    {
        $stmt = $this->dbConn->prepare($mysqlCode);
        $stmt->execute($paramArray);
        return $stmt;
    }

    public function interactNoExec($mysqlCode)
    {
        $stmt = $this->dbConn->prepare($mysqlCode);
        return $stmt;
    }

    /**
      Runs query with vals as inputs to query and then executes $operation per row;
      */
    public function each_row($query, $vals, $operation)
    {
        $stmt = $this->interact($query, $vals);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $operation($row);
        }
    }

    /* Ensure that the given name matches the name of one of the tables in the
     * database
     *
     * $name - a string that should match the name of one of the tables in the
     *          database
     */
    public function sanitizeTableName($name)
    {
        $sanitizedName;
        switch ($name) {
            case 'Case':
            case 'Comment':
            case 'Dependant':
            case 'Disability':
            case 'Document':
            case 'Mappings':
            case 'MilitaryService':
            case 'Person':
            case 'Task':
            case 'TaskType':
            case 'ThirdPartyTreatment':
            case 'VATreatment':
            case 'WartimeService':
                $sanitizedName = $name;
                break;
            default:
                echo "Invalid Table Name";
                break;
        }
        return $sanitizedName;
    }

    /* Ensure that the given name matches the name of one of the columns in the
     * database
     *
     * $name - a string that should match the name of one of the columns in the
     *          database
     */
    public function sanitizeColumnName($name)
    {
        $sanitizedName;
        switch ($name) {
            //Case
            case 'ID':
            case 'Name':
            case 'Archived':
            case 'Created':
            case 'Status':
            case 'Synopsis':
            case 'ClaimantID':
                //Comment
                //case 'ID':
            case 'UserID':
            case 'DateCreated':
            case 'Text':
            case 'EntityID':
            case 'UserName':
                //Dependant
                //case 'ID':
            case 'PersonID':
            case 'DependantName':
                //Disability
                //case 'ID':
                //case 'PersonID':
            case 'Disability':
            case 'HasFiledClaim':
            case 'ClaimStatus':
            case 'DenialDate':
            case 'CurrentPercentage':
                //Document
                //case 'ID':
            case 'Name':
            case 'CaseID':
            case 'TaskID':
            case 'Data':
                //Mappings
                //case 'ID':
            case 'Tag':
            case 'MTable':
            case 'MCol':
            case 'Direct':
                //MilitaryService
                //case 'ID':
                //case 'PersonID':
            case 'BranchOfService':
            case 'StartDate':
            case 'EndDate':
            case 'MOS':
            case 'ServiceNumber':
            case 'TypeOfDischarge':
                //Person
                //case 'ID':
            case 'FirstName':
            case 'MiddleName':
            case 'LastName':
            case 'HowFoundOffice':
            case 'NameOfAlternateContact':
            case 'RelationToAlternateContact':
            case 'PhoneOfAlternateContact':
            case 'Address':
            case 'City':
            case 'State':
            case 'ZIP':
            case 'MailingAddress':
            case 'MailingCity':
            case 'MailingState':
            case 'MailingZIP':
            case 'HomePhone':
            case 'BusinessPhone':
            case 'CellPhone':
            case 'Fax':
            case 'Email':
            case 'SSN':
            case 'Birthdate':
            case 'Birthplace':
            case 'MaritalStatus':
            case 'MarriageDate':
            case 'SpouseName':
            case 'DivorceDate':
            case 'StateWhereDivorced':
            case 'Employed':
            case 'EmployerName':
            case 'ReasonForUnemployment':
            case 'ReceivingSSDI/SSI':
            case 'AppliedForSSI':
            case 'AppliedForSSDI':
            case 'IsVeteran':
            case 'VeteranName':
            case 'MilitaryRetired':
            case 'MilitaryMedicalRetired':
            case 'HasPEBDisabilityPercentage':
            case 'CurrentVADisabilityPercentage':
            case 'CurrentRatingDisabilityPercentage':
            case 'SubmittedSSDIB/SSIApplication':
            case 'DateSubmittedSSDIB/SSIApplication':
            case 'VARO':
            case 'VAFileNumber':
            case 'VADecisionDate':
            case 'IssuesToAppeal':
            case 'DateNoticeOfDisagreementFiled':
            case 'ServiceConnection':
            case 'PercentageOfDisability':
            case 'Unemployability':
            case 'AdditionalInformation':
                //Task
                //case 'ID':
                //case 'CaseID':
            case 'TaskTypeID':
                //case 'Status':
            case 'DateStarted':
                //TaskType
            case 'id':
                //case 'Name':
            case 'Description':
            case 'AutoGenerationRoutines':
            case 'SortOrder':
                //ThirdPartyTreatment
                //case 'ID':
                //case 'PersonID':
            case 'TreatmentProvider':
            case 'Location':
                //case 'StartDate':
                //case 'EndDate':
            case 'Diagnosis':
                //VATreatment
                //case 'ID':
                //case 'PersonID':
            case 'TreatmentFacility':
                //WartimeService
                //case 'ID':
                //case 'PersonID':
            case 'War':
            case 'StartDateOfWartimeService':
            case 'EndDateOfWartimeService':
            case 'WhereStationed':
            case 'Unit':
            case 'CombatMedals':
                $sanitizedName = $name;
                break;
            default:
                echo "Invalid Column Name";
                break;
        }
        return $sanitizedName;
    }

    /**
      Runs $query with $data as input and returns the first query that matches.
      */
    public function get1($query, $data)
    {
        return $this->interact($query, $data)->fetch(PDO::FETCH_ASSOC);
    }
}

?>
