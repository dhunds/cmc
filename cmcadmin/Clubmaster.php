<?php
include('functions.php');

	echo "<h2 class='headingText' style='margin-bottom: 0px;'>Club Master</h2>";
	echo "<div>";
	echo "<div class='pure-u-1'><p style='text-align:right; margin-right: 5px;'><a href='exportClubMaster.php'><img src='images/icon_excel.gif'  border='0' width='25' height='25'/></a></p></div>";
	echo "<div class='pure-g' style='font-size:13px; font-weight:bold;'>";
    echo "<div class='pure-u-5-24'><p class='tHeading'>Owner Name</p></div>";	
	echo "<div class='pure-u-4-24'><p class='tHeading'>Owner Number</p></div>";
	echo "<div class='pure-u-3-24'><p class='tHeading'>Club Name</p></div>";	
	echo "<div class='pure-u-3-24'><p class='tHeading'>Club Status</p></div>";
	echo "<div class='pure-u-2-24'><p class='tHeading'>No of Users</p></div>";
	echo "<div class='pure-u-3-24'><p class='tHeading'>Registered Users</p></div>";
	echo "<div class='pure-u-3-24'><p class='tHeading'>Non Registered Users</p></div>";
	echo "<div class='pure-u-1-24'><p class='tHeading'>Action</p></div>";
	echo "</div>";

	$sql = "SELECT pm.*, ru.FullName FROM userpoolsmaster pm JOIN registeredusers ru ON pm.OwnerNumber=ru.MobileNumber";
	$stmt = $con->prepare($sql);
	if ($stmt->execute())
	{
		$rowCount = (int) $stmt->rowCount();

        $totalpages   = ceil($rowCount/PAGESIZE);

        if(isset($_REQUEST['page']) && $_REQUEST['page']!=''){
            $page = $_REQUEST['page'];
        }else{
            $page = 1;
        }

        $start= ($page-1) * PAGESIZE;
        $sql .=" LIMIT $start , ".PAGESIZE;

        $stmt = $con->prepare($sql);
        $stmt->execute();

		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);				
		if ($rowCount > 0)
		{
			foreach ($result as $row) 
			{
				$sql = "SELECT MemberNumber FROM userpoolsslave WHERE PoolId=".$row['PoolId'];
				$stmt = $con->query($sql);
				$members = $stmt->fetchAll(PDO::FETCH_ASSOC);
				$row['NoOfUsers'] = count($members)+1;

				$memberNumbers = '';

				foreach ($members as $val){
					$memberNumbers .="'".$val['MemberNumber']."',";
				}

				if ($memberNumbers) {
					$memberNumbers = substr($memberNumbers,0,-1);

					$sql = "SELECT COUNT(*) as RegisteredUsers FROM registeredusers WHERE MobileNumber IN ($memberNumbers)";
					$stmt = $con->query($sql);
					$registeredMembers = $stmt->fetch(PDO::FETCH_ASSOC);

					$row['RegisteredUsers'] = $registeredMembers['RegisteredUsers']+1;
					$row['NonRegisteredUsers'] = $row['NoOfUsers'] - $row['RegisteredUsers'];
				} else {
					$row['RegisteredUsers'] = 0;
					$row['NonRegisteredUsers'] = $row['NoOfUsers'];
				}

				echo "<div class='pure-g pure-g1' style='font-size:13px;'>";	
				echo "<div class='pure-u-5-24'><p style='margin-left: 5px;'>" . $row['FullName'] . " </p></div>";
				echo "<div class='pure-u-4-24'><p>" . $row['OwnerNumber'] . " </p></div>";	
				echo "<div class='pure-u-3-24'><p style='margin-left: 10px;'>" . $row['PoolName'] . " </p></div>";
				echo "<div class='pure-u-3-24'><p>" . $row['PoolStatus'] . "</p></div>";
				echo "<div class='pure-u-2-24'><p style='text-align: center'>" . $row['NoOfUsers'] . "</p></div>";
				echo "<div class='pure-u-3-24'><p style='text-align: center'>" . $row['RegisteredUsers'] . "</p></div>";
				echo "<div class='pure-u-3-24'><p style='text-align: center'>" . $row['NonRegisteredUsers'] . "</p></div>";
				echo "<div class='pure-u-1-24'><p style='text-align: center'><a href='mEditClubMaster.php?id=" . $row['PoolId'] . "'>View</a></p></div>";
				echo "</div>";
			}											
		}				
		else
		{
			echo "<span style='color:Green;font-size:13px; font-weight:bold;'>No results to display!</span>";
		}
	}	
	else
	{							
		echo "<span style='color:Red;font-size:13px; font-weight:bold;'>Error: " . $con->error . "</span>";
	}
    echo pagination_search($totalpages, $page);
	echo "</div>";
	
?>
