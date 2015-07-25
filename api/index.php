<?php
include 'Consumer/ConsumerProfile.php';
include 'Provider/ProviderProfile.php';
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
$app->post('/getmyschedule','getMySchedule');
$app->post('/registerprovider','registerProvider');
$app->post('/loginprovider','loginProvider');
$app->post('/updateprovider','updateProvider');
$app->post('/changepasswordpro','changePasswordPro');
$app->post('/forgotpasswordpro','forgotPasswordPro');
$app->post('/getmyschedulepro','getMySchedulePro');
$app->post('/getpendingrequest','getPendingRequest');
$app->post('/updaterequest','updateRequest');
$app->post('/welcomeconsumer','welcomeConsumer');
$app->post('/welcomeprovider','welcomeProvider');
$app->post('/addclass','addClass');
$app->post('/createproviderschedule','createProviderSchedule');
$app->post('/viewmyclients','viewMyClients');
$app->post('/updateClassStatus','updateClassStatus');
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
			$db = null;
			$dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'Profile Successfully Updated');

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
	$cityname= $update->cityname;
	$sql = "SELECT * FROM CITY_RATE where CITY_NAME ='".$cityname."'";
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
		if($dataArray==null)
		{
		$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'CITY NOT FOUND');

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
				if (authenticateConsumer($phone,$authKey) && checkUserNameAndPassword ($phone, $oldpass))

		{
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
			      $db = null;
			      $dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'Password Successfully Updated');

				}
				catch(PDOException $e) {
			error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
			//echo '{"error":{"text":'. $e->getMessage() .'},"message":'. $update .'}';
			$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');
		}
			}


		}
		else {
		$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'Incorrect Current Password or Auth Key. If you are using our mobile app, please contact 9873805309');
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

			$dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'A New Password Has been sent to your registered mail id');

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


		function getMySchedule()


		{
			$app = \Slim\Slim::getInstance();
            $request = $app->request();
	        $update = json_decode($request->getBody());
			$phone = $update->phone;
			$authKey= $update->authKey;
			$start_date= $update->start_date;
			$end_date= $update->end_date;
			if (authenticateConsumer($phone,$authKey)) {
		$schedule = getUserSchedule($phone, $start_date, $end_date);


	if ($schedule !=null) {
			try {
				$response = $app->response();
	    $response['Content-Type'] = 'application/json';
		$response->body(json_encode($schedule));

	}
	catch(PDOException $e) {
	   error_log($e->getMessage(), 3, '/var/tmp/php.log');
			//echo '{"error":{"text":'. $e->getMessage() .'},"message":'. $update .'}';
			$schedule = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');
	        $response = $app->response();
	    $response['Content-Type'] = 'application/json';
		$response->body(json_encode($schedule));
	} }
	else
	{
     $schedule = array('Response_Type' => 'Error', 'Response_Message' => 'No Schedule Found');
	    $response = $app->response();
	    $response['Content-Type'] = 'application/json';
		$response->body(json_encode($schedule));
	}


	  }
	  else
	{
     $schedule = array('Response_Type' => 'Error', 'Response_Message' => 'Invalid Auth Key. If you are using our mobile app, please contact 9873805309');
	    $response = $app->response();
	    $response['Content-Type'] = 'application/json';
		$response->body(json_encode($schedule));
	}

		}




		function registerProvider() {

	$app = \Slim\Slim::getInstance();

	$request = $app->request();
	$update = json_decode($request->getBody());

	$name = $update->name;
	$phone = $update->phone;
	$email = $update->email;
	$password = $update->password;

	$user = getPUser($phone);

	if ($user !=null) {
			$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'The mobile number '.$phone.' is already registered');
	} else {
		$sql = "INSERT INTO PROVIDER (NAME, PHONE_NUM, EMAIL_ADDRESS, PASSWORD, CREATED_DT, LAST_UPDATED_DT) VALUES (:name, :phone, :email, :password, :createdDt, :lastUpdatedDt)";
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

function loginProvider() {

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

	$user = checkPUserNameAndPassword($phone, $password);

	if ($user != null) {
	$providerId = $user[0]->PROVIDER_ID;
	$emailAddr = $user[0]->EMAIL_ADDRESS;
		$providerUser = getProviderLoginForUser($providerId);

		$providerAuthKey = md5(uniqid($emailAddr.$time, false));
		if ($providerUser != null) {
			updateInProviderLogin($providerUser[0]->PROVIDER_LOGIN_ID,$providerAuthKey, $pushDeviceId, $deviceOS, $osVersion, $ip);
		} else {
			insertInProviderLogin($providerId, $providerAuthKey, $pushDeviceId, $deviceOS, $osVersion, $ip);
		}

		$dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'Login Successful', 'Auth_Key' => $providerAuthKey);
	} else {
		$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'The credentials provided are invalid. Kindly verify your phone number and password');
	}
	$response = $app->response();
	$response['Content-Type'] = 'application/json';
    $response->body(json_encode($dataArray));
}
function updateProvider(){
	$app = \Slim\Slim::getInstance();

	$request = $app->request();
	$update = json_decode($request->getBody());

	$name = $update->name;
	$phone = $update->phone;
	$email = $update->email;
	$bdate= $update->bdate;
	$height= $update->height;
	$weight= $update->weight;
	$address= $update->address;
	$gender= $update->gender;
	$authKey= $update->authKey;
	$degree= $update->degree;
	$institute= $update->institute;
	$course_type= $update->course_type;
	$batch_start= $update->batch_start;
	$batch_end= $update->batch_end;
	$experience= $update->experience;
	if (authenticateProvider($phone,$authKey)) {


        $sql = "UPDATE PROVIDER SET NAME = :name, EMAIL_ADDRESS = :email,BIRTH_DATE = :bdate, HEIGHT = :height, WEIGHT = :weight, ADDRESS =:address, GENDER =:gender, EXPERIENCE =:experience, LAST_UPDATED_DT= :lastUpdatedDt WHERE PHONE_NUM = :phone";
        $sql1="INSERT INTO QUALIFICATION (PROVIDER_ID, DEGREE, INSTITUTE, COURSE_TYPE, BATCH_START, BATCH_END) VALUES (:providerId, :degree, :institute, :course_type, :batch_start, :batch_end) " ;
		$sql2 = "SELECT PROVIDER_ID FROM PROVIDER where PHONE_NUM ='".$phone."' ";


	    $db = getDB();
		$stmt = $db->query($sql2);
		$stmt->bindParam("phone", $phone);
		$providerId = $stmt->fetchColumn(0);
		$db=null;
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
						//$stmt->bindParam("qualification", $qualification);
						$stmt->bindParam("experience", $experience);
						date_default_timezone_set('Asia/Kolkata');
			$time = date('Y/m/d H:i:s');
			//$stmt->bindParam("createdDt", $time);
			$stmt->bindParam("lastUpdatedDt", $time);
			$stmt->execute();
			$stmt = $db->prepare($sql1);
			$stmt->bindParam("providerId", $providerId);
			$stmt->bindParam("degree", $degree);
			$stmt->bindParam("institute", $institute);
			$stmt->bindParam("course_type",$course_type);
			$stmt->bindParam("batch_start", $batch_start);
			$stmt->bindParam("batch_end", $batch_end);
			$stmt->execute();
			$db = null;
			$dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'Profile Successfully Updated');


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


function changePasswordPro()
	  { $app = \Slim\Slim::getInstance();
        $request = $app->request();
	    $update = json_decode($request->getBody());
        $phone = $update->phone;
		$authKey= $update->authKey;
		$oldpass= $update->oldpass;
		$newpass= $update->newpass;
				if (authenticateProvider($phone,$authKey) && checkPUserNameAndPassword ($phone, $oldpass))

		{
			{ $sql = "UPDATE PROVIDER SET PASSWORD = :newpass, LAST_UPDATED_DT= :lastUpdatedDt WHERE PHONE_NUM = :phone";
		try
				{ $db = getDB();
			      $stmt = $db->prepare($sql);
			      $stmt->bindParam("newpass", $newpass);
				  date_default_timezone_set('Asia/Kolkata');
			      $time = date('Y/m/d H:i:s');
			      $stmt->bindParam("lastUpdatedDt", $time);
				  $stmt->bindParam("phone", $phone);
			      $stmt->execute();
			      $db = null;
			      $dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'Password Successfully Updated');

				}
				catch(PDOException $e) {
			error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
			//echo '{"error":{"text":'. $e->getMessage() .'},"message":'. $update .'}';
			$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');
		}
			}


		}
		else {
		$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'Incorrect Current Password or Auth Key. If you are using our mobile app, please contact 9873805309');
	}

	$response = $app->response();
	$response['Content-Type'] = 'application/json';
    $response->body(json_encode($dataArray));
	  }

	  function forgotPasswordPro()
	  { $app = \Slim\Slim::getInstance();
        $request = $app->request();
	    $update = json_decode($request->getBody());
		$phone = $update->phone;
		if(getPUser($phone))
		{ $newpass=randomPassword();

	     $sql = "SELECT EMAIL_ADDRESS FROM PROVIDER where PHONE_NUM ='".$phone."' ";
		 $sql1="UPDATE PROVIDER SET PASSWORD = :newpass, LAST_UPDATED_DT= :lastUpdatedDt WHERE PHONE_NUM = :phone";
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
			      $stmt->execute();
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

			$dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'A New Password Has been sent to your registered mail id');

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

		function getMySchedulePro()


		{
			$app = \Slim\Slim::getInstance();
            $request = $app->request();
	        $update = json_decode($request->getBody());
			$phone = $update->phone;
			$authKey= $update->authKey;
			$start_date= $update->start_date;
			$end_date= $update->end_date;
			if (authenticateProvider($phone,$authKey)) {
		$schedule = getPUserSchedule($phone, $start_date, $end_date);


	if ($schedule !=null) {
			try {
				$response = $app->response();
	    $response['Content-Type'] = 'application/json';
		$response->body(json_encode($schedule));

	}
	catch(PDOException $e) {
	   error_log($e->getMessage(), 3, '/var/tmp/php.log');
			//echo '{"error":{"text":'. $e->getMessage() .'},"message":'. $update .'}';
			$user = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');
	        $response = $app->response();
	    $response['Content-Type'] = 'application/json';
		$response->body(json_encode($schedule));
	} }
	else
	{
     $schedule = array('Response_Type' => 'Error', 'Response_Message' => 'No Schedule Found');
	    $response = $app->response();
	    $response['Content-Type'] = 'application/json';
		$response->body(json_encode($schedule));
	}


	  }
	  else
	{
     $schedule = array('Response_Type' => 'Error', 'Response_Message' => 'Invalid Auth Key. If you are using our mobile app, please contact 9873805309');
	    $response = $app->response();
	    $response['Content-Type'] = 'application/json';
		$response->body(json_encode($schedule));
	}

		}

		function getPendingRequest()
		{

	$app = \Slim\Slim::getInstance();

	$request = $app->request();
	$update = json_decode($request->getBody());
	$sql = "SELECT  cs.START_DATE,  cs.END_DATE,  c.NAME, c.PHONE_NUM,  cs.VENUE, csd.START_TIME, csd.SCHEDULE_ID, csd.END_TIME FROM CONSUMER_SCHEDULE cs, CONSUMER c, CONSUMER_SCHEDULE_DATE csd where cs.PROVIDER_ID IS null and c.CONSUMER_ID = cs.CONSUMER_ID and cs.SCHEDULE_ID = csd.SCHEDULE_ID group by csd.SCHEDULE_ID ";
	try {
		$db = getDB();
		$stmt = $db->query($sql);
		$requests = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;

	} catch(PDOException $e) {
	    error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
		$requests = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');

	}
		  $response = $app->response();
	    $response['Content-Type'] = 'application/json';
		$response->body(json_encode($requests));
		}

 		function updateRequest()
		{ $app = \Slim\Slim::getInstance();
          $request = $app->request();
	      $update = json_decode($request->getBody());
		  $phone = $update->phone;
		  $authKey= $update->authKey;
		  $map_status= $update->map_status;
		  $con_phone= $update->con_phone;
		  $schedule_id= $update->schedule_id;
		  		  if(authenticateProvider($phone,$authKey))
		  {  if($map_status=="accepted")
			  {   $sql = "SELECT PROVIDER_ID FROM PROVIDER where PHONE_NUM ='".$phone."'";
		  $sql1 = "SELECT CONSUMER_ID FROM CONSUMER where PHONE_NUM ='".$con_phone."'";
		  $sql2="INSERT INTO CONSUMER_PROVIDER_MAP (SCHEDULE_ID, CONSUMER_ID, PROVIDER_ID, MAP_STATUS) VALUES (:schedule_id, :consumer_id, :provider_id, :map_status) ";
          $sql3="UPDATE CONSUMER_SCHEDULE SET PROVIDER_ID =:provider_id WHERE SCHEDULE_ID =:schedule_id";
        try
			{ $db = getDB();
			$stmt = $db->query($sql);
		$stmt->bindParam("phone", $phone);
		$provider_id = $stmt->fetchColumn(0);

		$stmt = $db->query($sql1);
		$stmt->bindParam("phone", $con_phone);
		$consumer_id = $stmt->fetchColumn(0);
		      $stmt = $db->prepare($sql2);
		      $stmt->bindParam("schedule_id", $schedule_id);
			  $stmt->bindParam("consumer_id", $consumer_id);
			  $stmt->bindParam("provider_id", $provider_id);
			   $stmt->bindParam("map_status", $map_status);
			  $stmt->execute();
			  $stmt = $db->prepare($sql3);
		      $stmt->bindParam("schedule_id", $schedule_id);
			   $stmt->bindParam("provider_id", $provider_id);
			  $stmt->execute();
			  $db=null;
			  $dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'Class has been accepted');
			}
			 catch(PDOException $e) {
	    error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
		$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');

			  }


		  }
	else if($map_status=="rejected")
	{     $sql = "SELECT PROVIDER_ID FROM PROVIDER where PHONE_NUM ='".$phone."'";
		  $sql1 = "SELECT CONSUMER_ID FROM CONSUMER where PHONE_NUM ='".$con_phone."'";
     $sql4="INSERT INTO CONSUMER_PROVIDER_MAP (SCHEDULE_ID, CONSUMER_ID, PROVIDER_ID, MAP_STATUS) VALUES (:schedule_id, :consumer_id, :provider_id, :map_status) ";
	try
			{ $db = getDB();
			$stmt = $db->query($sql);
		$stmt->bindParam("phone", $phone);
		$provider_id = $stmt->fetchColumn(0);

		$stmt = $db->query($sql1);
		$stmt->bindParam("phone", $con_phone);
		$consumer_id = $stmt->fetchColumn(0);
		      $stmt = $db->prepare($sql4);
              $stmt->bindParam("schedule_id", $schedule_id);
			  $stmt->bindParam("consumer_id", $consumer_id);
			  $stmt->bindParam("provider_id", $provider_id);
              $stmt->bindParam("map_status", $map_status);
			  $stmt->execute();
			  $db=null;
			  $dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'Class has been rejected');
			}
			 catch(PDOException $e) {
	    error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
		$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');

			  }
	}



		  }
			else {
		$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'Incorrect Current Password or Auth Key. If you are using our mobile app, please contact 9873805309');
	}

	$response = $app->response();
	$response['Content-Type'] = 'application/json';
    $response->body(json_encode($dataArray));

		}

		function welcomeConsumer()
		{ $app = \Slim\Slim::getInstance();
          $request = $app->request();
	      $update = json_decode($request->getBody());
		  $phone = $update->phone;
		  $sql="SELECT NAME,ADDRESS from CONSUMER where PHONE_NUM='".$phone."'";
		 try
         {
		      $db = getDB();
		      $stmt = $db->query($sql);
		      $user = $stmt->fetchAll(PDO::FETCH_OBJ);
		      $db = null;

		 }
		 catch(PDOException $e) {
	    error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
		$user = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');

			  }

			$response = $app->response();
	        $response['Content-Type'] = 'application/json';
            $response->body(json_encode($user));
		}
		function welcomeProvider()
		{ $app = \Slim\Slim::getInstance();
          $request = $app->request();
	      $update = json_decode($request->getBody());
		  $phone = $update->phone;
		  $sql="SELECT NAME,ADDRESS from PROVIDER where PHONE_NUM='".$phone."'";
		 try
         {
		      $db = getDB();
		      $stmt = $db->query($sql);
		      $user = $stmt->fetchAll(PDO::FETCH_OBJ);
		      $db = null;

		 }
		 catch(PDOException $e) {
	    error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
		$user = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');

			  }

			$response = $app->response();
	        $response['Content-Type'] = 'application/json';
            $response->body(json_encode($user));
		}
		function addClass()
		{ $app = \Slim\Slim::getInstance();
          $request = $app->request();
	     $update = json_decode($request->getBody());
		  $phone = $update->phone;
		  $authKey= $update->authKey;
		 $venue= $update->venue;
		  $venue_lat= $update->venue_lat;
		  $venue_long= $update->venue_long;
		  $start_date= $update->start_date;
		  $end_date= $update->end_date;
		 $start_time= $update->start_time;
		 $end_time= $update->end_time;
		 $days= $update->days;
		 $mon=$days[0];
		  $tue=$days[1];
		  $wed=$days[2];
		  $thurs=$days[3];
		  $fri=$days[4];
		  $sat=$days[5];
		  $sun=$days[6];
		  if(authenticateConsumer($phone,$authKey))
		  { $sql = "SELECT CONSUMER_ID FROM CONSUMER where PHONE_NUM ='".$phone."'";
	        $sql1 = "INSERT INTO CONSUMER_SCHEDULE (PROFILE, CONSUMER_ID, VENUE, VENUE_LAT, VENUE_LONG, START_DATE, END_DATE, MON, TUE, WED, THURS, FRI, SAT, SUN) VALUES('CONSUMER', :consumer_id, :venue, :venue_lat, :venue_long, :start_date, :end_date, :mon, :tue, :wed, :thurs, :fri, :sat, :sun)";
	        try
			{ $db = getDB();
		$stmt = $db->query($sql);
		$stmt->bindParam("phone", $phone);
		$consumer_id = $stmt->fetchColumn(0);
		$stmt = $db->prepare($sql1);
		$stmt->bindParam("consumer_id", $consumer_id);
		$stmt->bindParam("venue", $venue);
		$stmt->bindParam("venue_lat", $venue_lat);
		$stmt->bindParam("venue_long", $venue_long);
		$stmt->bindParam("start_date", $start_date);
		$stmt->bindParam("end_date", $end_date);
		$stmt->bindParam("mon", $mon);
		$stmt->bindParam("tue", $tue);
		$stmt->bindParam("wed", $wed);
		$stmt->bindParam("thurs", $thurs);
		$stmt->bindParam("fri", $fri);
		$stmt->bindParam("sat", $sat);
		$stmt->bindParam("sun", $sun);

			  $stmt->execute();
			  $scheduleId = $db->lastInsertId();
			  $db=null;
			  createScheduleDate($start_date, $start_time, $end_time, $days, $scheduleId);
			  $dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'Class has been successfully booked'); }
			catch(PDOException $e) {
			error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
			//echo '{"error":{"text":'. $e->getMessage() .'},"message":'. $update .'}';
			$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');
		}




		  }
		  else
		{  $dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'Invalid Auth Key');

		}
		  $response = $app->response();
	        $response['Content-Type'] = 'application/json';
            $response->body(json_encode($dataArray));



		}

		function createProviderSchedule()
		{ $app = \Slim\Slim::getInstance();
          $request = $app->request();
	     $update = json_decode($request->getBody());
		  $phone = $update->phone;
		  $authKey= $update->authKey;
		  $name= $update->name;
		  $con_phone= $update->con_phone;
		 $venue= $update->venue;
		  $venue_lat= $update->venue_lat;
		  $venue_long= $update->venue_long;
		  $start_date= $update->start_date;
		  $end_date= $update->end_date;
		 $start_time= $update->start_time;
		 $end_time= $update->end_time;
		 $days= $update->days;
		 $mon=$days[0];
		  $tue=$days[1];
		  $wed=$days[2];
		  $thurs=$days[3];
		  $fri=$days[4];
		  $sat=$days[5];
		  $sun=$days[6];

		  if(authenticateProvider($phone,$authKey))
		  { $con_id=InsertConsumerByProvider($name, $con_phone, $venue);
	        if($con_id!=null)
			{

	        $sql = "SELECT PROVIDER_ID FROM PROVIDER where PHONE_NUM ='".$phone."'";
	        $sql1 = "INSERT INTO CONSUMER_SCHEDULE (PROFILE, CONSUMER_ID, PROVIDER_ID, VENUE, VENUE_LAT, VENUE_LONG, START_DATE, END_DATE, MON, TUE, WED, THURS, FRI, SAT, SUN) VALUES('PROVIDER', :consumer_id, :provider_id, :venue, :venue_lat, :venue_long, :start_date, :end_date, :mon, :tue, :wed, :thurs, :fri, :sat, :sun)";
	        try
			{ $db = getDB();
		$stmt = $db->query($sql);
		$stmt->bindParam("phone", $phone);
		$provider_id = $stmt->fetchColumn(0);
		$stmt = $db->prepare($sql1);
		$stmt->bindParam("consumer_id", $con_id);
		$stmt->bindParam("provider_id", $provider_id);
		$stmt->bindParam("venue", $venue);
		$stmt->bindParam("venue_lat", $venue_lat);
		$stmt->bindParam("venue_long", $venue_long);
		$stmt->bindParam("start_date", $start_date);
		$stmt->bindParam("end_date", $end_date);
		$stmt->bindParam("mon", $mon);
		$stmt->bindParam("tue", $tue);
		$stmt->bindParam("wed", $wed);
		$stmt->bindParam("thurs", $thurs);
		$stmt->bindParam("fri", $fri);
		$stmt->bindParam("sat", $sat);
		$stmt->bindParam("sun", $sun);

			  $stmt->execute();
			  $scheduleId = $db->lastInsertId();
			  $db=null;
			  createScheduleDate($start_date, $start_time, $end_time, $days, $scheduleId);
			  $dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'Schedule has been successfully uploaded'); }
			catch(PDOException $e) {
			error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
			//echo '{"error":{"text":'. $e->getMessage() .'},"message":'. $update .'}';
			$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');
		}



			}
           else
           {
			   $dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');
		   }
		  }
		  else
		{  $dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'Invalid Auth Key');

		}
		  $response = $app->response();
	        $response['Content-Type'] = 'application/json';
            $response->body(json_encode($dataArray));



		}

	  function viewMyClients()
	  { $app = \Slim\Slim::getInstance();
          $request = $app->request();
	     $update = json_decode($request->getBody());
		  $phone = $update->phone;
		  $authKey= $update->authKey;
		  if(authenticateProvider($phone, $authKey))
		  {
		  $sql = "SELECT PROVIDER_ID FROM PROVIDER where PHONE_NUM ='".$phone."'";
		  $db = getDB();
		$stmt = $db->query($sql);
		$stmt->bindParam("phone", $phone);
		$db=null;
		$provider_id = $stmt->fetchColumn(0);
		  $sql1 = "SELECT c.NAME, c.PHONE_NUM, c.ADDRESS, cs.START_DATE, cs.END_DATE FROM CONSUMER c, CONSUMER_SCHEDULE cs where cs.PROVIDER_ID ='".$provider_id."' and cs.CONSUMER_ID = c.CONSUMER_ID ";
		  try
			{ $db = getDB();
			$stmt = $db->query($sql1);
		   $myClients = $stmt->fetchAll(PDO::FETCH_OBJ);
		    $db=null;
			}
			catch(PDOException $e) {
			error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
			//echo '{"error":{"text":'. $e->getMessage() .'},"message":'. $update .'}';
			$myClients = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');
		  } }
		  else
		  {
			  $myClients = array('Response_Type' => 'Error', 'Response_Message' => 'Invalid Auth Key');

		  }
            $response = $app->response();
	        $response['Content-Type'] = 'application/json';
            $response->body(json_encode($myClients));
	  }

function updateClassStatus(){
	$app = \Slim\Slim::getInstance();
	$request = $app->request();
	$update = json_decode($request->getBody());

	$scheduleDateId = $update->scheduleDateId;
	$scheduleStatus = $update->scheduleStatus;

	$phone = $update->phone;
	$authKey= $update->authKey;

	if(authenticateConsumer($phone, $authKey))
	{

	$sql="UPDATE CONSUMER_SCHEDULE_DATE SET CLASS_STATUS =:scheduleStatus WHERE SCHEDULE_DATE_ID =:scheduleDateId";

	try {
			$db = getDB();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("scheduleStatus", $scheduleStatus);
			$stmt->bindParam("scheduleDateId", $scheduleDateId);
			$stmt->execute();
			$db = null;
			$dataArray = array('Response_Type' => 'Success', 'Response_Message' => 'Successfully updated the class attended status');

		} catch(PDOException $e) {
			error_log($e->getMessage(), 3, '/var/tmp/php.log');
			//echo '{"error":{"text":'. $e->getMessage() .'},"message":'. $update .'}';
			$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'We are unable to server your request at present. Kindly contact us at 9873805309');
	}
	} else {
		$dataArray = array('Response_Type' => 'Error', 'Response_Message' => 'Authentication Error. Kindly contact us at 9873805309');
	}

	$response = $app->response();
	$response['Content-Type'] = 'application/json';
    $response->body(json_encode($dataArray));
}
?>