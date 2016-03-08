<?php
include_once('connection.php');
define("MID", "MBK9572");
define("MERCHANT_NAME", "iShareRyde");
define("API_SECRET", "bToO0UJ7OaijZ9g7a1Axr3J5OpEF");
define("LOAD_MONEY_URL", "https://walletapi.mobikwik.com/loadmoney");
define("WALLET_ID", "support@clubmycab.com");

if (isset($_POST['submit'])) {
    $orderid = uniqid();
    $typeofmoney = 0;
    $creditmethod = 'cashback';
    $walletid = WALLET_ID;
    $error = 0;

    if (isset($_POST['amount']) && $_POST['amount'] != '') {
        $amount = $_POST['amount'];
    } else {
        $error = 1;
    }

    if (isset($_POST['cell']) && $_POST['cell'] != '') {
        $cell = substr(trim($_POST['cell']), -10);
    } else {
        $error = 1;
    }

    $comment = $_POST['comment'];

    if (!$error) {
        $string = "'".$amount ."''". $cell ."''". $comment ."''". $creditmethod ."''". MERCHANT_NAME ."''". MID ."''". $orderid ."''". $typeofmoney ."''". $walletid."'";

        $checksum = hash_hmac('sha256', $string, API_SECRET);

        $fields = array('amount' => $amount, 'typeofmoney' => $typeofmoney, 'cell' => $cell, 'orderid' => $orderid, 'creditmethod' => $creditmethod, 'comment' => $comment, 'walletid' => $walletid, 'mid' => MID, 'merchantname' => MERCHANT_NAME, 'checksum' => $checksum);

//        $headers = 'payloadtype=json';
        $strParams = http_build_query($fields);
        $url = LOAD_MONEY_URL . '?' . $strParams;
		//echo $url;die;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json')
        ));
        $result = curl_exec($curl);
        curl_close($curl);

        if ($result === FALSE) {
            echo curl_error($ch);
        } else {
            $resp = simplexml_load_string($result);

            if ($resp->status =='SUCCESS'){
	            $cell = '0091'.substr(trim($_POST['cell']), -10);
                $sql = "INSERT INTO payments(mobileNumber, amount, comment, sender, DateTime) VALUES ('" . $cell . "', '" . $_POST['amount'] . "', '" . $_POST['comment'] . "', 'admin', now())";
                $nStmt = $con->prepare($sql);
                $nStmt->execute();
            }
            echo $result;
            echo '<pre>';
            print_r($resp);
        }
    }
}

?>
<style>
    .divLeft {
        float: left;
        width: 10%;
    }

    .divRight {
        float: left;
        width: 90%;
    }
</style>
<div>
    <div>
        <div style="width:100%;height:100%;float:left;">
            <h2 class="headingText">Wallet Recharge</h2>

            <form method="post" action="" enctype="multipart/form-data">
                <div style="margin-left: 5px;">
                    <div class="divLeft bluetext">&nbsp;&nbsp;* Mobile Number:</div>
                    <div class="divRight bluetext"><input type="text" name="cell" id="cell"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;* Amount:</div>
                    <div class="divRight bluetext"><input type="text" name="amount" id="amount" onkeyup="showClubs();">
                    </div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;Comment:</div>
                    <div class="divRight bluetext"><textarea name="comment" cols="30" rows="5"></textarea></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;<input type="submit" name="submit"
                                                                     value="Add Money" class="cBtn"></div>
                    <div class="divRight bluetext"></div>

                </div>
            </form>

            <br/>
        </div>

        <div style="clear:both;"></div>
    </div>
</div>