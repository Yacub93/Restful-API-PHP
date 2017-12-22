<?php
// Connect to Firmstep database
	include("../connection.php");

	$db = new dbObj();
	$connection =  $db->getConnectionString();
	$request_method=$_SERVER["REQUEST_METHOD"];




switch($request_method)
	{

		case 'POST':
			// Create a new entry into the queue table with the current timestamp
				insertQueue();
		case 'GET':
			if(!empty($_GET["id"]))
			{
				$id = intval($_GET["id"]);
				getQueue($id);//if ID if passed, return single record
			}
			else
			{
				getQueue();
			}
			break;
		default:
			// Invalid Request Method
			header("HTTP/1.0 405 Method Not Allowed");
			break;
	}


function insertQueue()
	{
		global $connection;

		$data = json_decode(file_get_contents('php://input'), true);

		// var_dump($data);
		$firstName    = $data["firstName"];
		$lastName     = $data["lastName"];
		$organization = $data["organization"];
		$type         = $data["type"];
		$service      = $data["service"];

		date_default_timezone_set("Europe/London");
		$queuedDate   = date("Y-m-d H:i:s");//current timestamp

		var_dump($type);
		var_dump($service);

		//Verify Required Parameters - Return JSON error response if failed
		if ($type !== "Citizen" && $type !== "Anonymous") {
			$response = array(
				'status' => 3,
				'status_message' =>'ERROR! Type is Mandatory - values accepted are: Citizen or Anonymous.'
			);	
			header('Content-Type: application/json');
			echo json_encode($response);
			exit();
		}
		else if ($type === "Citizen" && $firstName === "") {
			$response=array(
				'status' => 4,
				'status_message' =>'ERROR! firstName must be entered for Citizen.'
			);	
			header('Content-Type: application/json');
			echo json_encode($response);
			exit();
		}
		else if ($type === "Citizen" && $lastName === "") {
			$response=array(
				'status' => 5,
				'status_message' =>'ERROR! lastName must be entered for Citizen.'
			);	
			header('Content-Type: application/json');
			echo json_encode($response);
			exit();
		}
		else if ($service !== "Council Tax" && $service !== "Benefits" && $service !== "Rent") {
			$response=array(
				'status' => 6,
				'status_message' =>'ERROR! Service is Mandatory - values accepted are: Council Tax, Benefits, Rent.'
			);
			header('Content-Type: application/json');
			echo json_encode($response);
			exit();
		}
		else
		{
			$response=array(
					'status' => 2,
					'status_message' =>'Validation successful.'
				);
			header('Content-Type: application/json');
			echo json_encode($response);
		}
		
		echo $query="INSERT INTO queue SET 
		firstName='".$firstName."', 
		lastName='".$lastName."', 
		organization='".$organization."', 
		type='".$type."', 
		service='".$service."', 
		queuedDate='".$queuedDate."'";

		if(mysqli_query($connection, $query))
		{
			$response=array(
				'status' => 1,
				'status_message' =>'Added to queue successfully.'
			);
		}
		else
		{
			$response=array(
				'status' => 0,
				'status_message' =>'Queue Insertion has failed.'
			);
		}
		header('Content-Type: application/json');
		echo json_encode($response);
	}




function getQueue()
	{
		global $connection;
		$query = "SELECT * FROM queue WHERE DATE(queuedDate) = DATE(NOW())";
		$response = array();
		$result=mysqli_query($connection, $query);
		while($row=mysqli_fetch_assoc($result))
		{
			$response[]=$row;
		}
		header('Content-Type: application/json');
		echo json_encode($response);
	}


//GET Single record for DB
// function getQueue($id=0)
// {
// 	global $connection;
// 	$query="SELECT * FROM queue";
// 	if($id != 0)
// 	{
// 		$query.=" WHERE id=".$id." LIMIT 1";
// 	}
// 	$response=array();
// 	$result=mysqli_query($connection, $query);
// 	while($row=mysqli_fetch_assoc($result))
// 	{
// 		$response[]=$row;
// 	}
// 	header('Content-Type: application/json');
// 	echo json_encode($response);
// }	

?>