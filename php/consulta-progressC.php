<?php
$servername = "localhost";
$username = "Desarrollo";
$password = "hIm7RAZqYnSjwxD";
$dbname = "passcrm540";
//$port = 3306;
$port = 33307;

if($_SERVER['REQUEST_METHOD'] == "POST") {
	
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname, $port);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	
    if (isset($_POST["get_realHoursContract"])) {
        $contractNo = $_POST["contractNo"];
	
		$sql = "SELECT vs.contract_no, SUM(vt.hours) AS realHours
                FROM vtiger_troubletickets vt
                LEFT JOIN vtiger_crmentityrel vcr ON vt.ticketid = vcr.relcrmid
                LEFT JOIN vtiger_servicecontracts vs ON vcr.crmid = vs.servicecontractsid
                LEFT JOIN vtiger_account va ON vt.parent_id = va.accountid
                WHERE vs.sc_related_to = va.accountid
                AND vs.contract_no = '$contractNo'
                GROUP BY contract_no";
				
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			// output data of each row
			$rows = array();
			while($row = $result->fetch_assoc()) {
				$rows[] = $row;
			}
			$rows = array_map('utf8_encode_array', $rows);
			echo json_encode($rows);
		} else {
			$cero = array("0 results");
			echo json_encode($cero);
			//printf("Error: %s\n", $conn->error);
		}
	}
    
	if (isset($_POST["get_progressContracts"])) {
	
		$sql = "SELECT va.accountname AS Cuenta, t1.subject AS Poliza, t1.contract_no AS Contrato, t1.servicecontractsid, t1.contract_status AS Status, t1.start_date, t1.due_date, t1.progress AS Progreso, t1.total_units AS Contratadas, t1.used_units AS Usadas, (t1.total_units - t1.used_units) AS Restantes
        FROM vtiger_servicecontracts t1 
        LEFT JOIN vtiger_servicecontracts t2 
        ON (t1.sc_related_to = t2.sc_related_to AND t1.servicecontractsid < t2.servicecontractsid)
        LEFT JOIN vtiger_account va ON t1.sc_related_to = va.accountid
        WHERE t2.servicecontractsid IS NULL
        AND t1.contract_status <> 'Complete' AND t1.contract_status <> 'Archived'
        AND t1.start_date > '2015-12-31'
        ORDER BY t1.total_units DESC";
        
        /*$sql = "SELECT (SELECT va.accountname FROM vtiger_account va WHERE t1.sc_related_to = va.accountid) AS Cuenta, t1.subject AS Poliza, t1.contract_no AS Contrato, t1.servicecontractsid, t1.contract_status AS Status, t1.start_date, t1.due_date, t1.progress AS Progreso, t1.total_units AS Contratadas, t1.used_units AS Usadas, (t1.total_units - t1.used_units) AS Restantes
        FROM vtiger_servicecontracts t1
        WHERE  t1.subject = 'Genelec 2da 2016'";*/
				
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			// output data of each row
			$rows = array();
			while($row = $result->fetch_assoc()) {
				$rows[] = $row;
			}
			$rows = array_map('utf8_encode_array', $rows);
			echo json_encode($rows);
		} else {
			$cero = array("0 results");
			echo json_encode($cero);
			//printf("Error: %s\n", $conn->error);
		}
	}
    
    if (isset($_POST["get_contractTickets"])) {
        $contractNo = $_POST["contractNo"];
        $sql = "SELECT va.accountname, vs.contract_no, vs.subject, vt.ticketid, vt.ticket_no, vcf.cf_805 AS fecha, vt.title, vt.status, vt.hours,
        IF(vc.smownerid = 4, 'Service Group', (SELECT CONCAT(vu.first_name, ' ', vu.last_name) FROM vtiger_users vu WHERE vt.ticketid = vc.crmid AND vu.id = vc.smownerid)) AS asignado
        FROM vtiger_troubletickets vt
        LEFT JOIN vtiger_crmentity vc ON vt.ticketid = vc.crmid
        LEFT JOIN vtiger_crmentityrel vcr ON vt.ticketid = vcr.relcrmid
        LEFT JOIN vtiger_servicecontracts vs ON vcr.crmid = vs.servicecontractsid
        LEFT JOIN vtiger_ticketcf vcf ON vt.ticketid = vcf.ticketid
        LEFT JOIN vtiger_account va ON vt.parent_id = va.accountid
        WHERE vs.contract_no = '$contractNo'
        ORDER BY fecha, ticket_no";
        
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			// output data of each row
			$rows = array();
			while($row = $result->fetch_assoc()) {
				$rows[] = $row;
			}
			$rows = array_map('utf8_encode_array', $rows);
			echo json_encode($rows);
		} else {
			$cero = array("0 results");
			echo json_encode($cero);
			//printf("Error: %s\n", $conn->error);
		}
    }
    
    if (isset($_POST["get_ticketComments"])) {
        $ticketId = $_POST["ticketId"];
        $sql = "SELECT ticketid, comments, createdtime,
        (SELECT CONCAT(vu.first_name, ' ', vu.last_name) FROM vtiger_users vu WHERE vu.id = vtc.ownerid) AS autor
        FROM vtiger_ticketcomments vtc 
        WHERE ticketid = $ticketId
        ORDER BY createdtime DESC";
        
        $result = $conn->query($sql);
		if ($result->num_rows > 0) {
			// output data of each row
			$rows = array();
			while($row = $result->fetch_assoc()) {
				$rows[] = $row;
			}
			$rows = array_map('utf8_encode_array', $rows);
			echo json_encode($rows);
		} else {
			$cero = array("0 results");
			echo json_encode($cero);
			//printf("Error: %s\n", $conn->error);
		}
    }
	
	$conn->close();
}

function utf8_encode_array($array) {
    return array_map('utf8_encode', $array);
}

?>
