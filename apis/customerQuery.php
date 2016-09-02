<?php
include('connection.php');

if (isset($_POST['userId']) && isset($_POST['userId'])) {
    $sql = "SELECT Email FROM registeredusers WHERE userId='" . $_POST['userId'] . "'";
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        require_once 'mail.php';

        $emailBody = 'Hi Admin, <br/><br/> A User with following details has contacted from app.<br/><br/>';
        $emailBody .= 'Name: ' . $_POST['name'] . '<br />';
        $emailBody .= 'Mobile Number: ' . $_POST['mobileNumber'] . '<br />';
        $emailBody .= 'Email: ' . $row['Email'] . '<br />';
        $emailBody .= 'Query Type: ' . $_POST['type'] . '<br />';
        $emailBody .= 'Query: ' . $_POST['desciption'] . '<br />';
        $emailBody .= 'Want Callback: ' . $_POST['callback'] . '<br />';
        $emailBody .= '<br/>iShareRyde Team';

        $msg = array();
        $msg['Source'] = "support@clubmycab.com";
        $msg['Destination']['ToAddresses'][] = "support@clubmycab.com";
        $msg['Message']['Subject']['Data'] = "User Contacted from App";
        $msg['Message']['Body']['Html']['Data'] =$emailBody;
        $msg['Message']['Body']['Html']['Charset'] = "UTF-8";

        try{
            $result = $client->sendEmail($msg);

            http_response_code(200);
            header('Content-Type: application/json');
            echo '{status:"success", message:"Message sent"}';
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo '{status:"fail", message:"Mailer error:'. $e->getMessage().'"}';
            exit;

        }
    }
}
