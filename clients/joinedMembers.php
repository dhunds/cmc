<?php
include 'connection.php';

$members = [];

if (isset($_REQUEST['cabId']) && $_REQUEST['cabId'] !='') {
    $sql = "SELECT MemberName, MemberNumber FROM acceptedrequest WHERE CabId='".$_REQUEST['cabId']."' AND Status !='Dropped'";
    $stmt = $con->query($sql);
    $membersJoined = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

    if ($membersJoined) {
        $stmt->execute();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>

<html lang="en">
<head>
    <title>JavaScript - Popup example</title>
<body>
<table width="100%">
    <tr bgcolor="#18A5DE">
        <td style="width: 48%; color: #fff;padding:8px;"><b>Mobile Number</b></td>
        <td style="width: 48%; color: #fff;padding:8px;"><b>Name</b></td>
    </tr>
    <?php
            foreach($members as $value){
                echo '<tr>
                            <td style="padding:8px;">'.$value['MemberNumber'].'</td>
                            <td style="padding:8px;">'.$value['MemberName'].'</td>
                        </tr>';
            }
    ?>
</table>

</body>
</html>

