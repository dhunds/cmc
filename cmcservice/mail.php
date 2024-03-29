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

    $body = '<div  style="margin:0px;">
   <table border="0" align="center" cellpadding="0" width="600" cellspacing="0" bgcolor="#f5f5f5" style="margin:0 auto;table-layout:fixed">
      <tbody>
         <tr>
            <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="30" width="300" /></td>
         </tr>
         <tr>
            <td>
               <table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
                  <tbody>
                     <tr>
                        <td align="center"><a href="https://www.ishareryde.com" target="_blank"><img alt="" src="'.IMG_URL.'/logo.png" style="display:block" border="0" /></a></td>
                     </tr>
                  </tbody>
               </table>
            </td>
         </tr>
         <tr>
            <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="30" width="300" /></td>
         </tr>
         <tr>
            <td>
               <table align="center" border="0" bgcolor="#ffffff" cellpadding="0" cellspacing="0" width="560">
                  <tbody>
                     <tr>
                        <td style="font-size:15px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif; padding:20 5 5 10px;">
                           Dear <strong>'.ucfirst($name).'</strong>,<br /><br /></td>
                     </tr>
                     <tr>
                        <td style="font-size:15px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif; padding:5 5 5 10px;">
                           Great to have you onboard.</td>
                     </tr>
                     <tr>
                        <td style="font-size:15px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif; padding:5 5 5 10px;">
                           Travel in a car for as low as <b>Rs. 4/km !!</b></td>
                     </tr>
                     <tr>
                        <td style="font-size:15px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif; padding:5 5 5 10px;">
                           If you own a car and love to drive, <b>offer rides and share fuel cost</b> with friends.</td>
                     </tr>
                     <tr>
                        <td style="font-size:15px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif; padding:5 5 5 10px;">
                           If you don\'t, <b>join a ride or share a cab.</b></td>
                     </tr>
                     <tr>
                        <td style="font-size:15px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif; padding:5 5 5 10px;">
                           And did we mention the reduction of traffic and pollution? - Like we said, it IS the smartest way to travel</td>
                     </tr>
                     <tr>
                        <td style="font-size:15px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif; padding:5 5 5 10px;">
                           <br />
                           Team iShareRyde</td>
                     </tr>
                     <tr >
                        <td colspan="2"><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="20" width="300" /></td>
                     </tr>
                  </tbody>
               </table>
            </td>
         </tr>
         <tr>
            <td>
               <table align="center" border="0"cellpadding="0" cellspacing="0" width="450">
                  <tbody>
                     <tr >
                        <td colspan="2"><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="20" width="300" /></td>
                     </tr>
                     <tr>
                        <td align="center" style="font-size:14px; color:#6d6d6d; font-family: Helvetica, Arial, sans-serif; line-height:25px;" height="30"><a href="mailto:support@ishareryde.com" style="color:#6d6d6d; text-decoration:none;">support@iShareRyde.com</a><br/>
                           <a href="https://www.ishareryde.com" target="_blank" style="color:#6d6d6d; text-decoration:none;">www.ishareryde.com</a>
                        </td>
                     </tr>
                     <tr >
                        <td colspan="2"><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="15" width="300" /></td>
                     </tr>
                     <tr>
                        <td align="center"><a href="https://www.facebook.com/ishareryde" target="_blank"><img src="'.IMG_URL.'/facebook.png" /></a> <a href="https://twitter.com/ishareryde" target="_blank"><img src="'.IMG_URL.'/twitter.png" /></a></td>
                     </tr>
                     <tr >
                        <td colspan="2"><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="15" width="300" /></td>
                     </tr>
                  </tbody>
               </table>
            </td>
         </tr>
         <tr>
         </tr>
      </tbody>
   </table>
   <div style="white-space:nowrap;font:15px courier;color:#ffffff">
        - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
   </div>
   <font color="#666666" size="1" face="Verdana,Helvetica"></font><br>
   <div class="yj6qo"></div>
   <div class="adL"></div>
</div>';

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

function sendPaymentFailedMail ($cabId, $amount, $onBehalfOf, $orderId, $to) {
    global $client;
    $body = '<html>
                <head>
                    <title></title>
                </head>
                <body>
                    <p>Hello Admin,</p>

                    <p>User payment failed. Following is the detail of same.</p>

                    <p>Cab ID: '.$cabId.'</p>
                    <p>Amount: '.$amount.'</p>
                    <p>Paying For: '.$onBehalfOf.'</p>
                    <p>Order ID: '.$orderId.'</p>
                    <p>Receiver Number: '.$to.'</p>

                    <p>Webmaster</p>

                    <p> </p>
                </body>
             </html>';

    $msg = array();
    $msg['Source'] = "support@ishareryde.com";
    $msg['Destination']['ToAddresses'][] = "Webmaster<support@ishareryde.com>";
    $msg['Message']['Subject']['Data'] = "Payment Failed !!";
    $msg['Message']['Body']['Html']['Data'] =$body;
    $msg['Message']['Body']['Html']['Charset'] = "UTF-8";

    try{
        $client->sendEmail($msg);
    } catch (Exception $e) {
        error_log($e->getMessage());
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

function sendPaymentMailToAssociate ($name, $email, $from, $amount) {
    global $client;
    $body = '<html>
                <head>
                    <title></title>
                </head>
                <body>
                    <p>Dear '.$name.',</p>

                    <p>You have received payment of Rs. '.$amount.' from '.$from.'</p>
                    <p>Team iShareRyde</p>

                    <p> </p>
                </body>
             </html>';

    $msg = array();
    $msg['Source'] = "support@ishareryde.com";
    $msg['Destination']['ToAddresses'][] = $email;
    $msg['Message']['Subject']['Data'] = "Payment Received";
    $msg['Message']['Body']['Html']['Data'] =$body;
    $msg['Message']['Body']['Html']['Charset'] = "UTF-8";

    try{
        $client->sendEmail($msg);
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

function sendPaymentMailOwner ($email, $RideDetail, $subject){

    $str = '<div  style="margin:0px;">
      <table border="0" align="center" cellpadding="0" width="600" cellspacing="0" bgcolor="#f5f5f5" style="margin:0 auto;table-layout:fixed">
         <tbody>
            <tr>
               <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="30" width="300" /></td>
            </tr>
            <tr>
               <td>
                  <table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
                     <tbody>
                        <tr>
                           <td align="center"><a href="https://www.ishareryde.com" target="_blank"><img alt="" src="'.IMG_URL.'/logo.png" style="display:block" border="0" /></a></td>
                        </tr>
                     </tbody>
                  </table>
               </td>
            </tr>
            <tr>
               <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="30" width="300" /></td>
            </tr>
            <tr>
               <td>
                  <table align="center" border="0" bgcolor="#ffffff" cellpadding="0" cellspacing="0" width="560">
                     <tbody>
                        <tr>
                           <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="30" width="300" /></td>
                        </tr>
                        <tr>
                           <td align="center" style="font-size:17px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif;">Thanks for choosing iShareRyde</td>
                        </tr>
                        <tr>
                           <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="30" width="300" /></td>
                        </tr>
                        <tr>
                           <td>
                              <table align="center" border="0"cellpadding="0" cellspacing="0" width="450">
                                 <tbody>
                                 <tr>
                                    <td>
                                       <table align="center" border="0"cellpadding="0" cellspacing="0" width="450">
                                          <tbody>
                                             <tr style="">
                                                <td align="left" style="font-size:28px; color:#16a4de; font-family: Helvetica, Arial, sans-serif;"><img src="'.IMG_URL.'/rupees.png" /> <span style="padding:0 0 5px 0; vertical-align:top;">'.$RideDetail['ride']['amount'].'</span></td>
                                                <td align="right" style="font-size:14px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif;
                                                   Helvetica, Arial, sans-serif;">'.date('F d, Y').'</td>
                                             </tr>
                                             <tr >
                                                <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="20" width="300" /></td>
                                             </tr>
                                          </tbody>
                                       </table>
                                    </td>
                                 </tr>
                                    <tr>
                                       <td style="border-bottom:1px solid #f1f1f1;"></td>
                                    </tr>
                                    <tr >
                                       <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="20" width="300" /></td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <table align="right" border="0"cellpadding="0" cellspacing="0" width="450">
                                             <tbody>
                                                <tr>
                                                   <td width="60%" align="left" style="font-size:14px; line-height:22px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif;">
                                                      <div style="float:left; margin-right:15px;"><img src="'.IMG_URL.'/map.png" /></div>
                                                      '.$RideDetail['ride']['FromShortName'].' <br/><br/>
                                                      '.$RideDetail['ride']['ToShortName'].'
                                                   </td>
                                                   <td width="40%" style="vertical-align: top; border-left:1px solid #f1f1f1; padding-left:15px;">
                                                      <table align="center" width="100%" border="0"cellpadding="0" cellspacing="0" >
                                                         <tbody>
                                                            <tr>
                                                               <td align="left"  style="font-size:13px; line-height:30px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif;">TRIP DATE</td>
                                                               <td align="right" style="font-size:13px; line-height:30px; color:#333333; font-family: Helvetica, Arial, sans-serif;">'.date('jS M', strtotime($RideDetail['ride']['TravelDate'])).'</td>
                                                            </tr>

                                                            <tr>
                                                               <td align="left" style="font-size:13px; line-height:30px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif;">KILOMETERS</td>
                                                               <td align="right" style="font-size:13px; line-height:30px; color:#333333; font-family: Helvetica, Arial, sans-serif;">'.$RideDetail['ride']['Distance'].'</td>
                                                            </tr>
                                                            <tr>
                                                               <td align="left"  style="font-size:13px; line-height:30px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif;">PAYMENTS</td>
                                                               <td align="right" style="font-size:13px; line-height:30px; color:#333333; font-family: Helvetica, Arial, sans-serif;">&#8377; '.$RideDetail['ride']['amount'].'</td>
                                                            </tr>
                                                         </tbody>
                                                      </table>
                                                   </td>
                                                </tr>
                                             </tbody>
                                          </table>
                                       </td>
                                    </tr>
                                    <tr >
                                       <td colspan="2"><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="20" width="300" /></td>
                                    </tr>
                                 </tbody>
                              </table>
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </td>
            </tr>
         </tr>
         <tr >
            <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="15" width="300" /></td>
         </tr>
         <tr>
            <td align="center" style="padding:10 0px; font-size:16px; color:#16a4de; font-family: Helvetica, Arial, sans-serif;" height="30">Payments Received</td>
         </tr>
         <tr >
            <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="15" width="300" /></td>
         </tr>';

  foreach ($RideDetail['members'] as $member) {


      $str .= '<tr>
            <td>
               <table bgcolor="#ffffff" style="padding:20px;" align="center" border="0"cellpadding="0" cellspacing="0" width="560">
                  <tbody>
                     <tr style="">
                        <td colspan="2" align="left" style="font-size:16px; color:#333333; font-family: Helvetica, Arial, sans-serif; line-height:22px; width: 100px;" height="30">'.$member['FullName'].'<br/></td>
                     </tr>
                     <tr>
                        <td align="left" style="font-size:12px; color:#838383; font-family: Helvetica, Arial, sans-serif; border-bottom: 1px #f1f1f1 solid;
                        padding-bottom: 10px;" height="15">'.$member['MemberLocationAddress'].'<br /> <div style="color:#000000; text-align:center; font-weight:bold;">TO </div> <br/> '.$member['MemberEndLocationAddress'].'</td>
                        <td align="right" style="font-size:12px; color:#838383; font-family: Helvetica, Arial, sans-serif; border-bottom: 1px #f1f1f1 solid;
                        padding-bottom: 10px;" height="15">'.$member['distance'].' km</td>
                     </tr>
                     <tr >
                        <td colspan="2"><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="5" width="300" /></td>
                     </tr>
                     <tr>
                        <td align="left" style="font-size:14px; color:#333333; font-family: Helvetica, Arial, sans-serif;" height="30"> Amount :</td>
                        <td align="right" style="font-size:14px; color:#333333; font-family: Helvetica, Arial, sans-serif;
                        Helvetica, Arial, sans-serif;">'.$member['amount'].'</td>
                     </tr>
                     <tr>
                        <td align="left" style="font-size:14px; color:#333333; font-family: Helvetica, Arial, sans-serif;" height="30"> Service Charge :</td>
                        <td align="right" style="font-size:14px; color:#333333; font-family: Helvetica, Arial, sans-serif;
                        Helvetica, Arial, sans-serif;"> - '.$member['serviceCharge'].'</td>
                     </tr>
                     <tr>
                        <td align="left" style="font-size:14px; color:#333333; font-family: Helvetica, Arial, sans-serif;" height="30"> Service Tax :</td>
                        <td align="right" style="font-size:14px; color:#333333; font-family: Helvetica, Arial, sans-serif;
                        Helvetica, Arial, sans-serif;"> - '.$member['serviceTax'].'</td>
                     </tr>
                     <tr >
                        <td colspan="2"><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="2" width="300" /></td>
                     </tr>
                     <tr>
                        <td align="left" style="font-size:16px; color:#16a4de; font-family: Helvetica, Arial, sans-serif;" height="30"> Subtotal</td>
                        <td align="right" style="font-size:16px; color:#16a4de; font-family: Helvetica, Arial, sans-serif;"><img src="'.IMG_URL.'/rupees-1.png" /><span style="padding:0 0 5px 0; vertical-align:top;"> '.($member['amount'] - ($member['serviceCharge'] + $member['serviceTax'])).'</span></td>
                     </tr>
                  </tbody>
               </table>
            </td>
         </tr>
         <tr >
            <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="20" width="300" /></td>
         </tr>';
  }

$str .='<tr >
            <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="20" width="300" /></td>
         </tr>
         <tr>
            <td>
               <table align="center" border="0"cellpadding="0" cellspacing="0" width="450">
                  <tbody>
                     <tr>
                        <td align="center" style="font-size:14px; color:#6d6d6d; font-family: Helvetica, Arial, sans-serif; line-height:25px;" height="30"><a href="mailto:support@ishareryde.com" style="color:#6d6d6d; text-decoration:none;">support@iShareRyde.com</a><br/>
                           <a href="https://www.ishareryde.com" target="_blank" style="color:#6d6d6d; text-decoration:none;">www.ishareryde.com</a>
                        </td>
                     </tr>
                     <tr >
                        <td colspan="2"><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="15" width="300" /></td>
                     </tr>
                     <tr>
                        <td align="center"><a href="https://www.facebook.com/ishareryde" target="_blank"><img src="'.IMG_URL.'/facebook.png" /></a> <a href="https://twitter.com/ishareryde" target="_blank"><img src="'.IMG_URL.'/twitter.png" /></a></td>
                     </tr>
                     <tr >
                        <td colspan="2"><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="15" width="300" /></td>
                     </tr>
                  </tbody>
               </table>
            </td>
         </tr>
         <tr>
         </tr>
      </tbody>
   </table>
   <div style="white-space:nowrap;font:15px courier;color:#ffffff">
      - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
   </div>
   <font color="#666666" size="1" face="Verdana,Helvetica"></font><br>
   <div class="yj6qo"></div>
   <div class="adL"></div>
   </div>';

    sendMail('iShareRyde<support@ishareryde.com>', $email, $subject, $str);
}



function sendPaymentMailMember ($rideDetails) {

    $str = '<div  style="margin:0px;">
   <table border="0" align="center" cellpadding="0" width="600" cellspacing="0" bgcolor="#f5f5f5" style="margin:0 auto;table-layout:fixed">
      <tbody>
         <tr>
            <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="30" width="300" /></td>
         </tr>
         <tr>
            <td>
               <table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
                  <tbody>
                     <tr>
                        <td align="center"><a href="https://www.ishareryde.com" target="_blank"><img alt="" src="'.IMG_URL.'/logo.png" style="display:block" border="0" /></a></td>
                     </tr>
                  </tbody>
               </table>
            </td>
         </tr>
         <tr>
            <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="30" width="300" /></td>
         </tr>
         <tr>
            <td>
               <table align="center" border="0" bgcolor="#ffffff" cellpadding="0" cellspacing="0" width="560">
                  <tbody>
                     <tr>
                        <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="30" width="300" /></td>
                     </tr>
                     <tr>
                        <td align="center" style="font-size:17px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif;">Thanks for choosing iShareRyde</td>
                     </tr>
                     <tr>
                        <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="30" width="300" /></td>
                     </tr>
                     <tr>
                        <td>
                           <table align="center" border="0"cellpadding="0" cellspacing="0" width="450">
                              <tbody>
                                 <tr>
                                    <td>
                                       <table align="center" border="0"cellpadding="0" cellspacing="0" width="450">
                                          <tbody>
                                             <tr style="">
                                                <td align="left" style="font-size:28px; color:#16a4de; font-family: Helvetica, Arial, sans-serif;"><img src="'.IMG_URL.'/rupees.png" /> <span style="padding:0 0 5px 0; vertical-align:top;">'.$rideDetails['amount'].'</span></td>
                                                <td align="right" style="font-size:14px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif;
                                                   Helvetica, Arial, sans-serif;">'.date('F d, Y').'</td>
                                             </tr>
                                             <tr >
                                                <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="20" width="300" /></td>
                                             </tr>
                                          </tbody>
                                       </table>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td style="border-bottom:1px solid #f1f1f1;"></td>
                                 </tr>
                                 <tr >
                                    <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="20" width="300" /></td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <table align="center" border="0"cellpadding="0" cellspacing="0" width="450">
                                          <tbody>
                                             <tr style="">
                                                <td align="left" style="font-size:16px; color:#333333; font-family: Helvetica, Arial, sans-serif;" height="30"> Per KM Charge :</td>
                                                <td align="right" style="font-size:16px; color:#333333; font-family: Helvetica, Arial, sans-serif;
                                                   Helvetica, Arial, sans-serif;">'.$rideDetails['perKmCharge'].'</td>
                                             </tr>
                                             <tr style="">
                                                <td align="left" style="font-size:16px; color:#333333; font-family: Helvetica, Arial, sans-serif;" height="30"> Distance :</td>
                                                <td align="right" style="font-size:16px; color:#333333; font-family: Helvetica, Arial, sans-serif;
                                                   Helvetica, Arial, sans-serif;">'.$rideDetails['distance'].' Km</td>
                                             </tr>';
                                            if ($rideDetails['discount']) {
                                                $str .= '
                                                 <tr>
                                                    <td align="left" style="font-size:16px; color:#333333; font-family: Helvetica, Arial, sans-serif;" height="30"> Discount :</td>
                                                    <td align="right" style="font-size:16px; color:#333333; font-family: Helvetica, Arial, sans-serif;
                                                       Helvetica, Arial, sans-serif;">' . $rideDetails['discount'] . '</td>
                                                 </tr>';
                                            }
                                            if ($rideDetails['credits']) {
                                                $str .= '
                                                <tr>
                                                    <td align="left" style="font-size:16px; color:#333333; font-family: Helvetica, Arial, sans-serif;" height="30"> Credits Used :</td>
                                                    <td align="right" style="font-size:16px; color:#333333; font-family: Helvetica, Arial, sans-serif;
                                                       Helvetica, Arial, sans-serif;">' . $rideDetails['credits'] . '</td>
                                                 </tr>';
                                            }
                                            $str .= '<tr >
                                                <td colspan="2"><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="10" width="300" /></td>
                                             </tr>
                                             <tr>
                                                <td align="left" style="font-size:16px; color:#16a4de; font-weight:bold; font-family: Helvetica, Arial, sans-serif; border-bottom:1px solid #f1f1f1; border-top:1px solid #f1f1f1;" height="50"> Subtotal</td>
                                                <td align="right" style="font-size:16px; color:#16a4de; font-weight:bold; border-bottom:1px solid #f1f1f1; border-top:1px solid #f1f1f1; font-family: Helvetica, Arial, sans-serif;"><img src="'.IMG_URL.'/rupees-1.png" /><span style="padding:0 0 5px 0; vertical-align:top;"> '.$rideDetails['amount'].'</span></td>
                                             </tr>
                                             <tr >
                                                <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="30" width="300" /></td>
                                             </tr>
                                          </tbody>
                                       </table>
                                    </td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <table align="center" border="0"cellpadding="0" cellspacing="0" width="450">
                                          <tbody>
                                             <tr>
                                                <td align="left" style="font-size:14px; line-height:25px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif;">
                                                   <div style="float:left; margin-right:15px; height:125px;"><img src="'.IMG_URL.'/map.png" /></div>'.$rideDetails['MemberLocationAddress'].'<br/><br/>
                                                   '.$rideDetails['MemberEndLocationAddress'].'
                                                </td>
                                             </tr>
                                          </tbody>
                                       </table>
                                    </td>
                                 </tr>
                                 <tr >
                                    <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="20" width="300" /></td>
                                 </tr>
                                 <tr>
                                    <td style="border-bottom:1px solid #f1f1f1;"></td>
                                 </tr>
                                 <tr >
                                    <td><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="20" width="300" /></td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <table align="center" border="0"cellpadding="0" cellspacing="0" width="450">
                                          <tbody>
                                             <tr>
                                                <td align="center" style="font-size:14px; line-height:25px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif;">VEHICLE</td>
                                                <td align="center" style="font-size:14px; line-height:25px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif;">REGISTRATION NO.</td>
                                                <td align="center" style="font-size:14px; line-height:25px; color:#8e8e8e; font-family: Helvetica, Arial, sans-serif;">RIDE TIME</td>
                                             </tr>
                                             <tr>
                                                <td align="center" style="font-size:14px; line-height:25px; color:#333333; font-family: Helvetica, Arial, sans-serif;">'.$rideDetails['vehicleModel'].'</td>
                                                <td align="center" style="font-size:14px; line-height:25px; color:#333333; font-family: Helvetica, Arial, sans-serif;">'.$rideDetails['registrationNumber'].'</td>
                                                <td align="center" style="font-size:14px; line-height:25px; color:#333333; font-family: Helvetica, Arial, sans-serif;">'.$rideDetails['TravelTime'].'</td>
                                             </tr>
                                          </tbody>
                                       </table>
                                    </td>
                                 </tr>
                                 <tr >
                                    <td colspan="2"><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="30" width="300" /></td>
                                 </tr>
                                 <tr>
                                    <td>
                                       <table align="center" border="0"cellpadding="0" cellspacing="0" width="450">
                                          <tbody>
                                             <tr>
                                                <td width="80"><img src="http://107.167.183.147/cmc/cmcservice/ProfileImages/'.$rideDetails['imagename'].'" onerror="this.src=\'http://107.167.183.147/cmc/img/avatar.png\'" height="50" width="50" style="border-radius: 50%;"/></td>
                                                <td align="left" style="font-size:16px; color:#333333; font-family: Helvetica, Arial, sans-serif;" height="30">You rode with '.$rideDetails['OwnerName'].'</td>
                                             </tr>
                                          </tbody>
                                       </table>
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </td>
                     </tr>
                     <tr >
                        <td colspan="2"><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="20" width="300" /></td>
                     </tr>
                  </tbody>
               </table>
            </td>
         </tr>
         <tr>
            <td>
               <table align="center" border="0"cellpadding="0" cellspacing="0" width="450">
                  <tbody>
                     <tr >
                        <td colspan="2"><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="20" width="300" /></td>
                     </tr>
                     <tr>
                        <td align="center" style="font-size:14px; color:#6d6d6d; font-family: Helvetica, Arial, sans-serif; line-height:25px;" height="30"><a href="mailto:support@ishareryde.com" style="color:#6d6d6d; text-decoration:none;">support@iShareRyde.com</a><br/>
                           <a href="https://www.ishareryde.com" target="_blank" style="color:#6d6d6d; text-decoration:none;">www.ishareryde.com</a>
                        </td>
                     </tr>
                     <tr >
                        <td colspan="2"><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="15" width="300" /></td>
                     </tr>
                     <tr>
                        <td align="center"><a href="https://www.facebook.com/ishareryde" target="_blank"><img src="'.IMG_URL.'/facebook.png" /></a> <a href="https://twitter.com/ishareryde" target="_blank"><img src="'.IMG_URL.'/twitter.png" /></a></td>
                     </tr>
                     <tr >
                        <td colspan="2"><img alt="" src="'.IMG_URL.'/space.gif" style="display:block" border="0" height="15" width="300" /></td>
                     </tr>
                  </tbody>
               </table>
            </td>
         </tr>
         <tr>
         </tr>
      </tbody>
   </table>
   <div style="white-space:nowrap;font:15px courier;color:#ffffff">
      - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
   </div>
   <font color="#666666" size="1" face="Verdana,Helvetica"></font><br>
   <div class="yj6qo"></div>
   <div class="adL"></div>
</div>';





    sendMail('iShareRyde<support@ishareryde.com>', $rideDetails['memberEmail'], 'Your Ride Summary', $str);
}