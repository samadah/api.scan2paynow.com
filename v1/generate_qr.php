<?php

error_reporting(0);

header("Content-Type: application/json"); 
header("Cache-Control: no-cache");

$qr = $_GET['qr'];
$merchantId = $_GET['merchantId'];
//

        include('dbconnect.php');
		$result = mysqli_query($mysqli, "select * from alertco_users where acctid = '$merchantId'") or die(mysqli_error($mysqli));
		$count = mysqli_num_rows($result);

		if($count < 1){
 
			$data = [ 'status' => '404', 'message' => 'Account Invalid' ];
			header("HTTP/1.1 404");
		    echo $json = json_encode( $data );
		    die();

		}else{



$curl = curl_init();

//Disable CURLOPT_SSL_VERIFYHOST and CURLOPT_SSL_VERIFYPEER by
//setting them to false.
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=".$qr."",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_POSTFIELDS => "",
  CURLOPT_HTTPHEADER => array(
    "cache-control: no-cache"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "Server not responding...Please try again";
} else {
  echo $response;
}

}
?>