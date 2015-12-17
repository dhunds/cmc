<?php
include('connection.php');
include('functions.php');
$dir = BASEURL.'/ProfileImages/';
?>
<h3 class="headingText">Members Profile</h3>
<div class="articleBorder">
    <form action="mProfile.php" method="POST">

        <table width="100%">
            <tr>
                <td width="100px" class="bluetext">Name</td>
                <td><input type="text" name="name" id="name" class="textfield"></td>
                <td><input type="submit" class="cBtn" name="nameview" id="nameview" value="View"></td>
                <?php
                $sql = "SELECT count(*) From registeredusers";
                $stmtI = $con->query($sql);
                $NoofUsers = $stmtI->fetchColumn();

                $sql = "SELECT count(*) From registeredusers where  Createdon >= DATE(NOW()) - INTERVAL 7 DAY";
                $stmtL = $con->query($sql);
                $NoofUsersPastSevenDays = $stmtL->fetchColumn();

                $sql = "SELECT count(*) From registeredusers where  Createdon >= DATE(NOW()) - INTERVAL 24 HOUR";
                $stmtO = $con->query($sql);
                $NoofUsersPastOneDays = $stmtO->fetchColumn();

                echo "<td align='center'>Total No. of Users <br/> $NoofUsers<td>";
                echo "<td align='center'>Users added in Last One Week <br/> $NoofUsersPastSevenDays<td>";
                echo "<td align='center'>Users added in Last 24 Hour <br/> $NoofUsersPastOneDays<td>";
                echo "<td><a href='exportProfile.php'><img src='images/icon_excel.gif'  border='0'
                width='25' height='25'/></a></td>";
                ?>
            </tr>
        </table>

        <?php
        if (isset($_POST['nameview'])) {
            $cname = $_POST['name'];

            $sql = "SELECT * From registeredusers where FullName like '%$cname%'";

            $stmt = $con->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<div class='pure-g' style='font-size:13px; font-weight:bold; margin-top:10px;'>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Name</p></div>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Number</p></div>";
            //echo "<div class='pure-u-4-24'><p class='tHeading'>Image</p></div>";
            echo "<div class='pure-u-4-24'><p class='tHeading'>EmailID</p></div>";
            echo "<div class='pure-u-1-24'><p class='tHeading'>Gender</p></div>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Date Of Birth</p></div>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Push Notification</p></div>";
            echo "<div class='pure-u-3-24'><p class='tHeading'>Device Token</p></div>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Platform</p></div>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Last Login DateTime</p></div>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Created On</p></div>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Action</p></div>";
            echo "</div>";
            foreach ($result as $row) {
                echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
                echo "<div class='pure-u-2-24'><p>" . $row['FullName'] . "</p></div>";
                echo "<div class='pure-u-2-24'><p>" . $row['MobileNumber'] . " </p></div>";
                //	echo "<div class='pure-u-4-24'><p><img src=" . $dir . $row['imagename']. " alt='' width='80' /></p></div>";
                echo "<div class='pure-u-4-24'><p>" . $row['Email'] . "</p></div>";
                echo "<div class='pure-u-1-24'><p>" . $row['Gender'] . "</p></div>";
                echo "<div class='pure-u-2-24'><p>" . $row['DOB'] . " </p></div>";
                echo "<div class='pure-u-2-24'><p>" . $row['PushNotification'] . " </p></div>";
                echo "<div class='pure-u-3-24' style='overflow:scroll;'><p>" . $row['DeviceToken'] . "</p></div>";
                echo "<div class='pure-u-2-24' style='text-align:center;'><p>" . $row['Platform'] . " </p></div>";
                echo "<div class='pure-u-2-24'><p>" . $row['LastLoginDateTime'] . " </p></div>";
                echo "<div class='pure-u-2-24'><p>" . $row['CreatedOn'] . " </p></div>";
                echo "<div class='pure-u-2-24'><p><a href='mEditProfile.php?id=" . $row['MobileNumber'] . "'>Edit</a></p></div>";
                echo "</div>";
            }
        } else {

            $sql = "SELECT * From registeredusers";

            $stmt = $con->prepare($sql);
            $stmt->execute();

            $rowCount = (int)$stmt->rowCount();
            $totalpages = ceil($rowCount / PAGESIZE);

            if (isset($_REQUEST['page']) && $_REQUEST['page'] != '') {
                $page = $_REQUEST['page'];
            } else {
                $page = 1;
            }

            $start = ($page - 1) * PAGESIZE;
            $sql .= " LIMIT $start , " . PAGESIZE;

            $stmt = $con->prepare($sql);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<div class='pure-g' style='font-size:13px; font-weight:bold; margin-top:10px;'>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Name</p></div>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Number</p></div>";
            //echo "<div class='pure-u-4-24'><p class='tHeading'>Image</p></div>";
            echo "<div class='pure-u-4-24'><p class='tHeading'>EmailID</p></div>";
            echo "<div class='pure-u-1-24'><p class='tHeading'>Gender</p></div>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Date Of Birth</p></div>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Push Notification</p></div>";
            echo "<div class='pure-u-3-24'><p class='tHeading'>Device Token</p></div>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Platform</p></div>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Last Login DateTime</p></div>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Created On</p></div>";
            echo "<div class='pure-u-2-24'><p class='tHeading'>Action</p></div>";
            echo "</div>";
            foreach ($result as $row) {
                echo "<div class='pure-g pure-g1' style='font-size:13px;'>";
                echo "<div class='pure-u-2-24'><p>" . $row['FullName'] . "</p></div>";
                echo "<div class='pure-u-2-24'><p>" . $row['MobileNumber'] . " </p></div>";
                //	echo "<div class='pure-u-4-24'><p><img src=" . $dir . $row['imagename']. " alt='' width='80' /></p></div>";
                echo "<div class='pure-u-4-24'><p>" . $row['Email'] . "</p></div>";
                echo "<div class='pure-u-1-24'><p>" . $row['Gender'] . "</p></div>";
                echo "<div class='pure-u-2-24'><p>" . $row['DOB'] . " </p></div>";
                echo "<div class='pure-u-2-24'><p>" . $row['PushNotification'] . " </p></div>";
                echo "<div class='pure-u-3-24' style='overflow:scroll;'><p>" . $row['DeviceToken'] . "</p></div>";
                echo "<div class='pure-u-2-24' style='text-align:center;'><p>" . $row['Platform'] . " </p></div>";
                echo "<div class='pure-u-2-24'><p>" . $row['LastLoginDateTime'] . " </p></div>";
                echo "<div class='pure-u-2-24'><p>" . $row['CreatedOn'] . " </p></div>";
                echo "<div class='pure-u-2-24'><p><a href='mEditProfile.php?id=" . $row['MobileNumber'] . "'>Edit</a></p></div>";
                echo "</div>";
            }
        }
        echo pagination_search($totalpages, $page);
?>
</div>
</form>
</div>