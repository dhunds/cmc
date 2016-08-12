<?php
include 'connection.php';

$members = [];

if (isset($_POST['cabId']) && $_POST['cabId'] !='') {
    $sql = "SELECT MemberName, MemberNumber FROM acceptedrequest WHERE CabId='".$_POST['cabId']."' AND Status !='Dropped'";
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
<table>
    <tr>
        <td><b>Mobile Number</b></td>
        <td><b>Name</b></td>
    </tr>
    <?php
            foreach($members as $value){
                echo '<tr>
                            <td>'.$value['MemberNumber'].'</td>
                            <td>&nbsp;&nbsp;'.$value['MemberName'].'</td>
                        </tr>';
            }
    ?>
</table>

</body>
</html>

