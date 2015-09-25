<html>
    <head>
<style>
h4 {
   text-decoration: underline;
  color: Blue;
}
</style>

  
<div align="center">
   <h4>Cab Details</H4>
    <BODY bgcolor="#f8f8ff" >
      <form action="country.php" method="post">
    
<style>
.tRowColor {color:#fff;background:#4279bd;}
</style>
<html>
 <head>
<style>
h4 {
   text-decoration: underline;
  color: Blue;
}
</style>

   </head>
     <div align="center">

<?php


class TableRows extends RecursiveIteratorIterator { 
    function __construct($it) { 
        parent::__construct($it, self::LEAVES_ONLY); 
    }

    function current() {
        return "<td style='width:150px;border:1px solid black;'>" . parent::current(). "</td>";
    }

    function beginChildren() { 
        echo "<tr>"; 
    } 

    function endChildren() { 
        echo "</tr>" . "\n";
    } 

} 
include ('connection.php');


try {
  $sql = "SELECT * FROM country Where countryCode='" . $_POST['COUNTRY'] . "'";
   $stmt = $con->prepare($sql); 
    $stmt->execute();

    	
			echo "<br/><br/>";
			echo "<table style='border: 1px solid #fff;'>";
			echo "<tr class='tRowColor'><th>CabName</th><th>CabType</th><th>CabDetailId</th><th>CabNameID</th><th>CarType</th><th>BaseFare</th><th>Mini.Dist.</th><th>Extra/km.</th><th>NightCharges</th><th>City</th><th>OutSataion</th></tr>";
		
    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC); 
	
    foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) { 
        echo $v;
    }
}


catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
$conn = null;
echo "</table>";
?>

</div>
</form>
</Body>
</Html>
   

