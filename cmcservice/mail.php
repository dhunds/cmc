<?php
use Aws\Ses\SesClient;

require '../vendor/autoload.php';

$client = SesClient::factory(array(
    'key' => 'AKIAJM3Y6I63SA5QZMUA',
    'secret' => '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',
    'region' => 'us-east-1'
));

function sendRegistrationMail ($name, $email) {
    global $client;
    $body = '<html>
                <head>
                    <title></title>
                </head>
                <body>
                    <p>Hello '.ucfirst($name).',</p>

                    <p>Great to have you onboard.</p>

                    <p>Travel in a car for as low as <strong>Rs. 3/km</strong> !!</p>

                    <p>If you own a car and love to drive, <strong>offer rides and share fuel cost</strong> with friends.</p>

                    <p>If you don\'t, <strong>join a ride or share a cab.</strong></p>

                    <p>And did we mention the reduction of traffic and pollution? - Like we said, it IS the smartest way to travel <span style="font-size:8px;"><img alt="smiley" height="23" src="http://bestpromotionsmails.in/js/ckeditor/plugins/smiley/images/regular_smile.png" title="smiley" width="23" /></span></p>

                    <p>Team iShareRyde</p>

                    <p> </p>
                </body>
             </html>';

    $msg = array();
    $msg['Source'] = "support@ishareryde.com";
    $msg['Destination']['ToAddresses'][] = $email;
    $msg['Message']['Subject']['Data'] = "Welcome to iShareRyde - the smartest way to travel !!";
    $msg['Message']['Body']['Html']['Data'] =$body;
    $msg['Message']['Body']['Html']['Charset'] = "UTF-8";

    try{
        $result = $client->sendEmail($msg);
        //$msg_id = $result->get('MessageId');
        //echo("MessageId: $msg_id");
        //print_r($result);
    } catch (Exception $e) {
        error_log($e->getMessage());
        //echo($e->getMessage());
    }
}

function sendGroupCreationMail ($groupName) {
    global $client;
    $body = '<html>
                <head>
                    <title></title>
                </head>
                <body>
                    <p>Hello Admin,</p>

                    <p>A new group with the name <strong>'.$groupName.'</strong>has been created</p>
                    <p>&nbsp;</p>
                    <p>Team iShareRyde</p>
                </body>
             </html>';

    $msg = array();
    $msg['Source'] = "support@ishareryde.com";
    $msg['Destination']['ToAddresses'][] = "support@ishareryde.com";
    $msg['Message']['Subject']['Data'] = "New Group created";
    $msg['Message']['Body']['Html']['Data'] =$body;
    $msg['Message']['Body']['Html']['Charset'] = "UTF-8";

    try{
        $client->sendEmail($msg);
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

function sendInviteApprovalMail ($email) {
    global $client;
    $body = '<html>
                <head>
                    <title></title>
                </head>
                <body>
                    <p>Dear Admin,</p>

                    <p>Some new members referred in your group are waiting for approval.</p>

                    <p>Please login to iShareRyde to approve them. </p>
                    <p>Team iShareRyde</p>

                    <p> </p>
                </body>
             </html>';

    $msg = array();
    $msg['Source'] = "support@ishareryde.com";
    $msg['Destination']['ToAddresses'][] = $email;
    $msg['Message']['Subject']['Data'] = "New referred members waiting for approval";
    $msg['Message']['Body']['Html']['Data'] =$body;
    $msg['Message']['Body']['Html']['Charset'] = "UTF-8";

    try{
        $client->sendEmail($msg);
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

function sendPaymentMailMember ($name, $email, $ride, $distance, $perkmCharge,  $fare ) {
    global $client;
    $body = '<html>
                <head>
                    <title></title>
                </head>
                <body>
                    <p>Dear '.$name.',</p>
                    <p>Thanks for choosing iShareRyde. Below is your ride summary</p>
                    <p>Ride : '.$ride.'</p>
                    <p>Total Distance (KM) : '.$distance.' </p>
                    <p>Rate per Km : '.$perkmCharge.' </p>
                    <p>Total Fare : '.$fare.' </p>
                    <p>Team iShareRyde</p>
                </body>
             </html>';

    $msg = array();
    $msg['Source'] = "support@ishareryde.com";
    $msg['Destination']['ToAddresses'][] = $email;
    $msg['Message']['Subject']['Data'] = "iShareRyde Payment Details";
    $msg['Message']['Body']['Html']['Data'] =$body;
    $msg['Message']['Body']['Html']['Charset'] = "UTF-8";

    try{
        $client->sendEmail($msg);
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

function sendPaymentMailOwner ($name, $email, $cabId, $ride, $distance) {
    global $client;

    $stmt = $con->query("SELECT pl.*, ru.FullName FROM paymentLogs pl JOIN registeredusers ru ON pl.mobileNumberFrom=ru.MobileNumber WHERE  pl.paidTo = 1 AND pl.CabId='".$cabId."'");
    $membersJoined = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($membersJoined) {
        $body = '<html>
                <head>
                    <title></title>
                </head>
                <body>
                    <p>Dear '.$name.',</p>
                    <p>Thanks for choosing iShareRyde. Below is your ride summary</p>
                    <p>Ride : '.$ride.'</p>
                    <p>&nbsp;</p>
                    <p>Team iShareRyde</p>
                </body>
             </html>';

        while ($row = $stmt->fetch()) {

        }

        $msg = array();
        $msg['Source'] = "support@ishareryde.com";
        $msg['Destination']['ToAddresses'][] = $email;
        $msg['Message']['Subject']['Data'] = "New Group created";
        $msg['Message']['Body']['Html']['Data'] =$body;
        $msg['Message']['Body']['Html']['Charset'] = "UTF-8";

        try{
            $client->sendEmail($msg);
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }
}

function sendMail($from, $to, $subject, $body){
    global $client;

    $msg = array();
    $msg['Source'] = $from;
    $msg['Destination']['ToAddresses'][] = $to;
    $msg['Message']['Subject']['Data'] = $subject;
    $msg['Message']['Body']['Html']['Data'] =$body;
    $msg['Message']['Body']['Html']['Charset'] = "UTF-8";

    try{
        $client->sendEmail($msg);
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}