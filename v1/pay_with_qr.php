<?php
//v1.
//Validate
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
if($pkey == ""){

	$data = [ 'status' => '400', 'message' => 'Public key (pkey) cannot be empty. Set it in header' ];
    header("HTTP/1.1 400");
    echo $json = json_encode( $data );
    die();
}




//Verify the right pkey were sent
include('dbconnect.php');
	$stmt = $mysqli -> prepare("SELECT id from scan2pay_merchant WHERE pkey = ?") or die(mysqli_error($mysqli));
	$stmt -> bind_param("s", $pkey) or die(mysqli_error($mysqli));
	$stmt->execute() or die(mysqli_error($mysqli));
	$stmt->bind_result($t);
	$stmt->fetch();

	if($t == ""){
	$data = [ 'status' => '404', 'message' => 'Invalid pKey' ];
	header("HTTP/1.1 404");
    echo $json = json_encode( $data );
    die();
	}

//If pkey is Ok. Check body for productid and merchant tel, and other compulsory things
$myArray = json_decode($params, true);
$merchantId = $myArray['merchantId'];
$productId = $myArray['productId'];
$payee_email = $myArray['payee_email'];
$payee_tel = $myArray['payee_tel'];
$payee_fname = $myArray['payee_fname'];
$payee_lname = $myArray['payee_lname'];
$amount = $myArray['amount'];


if($merchantId == "") {

    $data = [ 'status' => '400', 'message' => 'merchantId cannot be empty' ];
    header("HTTP/1.1 400");
    echo $json = json_encode( $data );
    die();

}
if($productId == "") {

    $data = [ 'status' => '400', 'message' => 'productId cannot be empty' ];
    header("HTTP/1.1 400");
    echo $json = json_encode( $data );
    die();

}
if($payee_email == "") {

    $data = [ 'status' => '400', 'message' => 'payee_email cannot be empty' ];
    header("HTTP/1.1 400");
    echo $json = json_encode( $data );
    die();

}
if($payee_tel == "") {

    $data = [ 'status' => '400', 'message' => 'payee_tel cannot be empty' ];
    header("HTTP/1.1 400");
    echo $json = json_encode( $data );
    die();

}
if($payee_fname == "") {

    $data = [ 'status' => '400', 'message' => 'payee_fname cannot be empty' ];
    header("HTTP/1.1 400");
    echo $json = json_encode( $data );
    die();

}
if($payee_lname == "") {

    $data = [ 'status' => '400', 'message' => 'payee_lname cannot be empty' ];
    header("HTTP/1.1 400");
    echo $json = json_encode( $data );
    die();

}
if($amount < 1) {

    $data = [ 'status' => '400', 'message' => 'amount cannot be empty' ];
    header("HTTP/1.1 400");
    echo $json = json_encode( $data );
    die();

}
//

if($merchantId != "") {

	//Select other params and output results
	include('dbconnect.php');
	$stmt = $mysqli -> prepare("SELECT tel, upline from alertco_users WHERE acctid = ?") or die(mysqli_error($mysqli));
	$stmt -> bind_param("s", $merchantId) or die(mysqli_error($mysqli));
	$stmt->execute() or die(mysqli_error($mysqli));
	$stmt->bind_result($tel, $upline);
	$stmt->fetch();


	//Check if merchant id exist
	if($tel == ""){
	$data = [ 'status' => '404', 'message' => 'Invalid merchantId' ];
	header("HTTP/1.1 404");
    echo $json = json_encode( $data );
    die();
	}

	//Check the merchant table to be sure if this acctid belongs to tel
	include('dbconnect.php');
	$res = mysqli_query($mysqli, "select * from scan2pay_merchant where tel = '$tel' and pKey = '$pkey'") or die(mysqli_query($mysqli));
	$checkstatus = mysqli_num_rows($res);

	if($checkstatus < 1){
	$data = [ 'status' => '404', 'message' => 'merchantId does not belong to this pkey' ];
	header("HTTP/1.1 404");
    echo $json = json_encode( $data );
    die();
	}

	if($checkstatus > 0){ //print response with details
		

		//First check if the payee is already on Alertco

		
		include('dbconnect.php');
		$stmt = $mysqli -> prepare("SELECT id from alertco_users WHERE tel = ?") or die(mysqli_error($mysqli));
		$stmt -> bind_param("s", $payee_tel) or die(mysqli_error($mysqli));
		$stmt->execute() or die(mysqli_error($mysqli));
		$stmt->bind_result($id);
		$stmt->fetch();

		if($id == ""){
			//Create account

					  include('dbconnect.php');
						//If no error, proceed to register this user...
					   $stmt = $mysqli -> prepare("INSERT INTO alertco_users (email, upline, secanswer, acctid, auth, fname, lname, tel, date, datetime, password) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)") or die(mysqli_error($mysqli));
					   
					   $auth = "member";
					   $date = date("d-M-Y"); 
					   $datetime = time();

					   //create account ID
					   $digits = 8;
					   $acctid = rand(pow(10, $digits-1), pow(10, $digits)-1);
					   //
					   $digits = 4;
					   $pin = rand(pow(10, $digits-1), pow(10, $digits)-1);
					   $secanswer = "no answer";
					   //
					   $stmt -> bind_param("sssssssssss", $payee_email, $upline, $secanswer, $acctid, $auth, ucwords($payee_fname), ucwords($payee_lname), $payee_tel, $date, $datetime, md5($pin)) or die(mysqli_error($mysqli));
					     
					   $stmt->execute() or die(mysqli_error($mysqli));

					   //

		//data 
	    $digits = 8;
		$requestId = rand(pow(10, $digits-1), pow(10, $digits)-1);
		$qr = "$merchantId*$productId*$requestId";
		$tel = $payee_tel;
		$amount = $amount; 
		$link = "http://localhost/api.scan2paynow.com/v1/generate_qr?qr=".$qr."&merchantId=".$merchantId."";

		//Issue response
		$data = [ 'status' => '00', 'merchantId' => $merchantId, 'qr' => $qr, 'productId' => $productId, 'payee_tel' => $tel, 'payee_email' => $payee_email, 'qrlink' => $link, 'amount' => $amount, 'message' => 'success', 'label1' => 'Scan this QR code with your Scan2Pay app to make payment', 'button1_label' => 'Pay With Scan2Pay ID', 'button2_label' => 'Pay With Bank Card' ];
		header("HTTP/1.1 200");
	    echo $json = json_encode( $data );
	    die();


		}else{

		include('dbconnect.php');
		$result = mysqli_query($mysqli, "select * from alertco_users where tel = '$payee_tel'") or die(mysqli_error($mysqli));
		$row = mysqli_fetch_array($result);
		
		//data 
		$digits = 8;
		$requestId = rand(pow(10, $digits-1), pow(10, $digits)-1);
		$qr = "$merchantId*$productId*$requestId";
		$tel = $row['tel'];
		$amount = $amount; 
		$link = "http://localhost/api.scan2paynow.com/v1/generate_qr?qr=".$qr."&merchantId=".$merchantId."";

		//Issue response
		$data = [ 'status' => '00', 'merchantId' => $merchantId, 'qr' => $qr, 'productId' => $productId, 'payee_tel' => $tel, 'payee_email' => $payee_email, 'qrlink' => $link, 'amount' => $amount, 'message' => 'success', 'label1' => 'Scan this QR code with your Scan2Pay app to make payment', 'button1_label' => 'Pay With Scan2Pay ID', 'button2_label' => 'Pay With Bank Card' ];
		header("HTTP/1.1 200");
	    echo $json = json_encode( $data );
	    die();

		}


		//
	
	}



    

}



//
?>