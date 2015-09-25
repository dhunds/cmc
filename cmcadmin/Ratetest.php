<!DOCTYPE HTML> 
<html>
<head>
</head>
<body> 

<?php
// define variables and set to empty values
$cabname = $dayrate = $nightrate = $contact = $address =$city = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   $cabname = test_input($_POST["cabname"]);
   $dayrate = test_input($_POST["dayrate"]);
   $nightrate = test_input($_POST["nightrate"]);
   $contact = test_input($_POST["contact"]);
   $address = test_input($_POST["address"]);
   $city = test_input($_POST["city"]);
}

function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}
?>
<fieldset style="width:50%" align="left" >
             <legend>Run Configuration:</legend>
             <div style="margin-top: 60px;">
                <center>

<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
  <font color="#0000CC"> CabName:</font> <input type="text" name="cabname">
   <br><br>
 <font color="#0000CC">  DayRate: </font><input type="text" name="dayrate">
   <br><br>
 <font color="#0000CC">  NightRate: </font><input type="text" name="nightrate">
   <br><br>
  <font color="#0000CC">  Contact: </font><input type="text" name="contact">
   <br><br>
  <font color="#0000CC">  Address:</font> <input type="text" name="address">
   <br><br>
    <font color="#0000CC">  City:</font> <input type="text" name="city">
   <br><br>
   <input style="background-color:#C8C8F4" type="submit" name="submit" value="Submit"> 
</form>
 </center>
             </div>
         </fieldset>

<?php


$sql2 = "INSERT INTO `cabcharges`(`CabName`, `DayRate`, `NightRate`,`Contact`,`Address`,`City`) 
VALUES ('$cabname ','$dayrate' ,'$nightrate','$contact','$address','$city')";
$stmt2 = $con->prepare($sql2);
$stmt2 = $stmt2->execute();
	

	if ($stmt2 == true) 
		{
			echo 'Records  Submitted Succesfully';
		}
		else 
		{
			echo 'Error';
		}

?>

</body>
</html>