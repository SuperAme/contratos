<?php
$servername = "localhost";
$username = "Desarrollo";
$password = "hIm7RAZqYnSjwxD";
$dbname = "passcrm540";
//$port = 3306;
$port = 33307;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT vtiger_account.accountname, vtiger_troubletickets.ticket_no, vtiger_troubletickets.status
FROM vtiger_account
LEFT JOIN vtiger_troubletickets ON vtiger_account.accountid = vtiger_troubletickets.parent_id
WHERE vtiger_troubletickets.status != 'Closed' AND (vtiger_account.accountname LIKE '**%' OR vtiger_account.accountname LIKE '*%') ORDER BY vtiger_account.accountname";

/*
$sql = "SELECT vtiger_account.accountname, vtiger_troubletickets.ticket_no, vtiger_troubletickets.status
FROM vtiger_account
LEFT JOIN vtiger_troubletickets ON vtiger_account.accountid = vtiger_troubletickets.parent_id
WHERE vtiger_account.account_no = 'ACC10'";
*/
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "Cuenta: " . $row["accountname"]. " Ticket: " . $row["ticket_no"] .  " Estado: " . $row["status"]."<br>";
    }
	echo $result->num_rows;
} else {
    echo "0 results";
}

/*
$sql = "SELECT vtiger_account.accountname, vtiger_troubletickets.ticket_no, vtiger_troubletickets.status
FROM vtiger_troubletickets, vtiger_account
WHERE vtiger_troubletickets.parent_id = vtiger_account.accountid
AND vtiger_troubletickets.status != 'Closed'
AND (vtiger_account.accountname LIKE '**%' OR vtiger_account.accountname LIKE '*%')
ORDER BY vtiger_account.accountname";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo " Cuenta: " . $row["accountname"] . " Ticket: " . $row["ticket_no"] .  " Estado: " . $row["status"]."<br>";
    }
	echo $result->num_rows;
} else {
    echo "0 results";
}
*/
/*
$sql = "SELECT accountname FROM vtiger_account WHERE accountname LIKE '**%' OR accountname LIKE '*%'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
	// output data of each row
	$rows = array();
	while($row = $result->fetch_assoc()) {
		$rows[] = $row["accountname"];
	}
	echo json_encode($rows[0]);
} else {
	echo "0 results";
}
*/
$conn->close();
?>