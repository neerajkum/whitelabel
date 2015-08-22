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
		$users =  null;
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
	$sql = "SELECT C.CONSUMER_ID FROM CONSUMER C, CONSUMER_LOGIN C1 WHERE C1.CONSUMER_ID = C.CONSUMER_ID AND C.PHONE_NUM = '".$username."' AND C1.AUTH_KEY = '".$consumerAuthKey."'";
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
{      $sql1="SELECT csd.SCHEDULE_DATE_ID, csd.SCHEDULE_ID, csd.SCHEDULE_DATE, cs.VENUE, csd.START_TIME, csd.END_TIME, csd.CLASS_STATUS FROM CONSUMER_SCHEDULE_DATE csd, CONSUMER_SCHEDULE cs, CONSUMER c where csd.SCHEDULE_DATE BETWEEN '".$startDate."' and '".$endDate."' and csd.SCHEDULE_ID=cs.SCHEDULE_ID and cs.CONSUMER_ID=c.CONSUMER_ID and c.PHONE_NUM='".$username."' order by csd.SCHEDULE_DATE";
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

function insertNewDate($phone,$scheduleDate,$scheduleStatus)
{ $sql1="SELECT CONSUMER_ID FROM CONSUMER where PHONE_NUM='".$phone."'";
	$db = getDB();
		$stmt = $db->query($sql1);
		$consumer_id = $stmt->fetchColumn(0);
		$db=null;
	$sql2="SELECT SCHEDULE_ID FROM CONSUMER_SCHEDULE where CONSUMER_ID='".$consumer_id."'";
	$db = getDB();
		$stmt = $db->query($sql2);
		$schedule_id = $stmt->fetchColumn(0);
		$db=null;
	$sql3="SELECT * FROM CONSUMER_SCHEDULE_DATE where SCHEDULE_ID='".$schedule_id."'";
	$db = getDB();
	$stmt = $db->query($sql3);
		$startTime = $stmt->fetchColumn(3);
		$endTime = $stmt->fetchColumn(4);
		$db=null;
	$sql="INSERT INTO CONSUMER_SCHEDULE_DATE (SCHEDULE_ID, SCHEDULE_DATE, START_TIME, END_TIME, CLASS_STATUS) VALUES(:schedule_id, :scheduleDate, :startTime, :endTime, :scheduleStatus )";

			$db = getDB();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("schedule_id", $schedule_id);
		    $stmt->bindParam("scheduleDate", $scheduleDate);
			$stmt->bindParam("startTime", $startTime);
		    $stmt->bindParam("endTime", $endTime);
			$stmt->bindParam("scheduleStatus", $scheduleStatus);
			$stmt->execute();
			$db=null;
}
function findPromo($promo_code)
{ $sql="SELECT * FROM PROMO_TABLE where PROMO_CODE ='".$promo_code."'";
		  $db=getDB();
		  $stmt = $db->query($sql);
		  $stmt->bindParam("promo_code", $promo_code);
		  $result = $stmt->fetchAll(PDO::FETCH_OBJ);
		  $db = null;
		  return $result;
		  }
function checkpromo($consumer_id, $promo_code)
{ $sql="SELECT PROMO_CODE FROM CONSUMER_SCHEDULE where CONSUMER_ID ='".$consumer_id."'";
 $db=getDB();
 $stmt = $db->query($sql);
 $promo = $stmt->fetchColumn(0);
 $db = null;
if($promo == $promo_code)
return false;
else
return true;	
	
}
?>