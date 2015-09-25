<?php

include ('connection.php');

//Fetching Values from URL
$CabId = $_POST['CabId'];
$OwnerName = $_POST['OwnerName'];
$OwnerNumber = $_POST['OwnerNumber'];
$MemberName = $_POST['MemberName'];
$MemberNumber= $_POST['MemberNumber'];
$Feedback= $_POST['Feedback'];
$RatingName= $_POST['RatingName'];
$RatingNumber= $_POST['RatingNumber'];


$sql2 = "INSERT INTO `feedback`(`CabId`, `OwnerName`, `OwnerNumber`, `MemberName`, `MemberNumber`, `Feedback`, `RatingName`, `RatingNumber`) 
VALUES ('$CabId','$OwnerName','$OwnerNumber','$MemberName','$MemberNumber','$Feedback','$RatingName','$RatingNumber')";


$stmt2 = $con->prepare($sql2);
$res2 = $stmt2->execute();


if ($res2 === true) 
		{
			echo 'Form Submitted Succesfully';
		}
		else {
			echo 'Error';
		}

?>