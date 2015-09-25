<?php
$sql = "SELECT count(*) totalRides,
    sum(case when CabStatus = 'A' then 1 else 0 end) activeRides,
    sum(case when CabStatus = 'C' then 1 else 0 end) completedRides,
    sum(case when CabStatus = 'I' then 1 else 0 end) archievedRides
FROM cabopen
WHERE 1";
$condition = '';

if (isset($_POST['submit'])) {
    if ($_POST['from'] != '' && $_POST['to'] != '') {
        $condition .= " AND TravelDate BETWEEN '" . $_POST['from'] . "' AND '" . $_POST['to'] . "'";
    } elseif ($_POST['from'] != '') {
        $condition .= " AND TravelDate >= '" . $_POST['from'] . "'";
    } elseif ($_POST['to'] != '') {
        $condition .= " AND TravelDate <= '" . $_POST['to'] . "'";
    }
}
$sql .= $condition;
$stmt = $con->prepare($sql);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $con->prepare("SELECT COUNT(*) As NoOfLogInUsers FROM registeredusers WHERE DATE_FORMAT(LastLoginDateTime,'%d/%m/%Y')= DATE_FORMAT(NOW(),'%d/%m/%Y')");
$stmt->execute();
$activeUsers = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $con->prepare("SELECT COALESCE(SUM(totalCredits),0) As totalCredits FROM registeredusers");
$stmt->execute();
$Credits = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<link rel="stylesheet" type="text/css" media="all" href="Calendar/calendar-blue.css"
      title="win2k-cold-1">
<script type="text/javascript" src="Calendar/calendar.js"></script>
<script type="text/javascript" src="Calendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="Calendar/calendar-setup.js"></script>
<div class="articleBorder">
    <div>
        <div style="width:60%;float:left;height:200px;overflow:auto; min-height: 400px;">
            <h4 class="headingText">Ride Summary</h4>

            <form method="post" action="">
                <div>
                    <div style="float:left;width:45%;" class="bluetext">Date From: <input type="text" name="from"
                                                                                          value="<?= $_POST['from'] ?>"
                                                                                          id="from"> <img
                            src="images/calendar.png" id="dtFrom">&nbsp;
                        <script type="text/javascript">
                            Calendar.setup({
                                inputField: 'from',
                                ifFormat: "%d/%m/%Y",
                                button: "dtFrom",
                                singleClick: true
                            });
                        </script>
                    </div>
                    <div style="float:left;width:55%;" class="bluetext">To: <input type="text" name="to"
                                                                                   value="<?= $_POST['to'] ?>" id="to">
                        <img src="images/calendar.png" id="dtto">&nbsp;&nbsp;&nbsp;
                        <script type="text/javascript">
                            Calendar.setup({
                                inputField: 'to',
                                ifFormat: "%d/%m/%Y",
                                button: "dtto",
                                //align          :    "Tl",
                                singleClick: true
                            });
                        </script>
                        <input type="submit" name="submit" value="Submit" class="cBtn"></div>
                    <div style="clear:both;"></div>
            </form>
        </div>
        <br/>

        <div style="border:1px solid #ddd;">
            <div style="float:left;width:49%;"><a
                    href="mpools.php?Mode=A&from=<?= $_POST['from'] ?>&to=<?= $_POST['to'] ?>">Active Rides</a></div>
            <div style="float:left;width:49%;"><?= $result['activeRides']; ?></div>
            <div style="clear:both;"></div>
        </div>
        <div style="border:1px solid #ddd;border-top:none;">
            <div style="float:left;width:49%;"><a
                    href="mpools.php?Mode=C&from=<?= $_POST['from'] ?>&to=<?= $_POST['to'] ?>">Completed Rides</a></div>
            <div style="float:left;width:49%;"><?= $result['completedRides']; ?></div>
            <div style="clear:both;"></div>
        </div>
        <div style="border:1px solid #ddd;border-top:none;">
            <div style="float:left;width:49%;"><a
                    href="mpools.php?Mode=I&from=<?= $_POST['from'] ?>&to=<?= $_POST['to'] ?>">Archived Rides </a></div>
            <div style="float:left;width:49%;"><?= $result['archievedRides']; ?></div>
            <div style="clear:both;"></div>
        </div>
        <div style="border:1px solid #ddd;border-top:none;">
            <div style="float:left;width:49%;"><a
                    href="mpools.php?Mode=S&from=<?= $_POST['from'] ?>&to=<?= $_POST['to'] ?>">Total Rides </a></div>
            <div style="float:left;width:49%;"><?= $result['totalRides']; ?></div>
            <div style="clear:both;"></div>
        </div>
    </div>
    <div style="width:38%;float:right;overflow:auto;height:160px;">
        <h4 class="headingText">Real Time</h4>

        <div style="border:1px solid #ddd;border-top:none;">
            <div style="float:left;width:49%;"><a href="mpools.php?Mode=LLU">Last Logged In Users</a></div>
            <div
                style="float:left;width:49%;"><?= ($activeUsers['NoOfLogInUsers']) ? $activeUsers['NoOfLogInUsers'] : '0'; ?></div>
            <div style="clear:both;"></div>
        </div>
        <div style="border:1px solid #ddd;border-top:none;">
            <div style="float:left;width:49%;"><a href="javascript:;">Total Credits in User Account</a></div>
            <div
                style="float:left;width:49%;">Rs. <?= $Credits['totalCredits']; ?></div>
            <div style="clear:both;"></div>
        </div>
    </div>
    <div style="clear:both;"></div>
</div>
</div>
