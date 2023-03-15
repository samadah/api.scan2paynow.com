<?php
//v1.
//Validate remittanceidtopay
//REST method POST
//
error_reporting(0);

header("Content-Type: application/json"); 
header("Cache-Control: no-cache");

$headers = apache_request_headers();

$skey = $_SERVER['HTTP_SKEY'];
$pkey = $_SERVER['HTTP_PKEY'];

//
include('dbconnect.php');
//capture entry
//Receiving entries in JSON
$params = json_encode((object) json_decode(file_get_contents('php://input'), TRUE));
//print_r($params);

//If no headers are sent
if($pkey == "" or $skey == ""){

	$data = [ 'status' => '400', 'message' => 'Keys (pkey and skey) cannot be empty. Send request as _POST' ];
    header("HTTP/1.1 400");
    echo $json = json_encode( $data );
    die();
}




//Verify the right pkey were sent
include('dbconnect.php');
	$stmt = $mysqli -> prepare("SELECT id from scan2pay_merchant WHERE pkey = ? and skey = ?") or die(mysqli_error($mysqli));
	$stmt -> bind_param("ss", $pkey, $skey) or die(mysqli_error($mysqli));
	$stmt->execute() or die(mysqli_error($mysqli));
	$stmt->bind_result($t);
	$stmt->fetch();

	if($t == ""){
	$data = [ 'status' => '404', 'message' => 'Keys do not match with those in your Scan2Pay account' ];
	header("HTTP/1.1 404");
    echo $json = json_encode( $data );
    die();
	

	}else{

	$data = [ 'statuscode' => '00', 'status' => 'success', 'message' => 'Account Ok' ];
	header("HTTP/1.1 200");
    echo $json = json_encode( $data );
    die();

	}

 
 
//
?>