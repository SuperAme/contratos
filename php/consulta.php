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
	
	
	if (isset($_POST["get_clients"])) {
	
		$sql = "SELECT accountname 
				FROM vtiger_account 
				WHERE accountname LIKE '**%' OR accountname LIKE '*%'
				ORDER BY vtiger_account.accountname DESC";
				
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			// output data of each row
			$rows = array();
			while($row = $result->fetch_assoc()) {
				$rows[] = $row["accountname"];
			}
			echo json_encode($rows);
		} else {
			echo "0 results";
		}
	}
	
	if (isset($_POST["get_notAssignedConTickets"])) {
		
		//Tickets en cualquier estado, no asignados
		$sql = "SELECT vt.ticketid, vt.ticket_no, va.accountname AS Cuenta, vt.title AS Refer, vt.status AS Estado, vc.createdtime, vcr.crmid,
                IF(vc.smownerid = 4, 'Service Group', 
                (SELECT user_name FROM vtiger_users WHERE vt.ticketid = vc.crmid AND vtiger_users.id = vc.smownerid)) AS Asignado_a,
                vt.hours AS Horas, vcf.cf_709 AS closed_date
                FROM vtiger_troubletickets vt
                LEFT JOIN vtiger_account va ON vt.parent_id = va.accountid
                LEFT JOIN vtiger_crmentity vc ON vt.ticketid = vc.crmid
                LEFT JOIN vtiger_ticketcf vcf ON vt.ticketid = vcf.ticketid
                LEFT JOIN vtiger_crmentityrel vcr ON vt.ticketid = vcr.relcrmid
                WHERE vcr.crmid IS NULL
                AND vt.status = 'Closed'
                AND vc.deleted = 0
                AND vcf.cf_643 = 'Poliza'
                AND vc.createdtime > '2017-01-01 00:00:00'
                ORDER BY va.accountname DESC, vc.createdtime";


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
		}
	}
    
    if (isset($_POST["get_lastConTicket"])) {
        $ticketid = $_POST["ticketid"];
        
        $sql = "SELECT vt.ticketid, vt.ticket_no, vt.title, vt.hours, va.accountname, vs.servicecontractsid, vs.contract_no, vs.subject, vs.contract_status, vs.progress AS Progreso, vs.total_units AS Contratadas, vs.used_units AS Usadas, (vs.total_units - vs.used_units) AS Restantes
                FROM vtiger_troubletickets vt
                LEFT JOIN vtiger_account va ON vt.parent_id = va.accountid
                LEFT JOIN vtiger_servicecontracts vs ON va.accountid = vs.sc_related_to
                WHERE vs.contract_status <> 'Complete' AND vs.contract_status <> 'Archived' AND vs.contract_status <> 'Undefined'
                AND vt.ticketid = $ticketid
                ORDER BY vs.contract_no DESC LIMIT 1";
        
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
		}
    }
    
    if (isset($_POST["set_conTicket"])) {
        $ticketid = $_POST["ticketid"];
        $conid = $_POST["conid"];
        $ticketHours = $_POST["ticketHours"];
        
        $sql = "INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule)
                VALUES ($conid, 'ServiceContracts', $ticketid, 'HelpDesk')";
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        
        $sql = "UPDATE vtiger_servicecontracts vs
                SET vs.used_units = vs.used_units + $ticketHours, vs.progress = ROUND((vs.used_units * 100)/vs.total_units, 0)
                WHERE vs.servicecontractsid = $conid";
        if ($conn->query($sql) === TRUE) {
            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
	
	$conn->close();
}

function utf8_encode_array($array) {
    return array_map('utf8_encode', $array);
}

?>
