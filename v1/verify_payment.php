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
if($skey == "" or $pkey == ""){
	$data = [ 'status' => '400', 'message' => 'Both keys (skey & pkey) cannot be empty. Set it in header' ];
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
	$data = [ 'statuscode' => '406', 'status' => 'keys_unmatched', 'message' => 'Invalid keys combination' ];
	header("HTTP/1.1 404");
    echo $json = json_encode( $data );
    die();
	}

//Receiving by POST form data
 
$ref = $_POST['ref'];


 /////

if($ref != "") {

	//Select other params and output results
	include('dbconnect.php');
	$res = mysqli_query($mysqli, "select * from scan2pay_in where transref = '$ref'") or die(mysqli_error($mysqli));
	$checkref = mysqli_num_rows($res);

	if($checkref < 1){
	$data = [ 'statuscode' => '407', 'status' => 'invalid_ref', 'message' => 'Invalid Ref ID' ];
	header("HTTP/1.1 404");
    echo $json = json_encode( $data );
    die();
	}

	if($checkref > 0){ //print response with details
		

		//let's see the details
		$row = mysqli_fetch_array($res);
		//
		$ref = $row['transref'];
		$productId = $row['productid'];
		$payee_name = $row['payee_name'];
		$amount = $row['amount'];


		 
		$data = [ 'statuscode' => '00', 'status' => 'completed', 'ref' => $ref, 'productId' => $productId, 'payee_name' => $payee_name, 'amount' => $amount, 'message' => 'Successful' ];
		header("HTTP/1.1 200");
	    echo $json = json_encode( $data );
	    die();


		//
	   }


}else{

	$data = [ 'statuscode' => '408', 'status' => 'no_ref', 'message' => 'No ref ID supplied' ];
		header("HTTP/1.1 400");
	    echo $json = json_encode( $data );
	    die();

}



//
?>