<?php
include_once 'db.php';

function getUser($username) {
	$sql = "SELECT * FROM CONSUMER where PHONE_NUM ='".$username."'";
	try {
		$db = getDB();
		$stmt = $db->query($sql);
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		return $users;
	} catch(PDOException $e) {
	    error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}';
		return null;
	}
}

function getUserProfile($username) {
	$sql = "SELECT NAME,PHONE_NUM,EMAIL_ADDRESS,BIRTH_DATE,HEIGHT,WEIGHT,ADDRESS,GENDER FROM CONSUMER where PHONE_NUM ='".$username."'";
	try {
		$db = getDB();
		$stmt = $db->query($sql);
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		return $users;
	} catch(PDOException $e) {
	    error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}';
		return null;
	}
}



function checkUserNameAndPassword ($username, $password) {
	$sql = "SELECT * FROM CONSUMER where PHONE_NUM ='".$username."' and PASSWORD ='".$password."'";
	try {
		$db = getDB();
		$stmt = $db->query($sql);
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		//return $users;
	} catch(PDOException $e) {
		error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}';
		$userrs =  null;
	}

	return $users;
}

function getConsumerLoginForUser ($consumerId) {
	$sql = "SELECT * FROM CONSUMER_LOGIN where CONSUMER_ID ='".$consumerId."'";
	try {
		$db = getDB();
		$stmt = $db->query($sql);
		$consumerLogin = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
	} catch(PDOException $e) {
		error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}';
		$consumerLogin =  null;
	}
	return $consumerLogin;
}

function updateInConsumerLogin ($consumerLoginId, $consumerAuthKey, $pushDeviceId, $deviceOS, $osVersion, $ip) {
	$sql = "UPDATE CONSUMER_LOGIN SET AUTH_KEY = :authKey, PUSH_DEVICE_ID = :pushDeviceId, DEVICE_OS = :deviceOS, OS_VERSION = :osVersion, LOGIN_DATE = :loginDate, IP_ADDRESS = :ipAddress WHERE CONSUMER_LOGIN_ID = :consumerLoginId";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("consumerLoginId", $consumerLoginId);
		$stmt->bindParam("authKey", $consumerAuthKey);
		$stmt->bindParam("pushDeviceId", $pushDeviceId);
		$stmt->bindParam("deviceOS", $deviceOS);
		$stmt->bindParam("osVersion", $osVersion);
		$time = date('Y/m/d H:i:s');
		$stmt->bindParam("loginDate", $time);
		$stmt->bindParam("ipAddress", $ip);
		$stmt->execute();
		$db = null;
		//return $users;
	} catch(PDOException $e) {
		error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}

	return $consumerLoginId;
}

function insertInConsumerLogin ($consumerId, $consumerAuthKey, $pushDeviceId, $deviceOS, $osVersion, $ip) {
	$sql = "INSERT INTO CONSUMER_LOGIN (CONSUMER_ID, AUTH_KEY, PUSH_DEVICE_ID, DEVICE_OS, OS_VERSION, LOGIN_DATE, IP_ADDRESS) VALUES (:consumerId, :authKey, :pushDeviceId, :deviceOS, :osVersion, :loginDate, :ipAddress)";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("consumerId", $consumerId);
		$stmt->bindParam("authKey", $consumerAuthKey);
		$stmt->bindParam("pushDeviceId", $pushDeviceId);
		$stmt->bindParam("deviceOS", $deviceOS);
		$stmt->bindParam("osVersion", $osVersion);
		$time = date('Y/m/d H:i:s');
		$stmt->bindParam("loginDate", $time);
		$stmt->bindParam("ipAddress", $ip);
		$stmt->execute();
		$consumerLoginId = $db->lastInsertId();
		$db = null;
		//return $users;
	} catch(PDOException $e) {
		error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}';
		$consumerLoginId = null;
	}

	return $consumerLoginId;
}

function authenticateConsumer ($username, $consumerAuthKey) {
	$sql = "SELECT c.consumer_id FROM consumer c, consumer_login cl WHERE cl.consumer_id = c.consumer_id AND c.phone_num = '".$username."' AND cl.AUTH_KEY = '".$consumerAuthKey."'";
	try {
		$db = getDB();
		$stmt = $db->query($sql);
		$authenticatedUser = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		//return $users;
	} catch(PDOException $e) {
		error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}';
		$authenticatedUser = null;
	}

	if ($authenticatedUser != null) {
		return true;
	} else {
		return false;
	}
}
function randomPassword() {
   $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 10; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getUserSchedule($username,$startDate,$endDate)
{      $sql1="SELECT csd.SCHEDULE_DATE_ID, csd.SCHEDULE_ID, csd.SCHEDULE_DATE, csd.START_TIME, csd.END_TIME, csd.CLASS_STATUS, cs.VENUE FROM CONSUMER_SCHEDULE_DATE csd, CONSUMER_SCHEDULE cs, CONSUMER c where csd.SCHEDULE_DATE BETWEEN '".$startDate."' and '".$endDate."' and csd.SCHEDULE_ID=cs.SCHEDULE_ID and cs.CONSUMER_ID=c.CONSUMER_ID and c.PHONE_NUM='".$username."' order by csd.SCHEDULE_DATE";
       $db = getDB();
	   $stmt = $db->query($sql1);
	   $schedule = $stmt->fetchAll(PDO::FETCH_OBJ);
	   return $schedule;

}

function createScheduleDate($startDate, $startTime, $endTime, $days, $scheduleId){

	$initialDates = getInitialDateArray ($days, $startDate);

	$count = 0;

	for ($j=0; $j < 4 ; $j++)
	{	for ($i = 0; $i < count($initialDates) ; $i++) {
			$currentDate = $initialDates[$i];

			$newTime = strtotime(date("d-m-Y", $currentDate) . '+ '.$count.'days');

			$newDate = date("Y-m-d", $newTime);

			error_log(" newDate ".$newDate, 3, 'C:\xampp\php\logs\php.log');

			$sql="INSERT INTO CONSUMER_SCHEDULE_DATE (SCHEDULE_ID, SCHEDULE_DATE, START_TIME, END_TIME) VALUES(:scheduleId, :newDate, :startTime, :endTime )";

			$db = getDB();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("scheduleId", $scheduleId);
		    $stmt->bindParam("newDate", $newDate);
			$stmt->bindParam("startTime", $startTime);
		    $stmt->bindParam("endTime", $endTime);
			$stmt->execute();
			$db=null;



	}
	$count = $count+7;
	}
}

function getInitialDateArray($days, $startDate) {

	$dw = date( "w", strtotime($startDate));

	$initialDays = getInitialDayArray($days);
	$initialDates = array();

	for ($i = 0; $i < count($initialDays) ; $i++) {

		$currentNumber = $initialDays[$i];

		$diff = $currentNumber - $dw;
		if ($diff < 0) {
			$diff = $diff +7;
		}

		$newDate = strtotime($startDate . '+ '.$diff.'days');
		array_push($initialDates,$newDate);

	}

	return $initialDates;
}

function getInitialDayArray($days) {
	$dayOfWeekArray = array();
	if ($days[0] == 'Y' || $days[0] == 'y') {
		array_push($dayOfWeekArray,1);

	}
	if ($days[1] == 'Y' || $days[1] == 'y') {
		array_push($dayOfWeekArray,2);

	}
	if ($days[2] == 'Y' || $days[2] == 'y') {
		array_push($dayOfWeekArray,3);

	}
	if ($days[3] == 'Y' || $days[3] == 'y') {
		array_push($dayOfWeekArray,4);

	}
	if ($days[4] == 'Y' || $days[4] == 'y') {
		array_push($dayOfWeekArray,5);

	}
	if ($days[5] == 'Y' || $days[5] == 'y') {
		array_push($dayOfWeekArray,6);

	}
	if ($days[6] == 'Y' || $days[6] == 'y') {
		array_push($dayOfWeekArray,0);
	}
	return $dayOfWeekArray;
}


?>