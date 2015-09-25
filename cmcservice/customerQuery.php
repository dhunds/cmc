<?php
include('connection.php');
require("classes/class.phpmailer.php");
$mail = new PHPMailer();

if (isset($_POST['mobileNumber']) && isset($_POST['mobileNumber'])) {
    $sql = "SELECT Email FROM registeredusers WHERE MobileNumber='" . $_POST['mobileNumber'] . "'";
    $stmt = $con->query($sql);
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $emailBody = 'Hi Admin, <br/><br/> A User with following details has contacted from app.<br/><br/>';
        $emailBody .= 'Name: ' . $_POST['name'] . '<br />';
        $emailBody .= 'Mobile Number: ' . $_POST['mobileNumber'] . '<br />';
        $emailBody .= 'Email: ' . $row['Email'] . '<br />';
        $emailBody .= 'Query Type: ' . $_POST['type'] . '<br />';
        $emailBody .= 'Query: ' . $_POST['desciption'] . '<br />';
        $emailBody .= 'Want Callback: ' . $_POST['callback'] . '<br />';
        $emailBody .= '<br/>ClubMyCab Team';

        $mail->From = "webmaster@clubmycab.com";
        $mail->FromName = "Webmaster";
        $mail->AddAddress("support@clubmycab.com");

        $mail->Subject = "User Contacted from App";
        $mail->Body = $emailBody;
        $mail->IsHTML(true);
        $mail->WordWrap = 50;

        if (!$mail->Send()) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo '{status:"fail", message:"Mailer error:'. $mail->ErrorInfo.'"}';
            exit;
        } else {
            http_response_code(200);
            header('Content-Type: application/json');
            echo '{status:"success", message:"Message sent"}';
            exit;
        }
    }
}
