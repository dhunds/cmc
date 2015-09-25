<html>
   <head>
   <title>Top Origin</title>
   <h4>Number Of Members</h4>
<style>h4 {
    color: blue;
   text-decoration: underline;
}
   </style>
   </head>
   <BODY >
   <form action="mMembers.php" method="POST">
           <select input type="text" name="from" id="from">
           <option value="">Origin</option>
               <option value="">Noida</option>
             <option value="">Gr.Noida</option>
                <option value="">Gurgaon</option>
            <option value="">Delhi</option>
            </select> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; 

 <select input type="text" name="to" id="from">
           <option value="">Destination</option>
               <option value="">Noida</option>
             <option value="">Gr.Noida</option>
                <option value="">Gurgaon</option>
            <option value="">Delhi</option>
            </select> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; 
               Total Number of Members:&nbsp;&nbsp;

               <input type="text" name="Total no of Members">

<br/><br/>


<?php
include ('connection.php');

if(isset($_POST['btnTopOrigin']))
{

$name = $_POST['from'];


try {
  //$sql = "SELECT FromLocation FROM cabopen Where TravelDate BETWEEN '" . $_POST['from'] . "' AND '" . $_POST['to'] . "'";
  $sql = "SELECT DISTINCT(a.FromLocation,a.ToLocation) as FromLocation,(SELECT COUNT(*) FROM cabopen where FromLocation = a.FromLocation) AS NoOfCabs FROM cabopen a Where TravelDate BETWEEN '" . $_POST['from'] . "' AND '" . $_POST['to'] . "'";
//echo $sql;
  $stmt = $con->prepare($sql); 
  $stmt->execute();
     
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);	
    echo "<br/>";
					foreach ($result as $row) {
					$FromLoc = $row['FromLocation'];
					$FromData = split(',', $FromLoc);
					$l1 = count($FromData);
					
					/*for ($i = 0; $i < $l1-2; $i++) {
					if($i > 0)
					{
					 echo "," . $FromData[$i]; 
					 }
					 else
					 {
					 echo $FromData[$i]; 
					 }
					}*/

      
	   echo "<span style='color:#4279bd;'>". $FromData[$l1-2]  . "</span>&nbsp;&nbsp;&nbsp;&nbsp;"; 
	   //echo $FromData[$l1-1] . "&nbsp;&nbsp;&nbsp;&nbsp;"; 
echo $row['NoOfCabs'] .  "</span>&nbsp;&nbsp;&nbsp;&nbsp;";
echo "<a href='mTopOrigins.php?loc=" . urlencode($FromData[$l1-2]) . "'>Report</a></br></br>";
    }
}

catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;
echo "</table>";
}



if(isset($_POST['btnTopDestination']))
{

$name = $_POST['from'];


try {
  //$sql = "SELECT FromLocation FROM cabopen Where TravelDate BETWEEN '" . $_POST['from'] . "' AND '" . $_POST['to'] . "'";
  $sql = "SELECT DISTINCT(a.ToLocation) as ToLocation,(SELECT COUNT(*) FROM cabopen where ToLocation = a.ToLocation) AS NoOfCabs FROM cabopen a Where TravelDate BETWEEN '" . $_POST['from'] . "' AND '" . $_POST['to'] . "'";
//echo $sql;
  $stmt = $con->prepare($sql); 
  $stmt->execute();
     
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);	
    echo "<br/>";
					foreach ($result as $row) {
					$ToLoc = $row['ToLocation'];
					$ToData = split(',', $ToLoc);
					$l1 = count($ToData);
					
					/*for ($i = 0; $i < $l1-2; $i++) {
					if($i > 0)
					{
					 echo "," . $FromData[$i]; 
					 }
					 else
					 {
					 echo $FromData[$i]; 
					 }
					}*/

      
	   echo "<span style='color:#4279bd;'>". $ToData[$l1-2]  . "</span>&nbsp;&nbsp;&nbsp;&nbsp;"; 
	   //echo $FromData[$l1-1] . "&nbsp;&nbsp;&nbsp;&nbsp;"; 
echo $row['NoOfCabs'] .  "</span>&nbsp;&nbsp;&nbsp;&nbsp;";
echo "<a href='mTopOrigins.php?loc=" . urlencode($ToData[$l1-2]) . "'>Report</a></br></br>";
    }
}

catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;
echo "</table>";
}
?>

</form>
</body>
</html>