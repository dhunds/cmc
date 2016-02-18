<?php
ini_set('max_execution_time', 0);
include('connection.php');

if (isset($_POST['mobileNumber']) && $_POST['mobileNumber'] !='' && isset($_POST['numbers']) && $_POST['numbers'] !='') {
    $mobileNumber = $_POST['mobileNumber'];
    $strNum = $_POST['numbers'];
    $arrNum = explode(',', $strNum);

    foreach($arrNum as $mobileNumber){
        $stmt = $con->query("SELECT id FROM contacts WHERE mobileNumber='$mobileNumber'");
        $numberExists = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($numberExists > 0) {
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $sql = "INSERT INTO userContacts SET mobileNumber='$mobileNumber', contactId=".$data['id'];
            $stmt = $con->prepare("INSERT INTO userContacts SET mobileNumber='$mobileNumber', contactId=".$data['id']);
            $stmt->execute();
        } else {
            $stmt = $con->prepare("INSERT INTO contacts SET mobileNumber='$mobileNumber'");
            $stmt->execute();
            $contactId = $con->lastInsertId();

            $stmt = $con->prepare("INSERT INTO userContacts SET mobileNumber='$mobileNumber', contactId=".$contactId);
            $stmt->execute();
        }
    }
    http_response_code(200);
    header('Content-Type: application/json');
    echo '{"status":"success", "message":"Contacts Saved"}';
    exit;
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo '{"status":"failed", "message":"Invalid Params."}';
}