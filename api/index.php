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
$app->post('/changepassword','changePassword');
$app->post('/forgotpassword','forgotPassword');
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
			date_default_timezone_set('Asia/Kolkata');
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
	date_default_timezone_set('Asia/Kolkata');
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

	// error_log("something to get in update".$request->getBody(), 3, 'C:\xampp\php\logs\php.log');

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

        $sql = "UPDATE CONSUMER SET NAME = :name, EMAIL_ADDRESS = :email,BIRTH_DATE = :bdate, HEIGHT = :height, WEIGHT = :weight, ADDRESS =:address, GENDER =:gender, LAST_UPDATED_DT= :lastUpdatedDt WHERE PHONE_NUM = :phone";
        try {
			$db = getDB();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("name", $name);
			$stmt->bindParam("phone", $phone);
			$stmt->bindParam("email", $email);
			$stmt->bindParam("bdate", $bdate);
                        $stmt->bindParam("height", $height);
                        $stmt->bindParam("weight", $weight);
                        $stmt->bindParam("address", $address);
                        $stmt->bindParam("gender", $gender);
						date_default_timezone_set('Asia/Kolkata');
			$time = date('Y/m/d H:i:s');
			//$stmt->bindParam("createdDt", $time);
			$stmt->bindParam("lastUpdatedDt", $time);
			$stmt->execute();
			$id = $db->lastInsertId();
			$db = null;
			$dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'Profile Successfully Updated', 'Id' => $id);

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
		$user = getUserProfile($phone);

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
	  
	  function changePassword()
	  { $app = \Slim\Slim::getInstance();
        $request = $app->request();
	    $update = json_decode($request->getBody());
        $phone = $update->phone;
		$authKey= $update->authKey;
		$oldpass= $update->oldpass;
		$newpass= $update->newpass;
		$connewpass= $update->connewpass;
		if (authenticateConsumer($phone,$authKey) && checkUserNameAndPassword ($phone, $oldpass))
			
		{  if($newpass==$connewpass)
			{ $sql = "UPDATE CONSUMER SET PASSWORD = :newpass, LAST_UPDATED_DT= :lastUpdatedDt WHERE PHONE_NUM = :phone";
		try
				{ $db = getDB();
			      $stmt = $db->prepare($sql);
			      $stmt->bindParam("newpass", $newpass);
				  date_default_timezone_set('Asia/Kolkata');
			      $time = date('Y/m/d H:i:s');
			      $stmt->bindParam("lastUpdatedDt", $time);
				  $stmt->bindParam("phone", $phone);
			      $stmt->execute();
			      $id = $db->lastInsertId();
			      $db = null;
			      $dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'Password Successfully Updated', 'Id' => $id);
					
				}
				catch(PDOException $e) {
			error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
			//echo '{"error":{"text":'. $e->getMessage() .'},"message":'. $update .'}';
			$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');
		}
			}
			else {
		$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'New Password and Confirm Password does not match');
	}
	
		}
		else {
		$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'Incorrect Current Password. If you are using our mobile app, please contact 9873805309');
	}

	$response = $app->response();
	$response['Content-Type'] = 'application/json';
    $response->body(json_encode($dataArray));
	  }
	  
	  function forgotPassword()
	  { $app = \Slim\Slim::getInstance();
        $request = $app->request();
	    $update = json_decode($request->getBody());
		$phone = $update->phone;
		if(getUser($phone))
		{ $newpass=randomPassword();
	      
	     $sql = "SELECT EMAIL_ADDRESS FROM CONSUMER where PHONE_NUM ='".$phone."' ";
		 $sql1="UPDATE CONSUMER SET PASSWORD = :newpass, LAST_UPDATED_DT= :lastUpdatedDt WHERE PHONE_NUM = :phone";
	try
			{ $db = getDB();
		$stmt = $db->query($sql);
		$stmt->bindParam("phone", $phone);
		$email = $stmt->fetchColumn(3);
		$stmt = $db->prepare($sql1);
			      $stmt->bindParam("newpass", $newpass);
				  date_default_timezone_set('Asia/Kolkata');
			      $time = date('Y/m/d H:i:s');
			      $stmt->bindParam("lastUpdatedDt", $time);
				  $stmt->bindParam("phone", $phone);
				  $stmt->bindParam("newpass", $newpass);
			      $stmt->execute();
			      $id = $db->lastInsertId();
			      $db = null;

    
    $from = "thepoweryoga@gmail.com";
    $subject = "Your Password Has been reset"; 
    $message = "Hi, we have reset your password. 

    Your New Password is: $newpass 

   LOGIN TO OUR MOBILE APP USING THIS NEW PASSWORD
    Once logged in you can change your password 

    Thanks! 
    Admin YOM

    This is an automated response, DO NOT REPLY!"; 

   $headers = "From: $from\r\n";
        $headers .= "Content-type: text/html\r\n";
       
        mail($email, $subject, $message, $headers);
		
			$dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'A New Password Has been sent to your registered mail id', 'Id' => $id);	
				
			}
			catch(PDOException $e) {
			error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
			//echo '{"error":{"text":'. $e->getMessage() .'},"message":'. $update .'}';
			$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');
		}
			}
			else {
		$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'Given Phone Number is not registered !!');
	}
			$response = $app->response();
	        $response['Content-Type'] = 'application/json';
            $response->body(json_encode($dataArray));
			
			
		}
		  
		  
		  
		  
		  
		  
	  

?>