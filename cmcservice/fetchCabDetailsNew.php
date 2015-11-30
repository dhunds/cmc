<?php

include('connection.php');

function CalculateDurationAndDistance($slat, $slon, $elat, $elon)
{
    $ch1 = curl_init();
    $fields_string = '';
    $fieldsNew = array(
        'origin' => $slat . ',' . $slon,
        'destination' => $elat . ',' . $elon
    );
    foreach ($fieldsNew as $key => $value) {
        $fields_string .= $key . '=' . urlencode($value) . '&';
    }
    rtrim($fields_string, '&');
    curl_setopt($ch1, CURLOPT_URL, "http://localhost/cmc/cmcservice/distanceApi.php");
    curl_setopt($ch1, CURLOPT_POST, true);
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch1, CURLOPT_POSTFIELDS, $fields_string);
    $resultNew = curl_exec($ch1);
    return $resultNew;
}

function getDuration($slat, $slon, $elat, $elon)
{
    $data = CalculateDurationAndDistance($slat, $slon, $elat, $elon);
    $dataVal = explode("|", $data);
    return $dataVal[0];
}

function distance($lat1, $lon1, $lat2, $lon2)
{

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $km = $miles * 1.609344;
}

function IsNullOrEmptyString($question)
{
    return (!isset($question) || trim($question) === '');
}

function sort_arr_of_obj($array, $sortby, $direction = 'asc')
{
    $sortedArr = array();
    $tmp_Array = array();

    foreach ($array as $k => $v) {
        $tmp_Array[] = strtolower($v->$sortby);
    }

    if ($direction == 'asc') {
        asort($tmp_Array);
    } else {
        arsort($tmp_Array);
    }

    foreach ($tmp_Array as $k => $tmp) {
        $sortedArr[] = $array[$k];
    }

    return $sortedArr;

}

function CustomSort($mainCabsData, $con)
{
    $lsql = 'select * from cabmode';
    $lstmt = $con->query($lsql);
    $lno_of_rows = $lstmt->rowCount();
    $SortedArray = array();
    if ($lno_of_rows > 0) {
        while ($lrow = $lstmt->fetch()) {
            $IsFound = false;
            $lsortedArray = array();
            $CabMode = $lrow["ModeID"];
            foreach ($mainCabsData as $row) {
                $lstdClass = new stdClass;
                $lstdClass = $row;

                if (property_exists($lstdClass, "CabMode")) {
                    if ($lstdClass->CabMode == $CabMode) {
                        $lsortedArray[] = $lstdClass;
                        $IsFound = true;
                    }
                }
            }
            if ($IsFound) {
                $SortedArray[] = sort_arr_of_obj($lsortedArray, 'timeEstimate', 'asc');
            }
        }
    }
    return $SortedArray;
}

function CallTaxiForSureCabsInfo($slat, $slon)
{
    $url = "http://iospush.taxiforsure.com/getNearestDriversForApp/";
    $fields = array(
        'latitude' => $slat,
        'longitude' => $slon,
        'appVersion' => '4.4',
        'density' => 320
    );
    $url = $url . '?' . http_build_query($fields);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $result = curl_exec($ch);
    if ($result === false) {
        echo "Error Number:" . curl_errno($ch) . "<br>";
        echo "Error String:" . curl_error($ch);
    }
    curl_close($ch);
    return $result;
}

function getOlaCabInfo($slat, $slon, $elat, $elon)
{
    $curl = curl_init();
    $url = "https://devapi.olacabs.com/v1/products";
    $fields = array(
        'pickup_lat' => $slat,
        'pickup_lng' => $slon,
        'drop_lat' => $elat,
        'drop_lng' => $elon
    );

    $url = $url . '?' . http_build_query($fields);
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => array('X-APP-TOKEN: 7e22de1177fb4ac4b173a8653c72e1f3')
    ));
    $resp = curl_exec($curl);
    curl_close($curl);
    return $resp;
}

function CallCabAPI($CabID, $From, $To, $slat, $slon, $elat, $elon)
{
    $result = false;
    if ($CabID == 1) // Uber Cabs
    {
        if ($elat == '' && $elon == '') {
            //get Time estimates
            $url = "http://localhost/cmc/cmcservice/uberConnect.php?type=timeestimates";
            $fields = array(
                'lat' => $slat,
                'lon' => $slon,
            );
            $url = $url . '&' . http_build_query($fields);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $result = curl_exec($ch);
            if ($result === false) {
                echo "Error Number:" . curl_errno($ch) . "<br>";
                echo "Error String:" . curl_error($ch);
            }
            curl_close($ch);
        } else {
            //get Time estimates
            $url = "http://localhost/cmc/cmcservice/uberConnect.php?type=priceestimates";
            $fields = array(
                'lat' => $slat,
                'lon' => $slon,
                'elat' => $elat,
                'elon' => $elon,
            );
            $url = $url . '&' . http_build_query($fields);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $result = curl_exec($ch);

            if ($result === false) {
                echo "Error Number:" . curl_errno($ch) . "<br>";
                echo "Error String:" . curl_error($ch);
            }
            curl_close($ch);
        }

    } else if ($CabID == 3) //Meru Cabs
    {
        $url = "http://mobileapp.merucabs.com/NearByCab_Eve/GetNearByCabs.svc/rest/nearby";
        $fields = array(
            'Lat' => $slat,
            'Lng' => $slon,
            'SuggestedRadiusMeters' => 5000,
            'CabMaxCount' => 10,
        );
        $url = $url . '?' . http_build_query($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json'));
        $result = curl_exec($ch);
        if ($result === false) {
            echo "Error Number:" . curl_errno($ch) . "<br>";
            echo "Error String:" . curl_error($ch);
        }
        curl_close($ch);
    } else if ($CabID == 4) //Taxi For Sure
    {
        $url = "http://www.radiotaxiforsure.in/api/consumer-app/fares-new/";
        $fields = array(
            'pickup_latitude' => $slat,
            'pickup_longitude' => $slon,
            'appVersion' => '4.1.1'
        );
        $url = $url . '?' . http_build_query($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $result = curl_exec($ch);
        if ($result === false) {
            echo "Error Number:" . curl_errno($ch) . "<br>";
            echo "Error String:" . curl_error($ch);
        }
        curl_close($ch);
    } else if ($CabID == 6) //Mega Cabs
    {
        $url = "http://175.41.138.72:6060/AccessWs.asmx/GetCabInVicinity";
        $fields = array(
            'lat' => $slat,
            'lng' => $slon,
            'city' => $From,
            'key' => '6eu5tcf',
            'platform' => 'android',
            'privateKey' => '6eu5tcf'
        );
        $url = $url . '?' . http_build_query($fields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $result = curl_exec($ch);
        if ($result === false) {
            echo "Error Number:" . curl_errno($ch) . "<br>";
            echo "Error String:" . curl_error($ch);
        }
        curl_close($ch);
    }
    return $result;
}

function ReturnStdClass($CabNameID, $mainCabsData, $SedanType)
{
    $rstdClass = new stdClass;
    $lstdClass = new stdClass;
    $IsFound = false;
    foreach ($mainCabsData as $row) {
        $lstdClass = $row;
        if (property_exists($lstdClass, "CabNameID")) {
            if ($SedanType != '') {
                if ($lstdClass->CabNameID == $CabNameID) {
                    if (strtolower($lstdClass->CarType) == strtolower($SedanType)) {
                        $IsFound = true;
                        break;
                    }
                }
            } else {
                if ($lstdClass->CabNameID == $CabNameID) {
                    $IsFound = true;
                    break;
                }
            }
        }
    }
    if ($IsFound) {
        $rstdClass = $lstdClass;
    }
    return $rstdClass;
}

function RemoveStdClass($CabNameID, $mainCabsData, $SedanType)
{
    $newArray = array();
    $lstdClass = new stdClass;
    foreach ($mainCabsData as $row) {
        $lstdClass = $row;
        if (property_exists($lstdClass, "CabNameID")) {
            if ($lstdClass->CabNameID != $CabNameID) {
                $newArray[] = $row;
            } else {
                if ($SedanType != '') {
                    if (strtolower($lstdClass->CarType) == strtolower($SedanType)) {

                    } else {
                        $newArray[] = $row;
                    }
                }
            }
        } else {
            $newArray[] = $row;
        }
    }
    return $newArray;
}

function AppendProperties($CabStdClass, $CabsAllData)
{
    foreach ($CabStdClass as $property => $property_value) {
        if (property_exists($CabsAllData, $property)) {

        } else {
            $CabsAllData->$property = $property_value;
        }
    }
    return $CabsAllData;
}

function AppendPropertiesOverwrite($CabStdClass, $CabsAllData)
{
    foreach ($CabStdClass as $property => $property_value) {
        if (property_exists($CabsAllData, $property)) {
            $CabsAllData->$property = $property_value;
        } else {
            $CabsAllData->$property = $property_value;
        }
    }
    return $CabsAllData;
}

function GetTaxiForSureCab($mproduct, $TaxiForSureI, $lat, $lon)
{
    $lVal = 0;
    $mCab = new stdClass();
    foreach ($TaxiForSureI->response_data->data as $item) {
        if (strtolower($item->carType) == strtolower($mproduct)) {
            $diffLat = ($item->latitude - $lat) * ($item->latitude - $lat);
            $diffLng = ($item->longitude - $lon) * ($item->longitude - $lon);
            if ($lVal != 0) {
                $cVal = $diffLat + $diffLng;
                if ($cVal < $lVal) {
                    $mCab->CabName = "TaxiForSure";
                    $mCab->distance = $item->distance;
                    $mCab->uuid = $item->uuid;
                    $mCab->Distance = $item->Distance;
                    $mCab->carType = $item->carType;
                    $mCab->longitude = $item->longitude;
                    $mCab->duration = $item->duration;
                    $mCab->timeEstimate = (int)$item->duration * 60;
                    $mCab->Time = $item->Time;
                    $mCab->latitude = $item->latitude;
                    $mCab->city = $item->city;
                    $lVal = $cVal;
                }
            } else {
                $mCab->CabName = "TaxiForSure";
                $mCab->distance = $item->distance;
                $mCab->uuid = $item->uuid;
                $mCab->Distance = $item->Distance;
                $mCab->carType = $item->carType;
                $mCab->longitude = $item->longitude;
                $mCab->duration = $item->duration;
                $mCab->timeEstimate = (int)$item->duration * 60;
                $mCab->Time = $item->Time;
                $mCab->latitude = $item->latitude;
                $mCab->city = $item->city;
                $lVal = $diffLat + $diffLng;
            }
        }
    }
    return $mCab;
}

function GetMegaCab($MegaProducts, $lat, $lon)
{
    $lVal = 0;
    $mCab = new stdClass();
    if (isset($MegaProducts->data->CAB)) {
        foreach ($MegaProducts->data->CAB as $item) {
            $diffLat = ($item->LAT - $lat) * ($item->LAT - $lat);
            $diffLng = ($item->LONG - $lon) * ($item->LONG - $lon);
            if ($lVal != 0) {
                $cVal = $diffLat + $diffLng;
                if ($cVal < $lVal) {
                    $mCab->Carno = $item->CARNO;
                    $mCab->Car_Rto_No = $item->CAR_RTO_NO;
                    $mCab->Lat = $item->LAT;
                    $mCab->Lng = $item->LONG;
                    $lVal = $cVal;
                }
            } else {
                $mCab->Carno = $item->CARNO;
                $mCab->Car_Rto_No = $item->CAR_RTO_NO;
                $mCab->Lat = $item->LAT;
                $mCab->Lng = $item->LONG;
                $lVal = $diffLat + $diffLng;
            }
        }
    }
    return $mCab;
}

function GetMeruCab($mproduct, $MeruProducts, $lat, $lon)
{
    $lVal = 0;
    $mCab = new stdClass();
    foreach ($MeruProducts["Cablist"] as $item) {
        if (strtolower($item["Brand"]) == strtolower($mproduct)) {
            $diffLat = ($item["Lat"] - $lat) * ($item["Lat"] - $lat);
            $diffLng = ($item["Lng"] - $lon) * ($item["Lng"] - $lon);
            if ($lVal != 0) {
                $cVal = $diffLat + $diffLng;
                if ($cVal < $lVal) {
                    $mCab->CabName = "Meru";
                    $mCab->CarType = $mproduct;
                    $mCab->DeviceId = $item["DeviceId"];
                    $mCab->Lat = $item["Lat"];
                    $mCab->Lng = $item["Lng"];
                    $mCab->OrientationDegrees = $item["OrientationDegrees"];
                    $mCab->TrafficETA = $item["TrafficETA"];
                    $mCab->AddtoETA = $MeruProducts["AddtoETA"];
                    $mCab->ExceedTime = $MeruProducts["ExceedTime"];
                    $lVal = $cVal;
                }
            } else {
                $mCab->CabName = "Meru";
                $mCab->CarType = $mproduct;
                $mCab->DeviceId = $item["DeviceId"];
                $mCab->Lat = $item["Lat"];
                $mCab->Lng = $item["Lng"];
                $mCab->OrientationDegrees = $item["OrientationDegrees"];
                $mCab->TrafficETA = $item["TrafficETA"];
                $mCab->AddtoETA = $MeruProducts["AddtoETA"];
                $mCab->ExceedTime = $MeruProducts["ExceedTime"];
                $lVal = $diffLat + $diffLng;
            }
        }
    }
    return $mCab;
}


$FromCity = $_REQUEST['FromCity'];

$ToCity = '';
if (isset($_REQUEST['ToCity'])) {
    $ToCity = $_REQUEST['ToCity'];
}

$lat = '';
if (isset($_REQUEST['slat'])) {
    $lat = $_REQUEST['slat'];
}

$lon = '';
if (isset($_REQUEST['slon'])) {
    $lon = $_REQUEST['slon'];
}

$elat = '';
if (isset($_REQUEST['elat'])) {
    $elat = $_REQUEST['elat'];
}
$elon = '';
if (isset($_REQUEST['elon'])) {
    $elon = $_REQUEST['elon'];
}

$dist = '';
if (isset($_REQUEST['dist'])) {
    $dist = $_REQUEST['dist'];
}

$stime = '';
if (isset($_REQUEST['stime'])) {
    $stime = $_REQUEST['stime'];
}

$etime = '';
if (isset($_REQUEST['etime'])) {
    $etime = $_REQUEST['etime'];
}

$sql = '';
if ($FromCity != '') {
    if ($ToCity == '') {
        $fromgroup = '';
        $fromstmt = $con->query("SELECT CityGroup FROM groupcities WHERE City = '$FromCity'");
        $cityRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

        if ($cityRows > 0) {
            $fromgroup = $fromstmt->fetchColumn();
        }
        if ($fromgroup != '') {
            $FromCity = $fromgroup;
        }

        $sql = "SELECT a.*,b.CabName, b.CabMobileSite,b.CabMode,b.CabPackageName, (SELECT ModeName FROM cabmode where ModeID = b.CabMode)  As ModeName, ((SELECT SUM(Rating) FROM cabrating where CabDetailID = a.CabDetailID)/(SELECT COUNT(*) FROM cabrating where CabDetailID = a.CabDetailID)) As Rating,(SELECT COUNT(*) FROM cabrating where CabDetailID = a.CabDetailID) AS NoofReviews, '' as timeEstimate, '' as priceEstimate, '' as product_id FROM cabdetails a , cabnames b WHERE a.City = '$FromCity' AND a.Active = 1 and b.CabNameID = a.CabNameID and a.OutStation = 'N'";
    } else {
        if ($FromCity == $ToCity) {
            $fromgroup = '';
            $fromstmt = $con->query("SELECT CityGroup FROM groupcities WHERE City = '$FromCity'");
            $cityRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($cityRows > 0) {
                $fromgroup = $fromstmt->fetchColumn();
            }
            if ($fromgroup != '') {
                $FromCity = $fromgroup;
            }
            $sql = "SELECT a.*,b.CabName, b.CabMobileSite,b.CabMode,b.CabPackageName, (SELECT ModeName FROM cabmode where ModeID = b.CabMode)  As ModeName, ((SELECT SUM(Rating) FROM cabrating where CabDetailID = a.CabDetailID)/(SELECT COUNT(*) FROM cabrating where CabDetailID = a.CabDetailID)) As Rating,(SELECT COUNT(*) FROM cabrating where CabDetailID = a.CabDetailID) AS NoofReviews, '' as timeEstimate, '' as priceEstimate, '' as product_id FROM cabdetails a , cabnames b WHERE a.City = '$FromCity' AND a.Active = 1 and b.CabNameID = a.CabNameID and a.OutStation = 'N'";
        } else {
            $fromgroup = '';
            $fromstmt = $con->query("SELECT CityGroup FROM groupcities WHERE City = '$FromCity'");
            $cityRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($cityRows > 0) {
                $fromgroup = $fromstmt->fetchColumn();
            }

            $togroup = '';
            $tostmt = $con->query("SELECT CityGroup FROM groupcities WHERE City = '$ToCity'");
            $cityRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

            if ($cityRows > 0) {
                $togroup = $tostmt->fetchColumn();
            }

            if ($fromgroup == $togroup) {
                $sql = "SELECT a.*,b.CabName, b.CabMobileSite,b.CabMode,b.CabPackageName, (SELECT ModeName FROM cabmode where ModeID = b.CabMode)  As ModeName, ((SELECT SUM(Rating) FROM cabrating where CabDetailID = a.CabDetailID)/(SELECT COUNT(*) FROM cabrating where CabDetailID = a.CabDetailID)) As Rating,(SELECT COUNT(*) FROM cabrating where CabDetailID = a.CabDetailID) AS NoofReviews, '' as timeEstimate, '' as priceEstimate, '' as product_id FROM cabdetails a , cabnames b WHERE a.City = '$fromgroup' AND a.Active = 1 and b.CabNameID = a.CabNameID and a.OutStation = 'N'";
            } else {
                $sql = "SELECT a.*,b.CabName, b.CabMobileSite,b.CabMode,b.CabPackageName, (SELECT ModeName FROM cabmode where ModeID = b.CabMode)  As ModeName, ((SELECT SUM(Rating) FROM cabrating where CabDetailID = a.CabDetailID)/(SELECT COUNT(*) FROM cabrating where CabDetailID = a.CabDetailID)) As Rating,(SELECT COUNT(*) FROM cabrating where CabDetailID = a.CabDetailID) AS NoofReviews, '' as timeEstimate, '' as priceEstimate, '' as product_id FROM cabdetails a , cabnames b WHERE a.City = '$FromCity' AND a.Active = 1 and b.CabNameID = a.CabNameID and a.OutStation = 'Y'";
            }
        }
    }
} else {
    $sql = "SELECT b.*, '' As Rating, '' as timeEstimate, '' as priceEstimate, '' as product_id FROM cabnames b WHERE  b.CabMode = 1";
}

$stmt = $con->query($sql);
$no_of_rows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($no_of_rows > 0) {
    $ApiCabs = array();
    $mainCabsData = array();
    while ($row = $stmt->fetch()) {
        if ($row['CabMode'] == 1) {
            if (in_array($row['CabNameID'], $ApiCabs) == false) {
                $ApiCabs[] = $row['CabNameID'];
            }
        }
        $CabsAllData = new stdClass;
        $CabsAllData->CabNameID = $row['CabNameID'];
        if (isset($row['CabDetailID'])) {
            $CabsAllData->CabDetailID = $row['CabDetailID'];
        } else {
            $CabsAllData->CabDetailID = "";
        }
        if (isset($row['CabContactNo'])) {
            $CabsAllData->CabContactNo = $row['CabContactNo'];
        } else {
            $CabsAllData->CabContactNo = "";
        }
        if (isset($row['Outstation'])) {
            $CabsAllData->Outstation = $row['Outstation'];
        } else {
            $CabsAllData->Outstation = "";
        }
        if (isset($row['CarType'])) {
            $CabsAllData->CarType = $row['CarType'];
        } else {
            $CabsAllData->CarType = "";
        }
        if ($CabsAllData->Outstation == 'N') {
            $CabsAllData->BaseFare = $row['BaseFare'];
            $CabsAllData->BaseFareKM = $row['BaseFareKM'];
            $CabsAllData->RatePerKMAfterBaseFare = $row['RatePerKMAfterBaseFare'];
            $CabsAllData->NightTimeStartHours = $row['NightTimeStartHours'];
            $CabsAllData->NightTimeEndHours = $row['NightTimeEndHours'];
            $CabsAllData->NightTimeRateMultiplier = $row['NightTimeRateMultiplier'];
            $CabsAllData->City = $row['City'];
            if (IsNullOrEmptyString($CabsAllData->BaseFare) == false) {
                if ($dist != '') {
                    if ($dist < $CabsAllData->BaseFareKM) {
                        $CabsAllData->Cost = floatval($row['BaseFare']);
                    }

                    if ($dist > $CabsAllData->BaseFareKM) {
                        $CabsAllData->Cost = floatval($row['BaseFare']) + ($dist - floatval($CabsAllData->BaseFareKM)) * floatval($row['RatePerKMAfterBaseFare']);
                    }

                    if ($CabsAllData->NightTimeStartHours != "") {
                        if ($stime != '') {
                            $sdatetime = date_create_from_format('d/m/Y H:i:s', $stime);
                            $edatetime = $sdatetime->modify("+" . intval($etime) . " minutes");
                            $sdate = date_format($sdatetime, 'd/m/Y');

                            $sHours = substr($CabsAllData->NightTimeStartHours, 0, 2);
                            $sMinutes = substr($CabsAllData->NightTimeStartHours, -2);
                            $nightstartdatetime = date_create_from_format('d/m/Y H:i', $sdate . ' ' . $sHours . ':' . $sMinutes);


                            $eHours = substr($CabsAllData->NightTimeEndHours, 0, 2);
                            $eMinutes = substr($CabsAllData->NightTimeEndHours, -2);
                            $nightenddatetime = date_create_from_format('d/m/Y H:i', $sdate . ' ' . $eHours . ':' . $eMinutes);

                            $nextdaydatetime = $nightenddatetime->modify("+1 days");

                            if ($CabsAllData->NightTimeStartHours == "0000") {
                                $nextdaystartdatetime = $nightstartdatetime->modify("+1 days");

                                if ($edatetime >= $nextdaystartdatetime && $edatetime <= $nextdaydatetime) {
                                    $CabsAllData->Cost = floatval($CabsAllData->Cost) * floatval($CabsAllData->NightTimeRateMultiplier);
                                }
                            } else {
                                if ($edatetime >= $nightstartdatetime && $edatetime <= $nextdaydatetime) {
                                    $CabsAllData->Cost = floatval($CabsAllData->Cost) * floatval($CabsAllData->NightTimeRateMultiplier);
                                }
                            }
                        }
                    }
                    $CabsAllData->Cost = intval($CabsAllData->Cost);
                    $CabsAllData->low_estimate = $CabsAllData->Cost;
                    $CabsAllData->high_estimate = intval($CabsAllData->Cost * 1.25);
                }
            } else {
                $CabsAllData->BaseFare = "NA";
                if ($dist != '') {
                    $CabsAllData->Cost = "NA";
                    $CabsAllData->low_estimate = "NA";
                    $CabsAllData->high_estimate = "NA";
                }
            }
        } else {
            if (isset($row['40kmor4hours'])) {
                $CabsAllData->fortykmorfourhours = $row['40kmor4hours'];
            } else {
                $CabsAllData->fortykmorfourhours = "";
            }
            if (isset($row['80kmor8hours'])) {
                $CabsAllData->eightykmoreighthours = $row['80kmor8hours'];
            } else {
                $CabsAllData->eightykmoreighthours = "";
            }
            if (isset($row['OvernightCharges'])) {
                $CabsAllData->OvernightCharges = $row['OvernightCharges'];
            } else {
                $CabsAllData->OvernightCharges = "";
            }
        }

        $CabsAllData->CabName = $row['CabName'];
        $CabsAllData->CabMobileSite = $row['CabMobileSite'];
        $CabsAllData->CabMode = $row['CabMode'];
        $CabsAllData->CabPackageName = $row['CabPackageName'];
        $CabsAllData->Rating = $row['Rating'];
        if (isset($row['NoofReviews'])) {
            $CabsAllData->NoofReviews = $row['NoofReviews'];
        } else {
            $CabsAllData->NoofReviews = "";
        }
        $mainCabsData[] = $CabsAllData;
    }

    if (count($ApiCabs) > 0) {
        foreach ($ApiCabs as $cabItem) {
            $CabStdClass = new stdClass;

            if ($cabItem == 1) // Uber Cabs
            {



                $cabDataPrice = CallCabAPI($cabItem, $FromCity, $ToCity, $lat, $lon, $elat, $elon);
                $uberPriceEstimate = json_decode($cabDataPrice);

                $cabDataTime = CallCabAPI($cabItem, $FromCity, '', $lat, $lon, '', '');
                $uberProductsTime = json_decode($cabDataTime);
                $arrTimeEstimate = [];

                foreach ($uberProductsTime->times as $time) {
                    if (isset($arrTimeEstimate[$time->display_name]) ) {
                        if ($arrTimeEstimate[$time->display_name]->estimate > $time->estimate) {
                            $arrTimeEstimate[$time->display_name] = $time;
                        }
                    } else {
                        $arrTimeEstimate[$time->display_name] = $time;
                    }
                }

                $finalCabsArray = [];

                $getMainCabsDataUber['UberBLACK'] = ReturnStdClass($cabItem, $mainCabsData, 'UberBLACK');
                $getMainCabsDataUber['uberGO'] = ReturnStdClass($cabItem, $mainCabsData, 'uberGO');
                $getMainCabsDataUber['uberX'] = ReturnStdClass($cabItem, $mainCabsData, 'uberX');
                $getMainCabsDataUber['uberXL'] = ReturnStdClass($cabItem, $mainCabsData, 'uberXL');

                $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, 'UberBLACK');
                $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, 'uberGO');
                $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, 'uberX');
                $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, 'uberXL');

                foreach ($uberPriceEstimate->prices as $price) {

                    if ($arrTimeEstimate[$price->display_name]->product_id = $price->product_id){
                        if ($price->display_name !='uberAuto') {
                            $CabStdClass = $getMainCabsDataUber[$price->display_name];

                            $CabsAllData = new stdClass;
                            $CabsAllData->CabName = "Uber";
                            $CabsAllData->Rating = number_format($CabStdClass->Rating, 1, '.', '');
                            $CabsAllData->NoofReviews = $CabStdClass->NoofReviews;
                            $CabsAllData->CabMode = $CabStdClass->CabMode;
                            $CabsAllData->high_estimate = $price->high_estimate;
                            $CabsAllData->duration = $price->duration;
                            $CabsAllData->productId = $price->product_id;
                            $CabsAllData->low_estimate = $price->low_estimate;
                            $CabsAllData->surge_multiplier = $price->surge_multiplier;
                            $CabsAllData->CarType = $price->display_name;
                            $CabsAllData->timeEstimate = $arrTimeEstimate[$price->display_name]->estimate;
                            $mainCabsData[] = $CabsAllData;
                        }
                    }
                }


            } else if ($cabItem == 2) // Ola Cabs
            {
                $olaApiData = json_decode(getOlaCabInfo($lat, $lon, $elat, $elon));

                $getMainCabsData = [];
                $getMainCabsData['Mini'] = ReturnStdClass($cabItem, $mainCabsData, 'Mini');
                $getMainCabsData['Sedan'] = ReturnStdClass($cabItem, $mainCabsData, 'Sedan');
                $getMainCabsData['Prime'] = ReturnStdClass($cabItem, $mainCabsData, 'Prime');
                $getMainCabsData['Delhi to NCR'] = ReturnStdClass($cabItem, $mainCabsData, 'Delhi to NCR');
                $getMainCabsData['Within Delhi'] = ReturnStdClass($cabItem, $mainCabsData, 'Within Delhi');

                $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, 'Mini');
                $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, 'Sedan');
                $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, 'Prime');
                $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, 'Delhi to NCR');
                $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, 'Within Delhi');

                if (!empty($olaApiData->categories)) {
                    $rideEstimate = [];

                    if (!empty($olaApiData->ride_estimate)) {
                        foreach ($olaApiData->ride_estimate as $estimate) {
                            $rideEstimate[$estimate->category] = $estimate;
                        }
                    }

                    foreach ($olaApiData->categories as $value) {

                        if (isset($rideEstimate[strtolower($value->display_name)])) {
                            $price_estimate = $rideEstimate[strtolower($value->display_name)];
                        }else{
                            $price_estimate = $rideEstimate['mini'];
                        }

                        if ($value->display_name != 'Auto' && $value->eta != -1) {
                            $CabsAllData = new stdClass;
                            $CabsAllData->CabName = 'Ola';
                            $CabsAllData->CabNameID = 2;
                            $CabsAllData->CarType = $value->display_name;
                            $CabsAllData->timeEstimate = ($value->eta * 60);
                            $CabsAllData->BaseFare = $value->fare_breakup[0]->base_fare;
                            $CabsAllData->BaseFareKM = $value->fare_breakup[0]->minimum_distance;
                            $CabsAllData->low_estimate = $price_estimate->amount_min;
                            $CabsAllData->high_estimate = $price_estimate->amount_max;
                            $CabsAllData->RatePerKMAfterBaseFare = $value->fare_breakup[0]->cost_per_distance;
                            $CabsAllData->CabMobileSite = 'm.olacab.com';
                            $CabsAllData->CabMode = 1;
                            $CabsAllData->CabPackageName = 'com.olacabs.customer';
                            $CabsAllData->Rating = $getMainCabsData[$value->display_name]->Rating;
                            $CabsAllData->NoofReviews = $getMainCabsData[$value->display_name]->NoofReviews;

                            $mainCabsData[] = $CabsAllData;
                            $CabsAllData = new stdClass;

                        }
                    }
                }


               //Old Implementation
                /*if ($olaApiData->status == 'SUCCESS' && ($olaApiData->cab_availability)) {

                    $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, 'Mini');
                    $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, 'Sedan');
                    $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, 'Prime');

                    foreach ($olaApiData->cab_categories as $value) {
                        if ($value->display_name != 'Auto' && ($value->cab_availability)) {

                            $CabStdClass = ReturnStdClass($cabItem, $mainCabsData, $value->display_name);
                            $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, $value->display_name);

                            if ($CabStdClass->CabName !=null && $CabStdClass->CabNameID !=null){
                                $CabsAllData = new stdClass;
                                $CabsAllData->CabName = $CabStdClass->CabName;
                                $CabsAllData->CabNameID = $CabStdClass->CabNameID;
                                $CabsAllData->CarType = $value->display_name;
                                $CabsAllData->timeEstimate = ($value->duration->value * 60);
                                $CabsAllData->BaseFare = $CabStdClass->BaseFare;
                                $CabsAllData->BaseFareKM = $CabStdClass->BaseFareKM;
                                $CabsAllData->low_estimate = $CabStdClass->low_estimate;
                                $CabsAllData->high_estimate = $CabStdClass->high_estimate;
                                $CabsAllData->RatePerKMAfterBaseFare = $CabStdClass->RatePerKMAfterBaseFare;
                                $CabsAllData->CabMobileSite = $CabStdClass->CabMobileSite;
                                $CabsAllData->CabMode = $CabStdClass->CabMode;
                                $CabsAllData->CabPackageName = $CabStdClass->CabPackageName;
                                $CabsAllData->Rating = $CabStdClass->Rating;
                                $CabsAllData->NoofReviews = $CabStdClass->NoofReviews;

                                $mainCabsData[] = $CabsAllData;
                                $CabsAllData = new stdClass;
                            }
                        }
                    }
                }*/
            } else if ($cabItem == 3) //Meru Cabs
            {
                $cabData = CallCabAPI($cabItem, '', '', $lat, $lon, '', '');
                $MeruProducts = json_decode(json_decode($cabData), true);
                if (isset($MeruProducts["Cablist"])) {
                    $product = $MeruProducts["Product"];
                    $sproduct = explode("|", $product);

                    foreach ($sproduct as $mproduct) {
                        if (strtolower($mproduct) != strtolower("MeruEve")) {
                            $CabsAllData = GetMeruCab($mproduct, $MeruProducts, $lat, $lon);
                            if (strtolower($mproduct) == strtolower("Genie")) {
                                $CabStdClass = ReturnStdClass($cabItem, $mainCabsData, $mproduct);
                                $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, $mproduct);
                            } else {
                                $CabStdClass = ReturnStdClass($cabItem, $mainCabsData, 'Sedan');
                                $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, 'Sedan');
                            }
                            $total = count((array)$CabStdClass);
                            if ($total > 0) {
                                $CabsAllData = AppendPropertiesOverwrite($CabStdClass, $CabsAllData);
                                if (strtolower($mproduct) == strtolower("Genie")) {
                                    $CabsAllData->CarType = $mproduct;
                                }
                                if (property_exists($CabsAllData, "Lat") && property_exists($CabsAllData, "Lng")) {
                                    $duration = getDuration($lat, $lon, $CabsAllData->Lat, $CabsAllData->Lng);
                                    $CabsAllData->timeEstimate = $duration;
                                    $mainCabsData[] = $CabsAllData;
                                }
                            }
                        }
                    }
                }
            } else if ($cabItem == 4) //Taxi For Sure
            {

                $cabIData = CallTaxiForSureCabsInfo($lat, $lon);
                $TaxiForSureI = json_decode($cabIData);
                $cabData = CallCabAPI($cabItem, '', '', $lat, $lon, '', '');
                $TaxiForSure = json_decode($cabData, true);

                $cabTypes = array();
                if (isset($TaxiForSureI->response_data)) {
                    foreach ($TaxiForSureI->response_data->carTypes as $item) {
                        $cabTypes[] = $item->carType;
                    }
                    foreach ($cabTypes as $mproduct) {
                        $CabStdClass = ReturnStdClass($cabItem, $mainCabsData, $mproduct);
                        $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, $mproduct);
                        $CabsAllData = new stdClass;
                        $CabsAllData = GetTaxiForSureCab($mproduct, $TaxiForSureI, $lat, $lon);
                        $CabsAllData = AppendProperties($CabStdClass, $CabsAllData);

                        if (isset($TaxiForSure["response_data"])) {
                            $dayTimings = $TaxiForSure["response_data"]["day"]["timings"];
                            $stDateTime = explode(" ", $stime);
                            $stTimeData = explode(":", $stDateTime[1]);
                            $astTime = (int)$stTimeData[0];

                            $aTime = 0;
                            $bTime = 0;
                            $stdayTimings = explode("-", $dayTimings);
                            if (strpos($stdayTimings[0], 'AM') !== false) {
                                $aTime = (int)str_replace('AM', '', $stdayTimings[0]);
                            }
                            if (strpos($stdayTimings[1], 'PM') !== false) {
                                $bTime = (int)str_replace('PM', '', $stdayTimings[1]);
                                if ($bTime != 12) {
                                    $bTime = $bTime + 12;
                                }
                            }
                            $IsDayFares = false;
                            if ($astTime > $aTime && $astTime < $bTime) {
                                $IsDayFares = true;
                            }
                            if ($IsDayFares) {
                                foreach ($TaxiForSure["response_data"]["day"]["at-km"] as $item) {

                                    if (strtolower($item["car_type"]) == strtolower($mproduct)) {

                                    }
                                }
                            } else {
                                foreach ($TaxiForSure["response_data"]["night"]["at-km"] as $item) {

                                }
                            }
                        }
                        $mainCabsData[] = $CabsAllData;
                    }
                }
            } else if ($cabItem == 6) //Mega Cabs
            {

                $cabData = CallCabAPI($cabItem, $FromCity, '', $lat, $lon, '', '');
                $CabStdClass = ReturnStdClass($cabItem, $mainCabsData, '');
                $mainCabsData = RemoveStdClass($cabItem, $mainCabsData, '');
                $MegaCabProducts = json_decode($cabData);
                if (isset($MegaCabProducts->data)) {
                    $CabsAllData = new stdClass;
                    $CabsAllData = GetMegaCab($MegaCabProducts, $lat, $lon);
                    $CabsAllData = AppendProperties($CabStdClass, $CabsAllData);
                    if (isset($CabsAllData->Lat) && isset($CabsAllData->Lng)) {
                        $duration = getDuration($lat, $lon, $CabsAllData->Lat, $CabsAllData->Lng);
                        $CabsAllData->timeEstimate = $duration;
                    } else {
                        $CabsAllData->timeEstimate = "";
                    }
                    $mainCabsData[] = $CabsAllData;
                }
            }
        }
    }
    echo json_encode($mainCabsData);
} else {
    echo "Error";
}
