<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

$dsn = 'mysql:host=localhost;dbname=cmcdev';
$us = 'root';
$pa = 'root';

try {
    $con = new PDO($dsn, $us, $pa);
} catch (PDOException $e) {
    $error_message = $e->getMessage();
    //include('../errors/err.php');
    echo $error_message;
    exit();
}

$i=0;

//Poolmaster

$stmt = $con->query("SELECT DISTINCT OwnerNumber FROM userpoolsmaster");
$totalRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($totalRows > 0) {
    while ($row = $stmt->fetch()) {
        $stmt1 = $con->query("SELECT userId FROM registeredusers WHERE trim(MobileNumber)='".trim($row['OwnerNumber'])."'");

        $userId = $stmt1->fetchColumn();

        if ($userId) {
            $stmt2 = $con->prepare("UPDATE userpoolsmaster SET ownerUserId=".$userId." WHERE OwnerNumber='".trim($row['OwnerNumber'])."'");
            $stmt2->execute();
            $i++;
        }
    }
}
//poolslave


$stmt = $con->query("SELECT DISTINCT MemberNumber FROM userpoolsslave");
$totalRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($totalRows > 0) {
    while ($row = $stmt->fetch()) {
        $stmt1 = $con->query("SELECT userId FROM registeredusers WHERE trim(MobileNumber)='".trim($row['MemberNumber'])."'");

        $userId = $stmt1->fetchColumn();

        if ($userId) {
            $stmt2 = $con->prepare("UPDATE userpoolsslave SET memberUserId=".$userId." WHERE MemberNumber='".trim($row['MemberNumber'])."'");
            $stmt2->execute();
            $i++;
        }
    }
}

// User Image

$stmt = $con->query("SELECT DISTINCT MobileNumber FROM userprofileimage");
$totalRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($totalRows > 0) {
    while ($row = $stmt->fetch()) {
        $stmt1 = $con->query("SELECT userId FROM registeredusers WHERE trim(MobileNumber)='".trim($row['MobileNumber'])."'");

        $userId = $stmt1->fetchColumn();

        if ($userId) {
            $stmt2 = $con->prepare("UPDATE userprofileimage SET userId=".$userId." WHERE MobileNumber='".trim($row['MobileNumber'])."'");
            $stmt2->execute();
            $i++;
        }
    }
}

// Save vehicle detail

$stmt = $con->query("SELECT DISTINCT mobileNumber FROM userVehicleDetail");
$totalRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($totalRows > 0) {
    while ($row = $stmt->fetch()) {
        $stmt1 = $con->query("SELECT userId FROM registeredusers WHERE trim(MobileNumber)='".trim($row['mobileNumber'])."'");

        $userId = $stmt1->fetchColumn();

        if ($userId) {
            $stmt2 = $con->prepare("UPDATE userVehicleDetail SET userId=".$userId." WHERE mobileNumber='".trim($row['mobileNumber'])."'");
            $stmt2->execute();
            $i++;
        }
    }
}


// Credits

$stmt = $con->query("SELECT DISTINCT mobileNumber FROM credits");
$totalRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($totalRows > 0) {
    while ($row = $stmt->fetch()) {
        $stmt1 = $con->query("SELECT userId FROM registeredusers WHERE trim(MobileNumber)='".trim($row['mobileNumber'])."'");

        $userId = $stmt1->fetchColumn();

        if ($userId) {
            $stmt2 = $con->prepare("UPDATE credits SET userId=".$userId." WHERE mobileNumber='".trim($row['mobileNumber'])."'");
            $stmt2->execute();
            $i++;
        }
    }
}

// User Offers

$stmt = $con->query("SELECT DISTINCT mobileNumber FROM userOffers");
$totalRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($totalRows > 0) {
    while ($row = $stmt->fetch()) {
        $stmt1 = $con->query("SELECT userId FROM registeredusers WHERE trim(MobileNumber)='".trim($row['mobileNumber'])."'");

        $userId = $stmt1->fetchColumn();

        if ($userId) {
            $stmt2 = $con->prepare("UPDATE userOffers SET userId=".$userId." WHERE mobileNumber='".trim($row['mobileNumber'])."'");
            $stmt2->execute();
            $i++;
        }
    }
}


// Notification Part 1

$stmt = $con->query("SELECT DISTINCT SentMemberNumber FROM notifications");
$totalRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($totalRows > 0) {
    while ($row = $stmt->fetch()) {
        $stmt1 = $con->query("SELECT userId FROM registeredusers WHERE trim(MobileNumber)='".trim($row['SentMemberNumber'])."'");

        $userId = $stmt1->fetchColumn();

        if ($userId) {
            $stmt2 = $con->prepare("UPDATE notifications SET sentMemberUserId=".$userId." WHERE SentMemberNumber='".trim($row['SentMemberNumber'])."'");
            $stmt2->execute();
            $i++;
        }
    }
}


// Notification Part 2

$stmt = $con->query("SELECT DISTINCT ReceiveMemberNumber FROM notifications WHERE ReceiveMemberNumber !=''");
$totalRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($totalRows > 0) {
    while ($row = $stmt->fetch()) {
        $stmt1 = $con->query("SELECT userId FROM registeredusers WHERE trim(MobileNumber)='".trim($row['ReceiveMemberNumber'])."'");

        $userId = $stmt1->fetchColumn();

        if ($userId) {
            $stmt2 = $con->prepare("UPDATE notifications SET receivedMemberUserId=".$userId." WHERE ReceiveMemberNumber='".trim($row['ReceiveMemberNumber'])."'");
            $stmt2->execute();
            $i++;
        }
    }
}


// Cabopen

$stmt = $con->query("SELECT DISTINCT MobileNumber FROM cabopen");
$totalRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($totalRows > 0) {
    while ($row = $stmt->fetch()) {
        $stmt1 = $con->query("SELECT userId FROM registeredusers WHERE trim(MobileNumber)='".trim($row['MobileNumber'])."'");

        $userId = $stmt1->fetchColumn();

        if ($userId) {
            $stmt2 = $con->prepare("UPDATE cabopen SET userId=".$userId." WHERE MobileNumber='".trim($row['MobileNumber'])."'");
            $stmt2->execute();
            $i++;
        }
    }
}

// userRating

$stmt = $con->query("SELECT DISTINCT ownerNumber FROM userRating");
$totalRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($totalRows > 0) {
    while ($row = $stmt->fetch()) {
        $stmt1 = $con->query("SELECT userId FROM registeredusers WHERE trim(MobileNumber)='".trim($row['ownerNumber'])."'");

        $userId = $stmt1->fetchColumn();

        if ($userId) {
            $stmt2 = $con->prepare("UPDATE userRating SET ownerUserId=".$userId." WHERE ownerNumber='".trim($row['ownerNumber'])."'");
            $stmt2->execute();
            $i++;
        }
    }
}

$stmt = $con->query("SELECT DISTINCT memberNumber FROM userRating");
$totalRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($totalRows > 0) {
    while ($row = $stmt->fetch()) {
        $stmt1 = $con->query("SELECT userId FROM registeredusers WHERE trim(MobileNumber)='".trim($row['memberNumber'])."'");

        $userId = $stmt1->fetchColumn();

        if ($userId) {
            $stmt2 = $con->prepare("UPDATE userRating SET memberUserId=".$userId." WHERE memberNumber='".trim($row['memberNumber'])."'");
            $stmt2->execute();
            $i++;
        }
    }
}


// CabMember

$stmt = $con->query("SELECT DISTINCT MemberNumber FROM cabmembers");
$totalRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($totalRows > 0) {
    while ($row = $stmt->fetch()) {
        $stmt1 = $con->query("SELECT userId FROM registeredusers WHERE trim(MobileNumber)='".trim($row['MemberNumber'])."'");

        $userId = $stmt1->fetchColumn();

        if ($userId) {
            $stmt2 = $con->prepare("UPDATE cabmembers SET memberUserId=".$userId." WHERE MemberNumber='".trim($row['MemberNumber'])."'");
            $stmt2->execute();
            $i++;
        }
    }
}



$stmt = $con->query("SELECT DISTINCT OwnerNumber FROM acceptedrequest");
$totalRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($totalRows > 0) {
    while ($row = $stmt->fetch()) {
        $stmt1 = $con->query("SELECT userId FROM registeredusers WHERE trim(MobileNumber)='".trim($row['OwnerNumber'])."'");

        $userId = $stmt1->fetchColumn();

        if ($userId) {
            $stmt2 = $con->prepare("UPDATE acceptedrequest SET ownerUserId=".$userId." WHERE OwnerNumber='".trim($row['OwnerNumber'])."'");
            $stmt2->execute();
            $i++;
        }
    }
}

$stmt = $con->query("SELECT DISTINCT MemberNumber FROM acceptedrequest");
$totalRows = $con->query("SELECT FOUND_ROWS()")->fetchColumn();

if ($totalRows > 0) {
    while ($row = $stmt->fetch()) {
        $stmt1 = $con->query("SELECT userId FROM registeredusers WHERE trim(MobileNumber)='".trim($row['MemberNumber'])."'");

        $userId = $stmt1->fetchColumn();

        if ($userId) {
            $stmt2 = $con->prepare("UPDATE acceptedrequest SET memberUserId=".$userId." WHERE MemberNumber='".trim($row['MemberNumber'])."'");
            $stmt2->execute();
            $i++;
        }
    }
}

echo $i.' rows updated';