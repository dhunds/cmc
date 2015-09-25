<html>
    <head>
    <title>Dynamic Drop Down List</title>

<style>
h4 {
   text-decoration: underline;
  color: Blue;
}
</style>
    </head>

     <div align="center">
    <BODY >
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; 
<form action="#" method="post">

 
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
   <input button style="background-color:#C8C8F4" type="submit" name="submit" value="SUBMIT"> 

          

<?php
if(isset($_POST['submit']))
{
echo hi;
//Storing Selected Value In Variable

$CabName = $_POST['cabname'];  
$DayRate = $_POST['dayrate'];
$NightRate = $_POST['nightrate'];
$Contact = $_POST['contact'];  
$Address = $_POST['address'];
$City = $_POST['city'];

// Displaying Selected Value

echo "You have selected :" .$CabName;  
echo "You have selected :" .$DayRate;  
echo "You have selected :" .$NightRate;  



$sql2 = "INSERT INTO `cabcharges`(`CabName`, `DayRate`, `NightRate`,`Contact`,`Address`,`City`) 
                          VALUES ('$CabName ','$DayRate' ,'$NightRate','$Contact','$Address','$City')";
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
}
?>        
</div>
</form>
</Body>
</Html>