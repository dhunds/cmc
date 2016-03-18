<?php
if (isset($_POST['submit']) && (count($_FILES) > 0 || $_POST['clubNames'] != '') && $_POST['mobNumber'] != '') {
    $_POST['mobNumber'] = '0091' . substr(trim($_POST['mobNumber']), -10);
    $MobileNumber = $_POST['mobNumber'];
    $FullName = $_POST['name'];
    $Email = $_POST['email'];
    $Platform = 'A';
    $deviceId = 'admin';

    $stmt = $con->query("select FullName, MobileNumber FROM registeredusers WHERE trim(MobileNumber)='" . $MobileNumber . "'");
    $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($found < 1) {
        $sql = "INSERT INTO registeredusers(FullName, MobileNumber, Email, Platform, DeviceToken, CreatedOn, isAdminType) VALUES ('$FullName', '$MobileNumber','$Email','$Platform','$deviceId',now(),1)";
        $stmt = $con->prepare($sql);
        $res = $stmt->execute();

        $sql = "INSERT INTO userprofileimage(MobileNumber, imagename) VALUES ('$MobileNumber','')";
        $stmt = $con->prepare($sql);
        $res2 = $stmt->execute();
    }
    $i = 0;
    if (($found > 0) || ($res == true)) {
        $clubs = [];

        if (is_uploaded_file($_FILES['clubs']['tmp_name'])) {
            $file = fopen($_FILES['clubs']['tmp_name'], "r");

            while (!feof($file)) {
                $club = fgetcsv($file);
                $clubs[] = $club[0];
            }
            fclose($file);
        } else {
            $clubs = explode(PHP_EOL, $_POST['clubNames']);
        }

        foreach ($clubs as $val) {
            $val = trim($val);
            if ($val != '') {
                $stmt = $con->query("Select * From userpoolsmaster WHERE PoolName='$val' AND OwnerNumber = '$MobileNumber'");
                $found = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

                if ($found > 0) {
                    echo "Club Name '".$val . "' Already Exist<br />";
                } else {
                    $val = preg_replace( '/[^[:print:]]/', '',$val);
                    $sql = "INSERT INTO userpoolsmaster(OwnerNumber, PoolName, Active) VALUES ('$MobileNumber', '$val','1')";
                    $stmt = $con->prepare($sql);
                    $res2 = $stmt->execute();
                    if ($res2 == true) {
                        $i++;
                    }
                }
            }
        }
    }
    echo $i . ' Clubs added Successfully';
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

     #map {
         width: 600px;
         height: 400px;
     }

</style>
<div>
    <div>
        <div style="width:100%;height:100%;float:left;">
            <h2 class="headingText">Add Clubs</h2>

            <form method="post" action="" enctype="multipart/form-data">
                <div style="margin-left: 5px;">
                    <div class="divLeft bluetext">&nbsp;&nbsp;Club Name:</div>
                    <div class="divRight bluetext"><input type="text" name="clubNames"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <div class="divLeft bluetext">&nbsp;&nbsp;Map:</div>
                    <div class="divRight bluetext" id="map"></div>
                    <div style="clear:both;"></div>
                    <br/>

                    <!-- <div class="divLeft bluetext">&nbsp;&nbsp;Email:</div>
                     <div class="divRight bluetext"><input type="text" name="email"></div>
                     <div style="clear:both;"></div>
                     <br/>

                     <div class="divLeft bluetext">&nbsp;&nbsp;Club Name:</div>
                     <div class="divRight bluetext">
                         <input type="text" name="clubNames">
                     <div style="clear:both;"></div>
                     <br/>
                         <div class="divLeft bluetext">&nbsp;&nbsp;Club Name:</div>
                         <div class="divRight bluetext">
                             <input type="text" name="clubNames">
                             <div style="clear:both;"></div>
                             <br/>
                     <div class="divLeft bluetext">&nbsp;&nbsp;Map:</div>
                     <div class="divRight bluetext">
                     Google Map
                     <div style="clear:both;"></div>
                     <br/>

                 <!--<div class="divLeft bluetext" style="padding-left: 15%">OR</div>
                     <div style="clear:both;"></div>
                     <br/>

                     <div class="divLeft bluetext">&nbsp;&nbsp;File (csv):</div>
                     <div class="divRight bluetext"><input type="file" name="clubs"></div>
                     <div style="clear:both;"></div>
                     <br/>-->

                    <div class="divLeft bluetext">&nbsp;&nbsp;<input type="submit" name="submit" value="Create Club"
                                                                     class="cBtn"></div>
                    <div class="divRight bluetext"></div>

                </div>
            </form>

            <br/>
        </div>

        <div style="clear:both;"></div>
    </div>
</div>
<script>
    function initMap() {
        var mapDiv = document.getElementById('map');
        var map = new google.maps.Map(mapDiv, {
            center: {lat: 28.4940472, lng: 77.0820822},
            zoom: 9
        });
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?callback=initMap"
        async defer></script>