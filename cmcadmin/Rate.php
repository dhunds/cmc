<div>
	<div style="width:49%;float:left;height:100%;overflow:auto;">
	<h4>History</h4>
</div>

<div>
	<div style="width:49%;float:left;height:100%;overflow:auto;">
	<h4>History</h4>

	
<?php
 if(isset($_POST['submit']))
{

//Storing Selected Value In Variable

$CabName = $_POST['cabname'];  
$DayRate = $_POST['dayrate'];
$NightRate = $_POST['nightrate'];
$Contact = $_POST['contact'];  
$Address = $_POST['address'];
$City = $_POST['city'];

// Displaying Selected Value

//echo "You have selected :" .$CabName;  
//echo "You have selected :" .$DayRate;  
//echo "You have selected :" .$NightRate;  



$sql2 = "INSERT INTO `cabcharges`(`CabName`, `DayRate`, `NightRate`,`Contact`,`Address`,`City`) 
VALUES ('$CabName ','$DayRate' ,'$NightRate','$Contact','$Address','$City')";
$stmt2 = $con->prepare($sql2);
$stmt2 = $stmt2->execute();
	

	if ($stmt2 == true) 
		{
			//echo 'Records  Submitted Succesfully';
		}
		else 
		{
			echo 'Error';
		}



}
?>
    <div align="center">
        
             <fieldset style="width:40%" align="left" >
             <legend>Cab Configuration:</legend>
             <div style="margin-top: 60px;">
                <center>
				
				<form action="" method="POST">
					<font color="#0000CC">CabName:</font>&nbsp;&nbsp;&nbsp;<input type="text" name="cabname"><br><br>
					<font color="#0000CC">DayRate:</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="dayrate"><br><br>
					<font color="#0000CC">NightRate:</font>&nbsp;&nbsp;&nbsp;<input type="text" name="nightrate"><br><br>
					<font color="#0000CC">Contact:</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="contact"><br><br>
					<font color="#0000CC">Address:</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="address"><br><br>
					<font color="#0000CC">City:</font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="city"><br><br></p>
					<input type="submit" name="submit" value="Submit" />
          
					
				  </form>
			    </center>
             </div>
			 </fieldset>	
       </div>
</div>