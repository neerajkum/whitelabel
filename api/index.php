<?php
include 'Consumer/ConsumerProfile.php';
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();


$app->post('/registerconsumer','registerConsumer');
$app->post('/login','loginConsumer');
$app->post('/updateconsumer','updateConsumer');
$app->post('/getcityrates','getCityRates');
$app->post('/getuserdetails','getUserDetails');
$app->run();

function registerConsumer() {

	$app = \Slim\Slim::getInstance();

	$request = $app->request();
	$update = json_decode($request->getBody());

	$name = $update->name;
	$phone = $update->phone;
	$email = $update->email;
	$password = $update->password;

	$user = getUser($phone);

	if ($user !=null) {
			$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'The mobile number '.$phone.' is already registered');
	} else {
		$sql = "INSERT INTO CONSUMER (NAME, PHONE_NUM, EMAIL_ADDRESS, PASSWORD, CREATED_DT, LAST_UPDATED_DT) VALUES (:name, :phone, :email, :password, :createdDt, :lastUpdatedDt)";
		try {
			$db = getDB();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("name", $name);
			$stmt->bindParam("phone", $phone);
			$stmt->bindParam("email", $email);
			$stmt->bindParam("password", $password);
			$time = date('Y/m/d H:i:s');
			$stmt->bindParam("createdDt", $time);
			$stmt->bindParam("lastUpdatedDt", $time);
			$stmt->execute();
			$id = $db->lastInsertId();
			$db = null;
			$dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'Registration Successful', 'Id' => $id);

		} catch(PDOException $e) {
			error_log($e->getMessage(), 3, '/var/tmp/php.log');
			//echo '{"error":{"text":'. $e->getMessage() .'},"message":'. $update .'}';
			$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');
		}
	}
	$response = $app->response();
	$response['Content-Type'] = 'application/json';
    $response->body(json_encode($dataArray));
}

function loginConsumer() {

	$app = \Slim\Slim::getInstance();

	$request = $app->request();
	$update = json_decode($request->getBody());
	$phone = $update->username;
	$password = $update->password;
	$pushDeviceId = $update->pushDeviceId;
	$deviceOS = $update->deviceOS;
	$osVersion = $update->osVersion;
	$ip=$_SERVER['REMOTE_ADDR'];
	$time=time();

	$user = checkUserNameAndPassword($phone, $password);

	if ($user != null) {
	$consumerId = $user[0]->CONSUMER_ID;
	$emailAddr = $user[0]->EMAIL_ADDRESS;
		$consumerUser = getConsumerLoginForUser($consumerId);

		$consumerAuthKey = md5(uniqid($emailAddr.$time, false));
		if ($consumerUser != null) {
			updateInConsumerLogin($consumerUser[0]->CONSUMER_LOGIN_ID,$consumerAuthKey, $pushDeviceId, $deviceOS, $osVersion, $ip);
		} else {
			insertInConsumerLogin($consumerId, $consumerAuthKey, $pushDeviceId, $deviceOS, $osVersion, $ip);
		}

		$dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'Login Successful', 'Auth_Key' => $consumerAuthKey);
	} else {
		$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'The credentials provided are invalid. Kindly verify your phone number and password');
	}
	$response = $app->response();
	$response['Content-Type'] = 'application/json';
    $response->body(json_encode($dataArray));
}


function updateConsumer(){
	$app = \Slim\Slim::getInstance();

	$request = $app->request();
	$update = json_decode($request->getBody());

	error_log("something to get in update".$request->getBody(), 3, 'C:\xampp\php\logs\php.log');

	$name = $update->name;
	$phone = $update->phone;
	$email = $update->email;
	//$password = $update->password;

	$bdate= $update->bdate;
	$height= $update->height;
	$weight= $update->weight;
	$address= $update->address;
	$gender= $update->gender;
	$authKey= $update->authKey;

	if (authenticateConsumer($phone,$authKey)) {

        $sql = "UPDATE CONSUMER SET NAME = :name, EMAIL_ADDRESS = :email, HEIGHT = :height, WEIGHT = :weight, ADDRESS =:address, GENDER =:gender, LAST_UPDATED_DT= :lastUpdatedDt WHERE PHONE_NUM = :phone";
        try {
			$db = getDB();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("name", $name);
			$stmt->bindParam("phone", $phone);
			$stmt->bindParam("email", $email);
			//$stmt->bindParam("password", $password);
                        $stmt->bindParam("height", $height);
                        $stmt->bindParam("weight", $weight);
                        $stmt->bindParam("address", $address);
                        $stmt->bindParam("gender", $gender);
			$time = date('Y/m/d H:i:s');
			//$stmt->bindParam("createdDt", $time);
			$stmt->bindParam("lastUpdatedDt", $time);
			$stmt->execute();
			$id = $db->lastInsertId();
			$db = null;
			$dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'Profile Successfully Successful', 'Id' => $id);

		} catch(PDOException $e) {
			error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
			//echo '{"error":{"text":'. $e->getMessage() .'},"message":'. $update .'}';
			$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');
		}
	} else {
		$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'Invalid Auth Key. If you are using our mobile app, please contact 9873805309');
	}

	$response = $app->response();
	$response['Content-Type'] = 'application/json';
    $response->body(json_encode($dataArray)); }

	function getCityRates(){
	$app = \Slim\Slim::getInstance();

	$request = $app->request();
	$update = json_decode($request->getBody());
	$cityrateid= $update->cityrateid;
	$cityname= $update->cityname;
	$sql = "SELECT * FROM CITY_RATE where CITY_RATE_ID ='".$cityrateid."' and CITY_NAME ='".$cityname."'";
	try {
		$db = getDB();
		$stmt = $db->query($sql);
		$dataArray = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db=null;
		
	}
    
		catch(PDOException $e) {
	   error_log($e->getMessage(), 3, '/var/tmp/php.log');
			//echo '{"error":{"text":'. $e->getMessage() .'},"message":'. $update .'}';
			$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');
		}
		$response = $app->response();
	    $response['Content-Type'] = 'application/json';
		$response->body(json_encode($dataArray)); 
			
		}
     
	 function getUserDetails()
	 {  $app = \Slim\Slim::getInstance();

	    $request = $app->request();
	    $update = json_decode($request->getBody());
		$phone = $update->phone;
		$user = getUser($phone);

	if ($user !=null) {
			try {
				$response = $app->response();
	    $response['Content-Type'] = 'application/json';
		$response->body(json_encode($user)); 
				
	}
	catch(PDOException $e) {
	   error_log($e->getMessage(), 3, '/var/tmp/php.log');
			//echo '{"error":{"text":'. $e->getMessage() .'},"message":'. $update .'}';
			$user = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');
	        $response = $app->response();
	    $response['Content-Type'] = 'application/json';
		$response->body(json_encode($user)); 
	} }
	else
	{
     $user = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');		
	    $response = $app->response();
	    $response['Content-Type'] = 'application/json';
		$response->body(json_encode($user)); 	
	}
		 
		 
	  }
	


?>