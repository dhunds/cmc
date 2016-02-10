<?php
use Aws\Ses\SesClient;
require 'vendor/autoload.php';

$client = SesClient::factory(array(
    'key' => 'AKIAJM3Y6I63SA5QZMUA',
    'secret' => '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',
    'region' => 'us-east-1'
));

// Check for empty fields
if(empty($_POST['name'])  		||
   empty($_POST['email']) 		||
   empty($_POST['phone']) 		||
   empty($_POST['message'])	||
   empty($_POST['organisation'])   ||
   !filter_var($_POST['email'],FILTER_VALIDATE_EMAIL))
   {
	echo "No arguments Provided!";
	return false;
   }
$name = $_POST['name'];
$emailBody = 'Hello Admin, <br/><br/> You have received a new message from your website contact form.<br/><br/> Here are the details:<br/><br/>';
$emailBody .= 'Name: ' . $_POST['name'] . '<br />';
$emailBody .= 'Organization Name: ' . $_POST['organisation'] . '<br />';
$emailBody .= 'Email: ' . $_POST['email'] . '<br />';
$emailBody .= 'Phone: ' . $_POST['phone'] . '<br />';
$emailBody .= 'Message: ' . $_POST['message'] . '<br />';

$emailBody .= '<br/>iShareRyde Team';

$msg = array();
$msg['Source'] = "support@clubmycab.com";
$msg['Destination']['ToAddresses'][] = "support@clubmycab.com";
$msg['Message']['Subject']['Data'] = "Website Contact Form:  $name";
$msg['Message']['Body']['Html']['Data'] = $emailBody;
$msg['Message']['Body']['Html']['Charset'] = "UTF-8";

$result = $client->sendEmail($msg);
return true;