<?php
include 'db.php';

function getUser($username) {
	$sql = "SELECT  FROM CONSUMER where PHONE_NUM ='".$username."'";
	try {
		$db = getDB();
		$stmt = $db->query($sql);
		$users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		return $users;
	} catch(PDOException $e) {
	    error_log($e->getMessage(), 3, 'G:\xampp\php\logs\php.log');
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
	    error_log($e->getMessage(), 3, 'G:\xampp\php\logs\php.log');
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
		error_log($e->getMessage(), 3, 'G:\xampp\php\logs\php.log');
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
		error_log($e->getMessage(), 3, 'G:\xampp\php\logs\php.log');
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
		error_log($e->getMessage(), 3, 'G:\xampp\php\logs\php.log');
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
		error_log($e->getMessage(), 3, 'G:\xampp\php\logs\php.log');
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
		error_log($e->getMessage(), 3, 'G:\xampp\php\logs\php.log');
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

function getUserSchedule($username)
{ $sql = "SELECT csd.SCHEDULE_DATE,csd.START_TIME,csd.END_TIME,cs.TYPE_OF_SERVICE,cs.VENUE,p.NAME,p.PHONE_NUM,csd.CLASS_STATUS FROM CONSUMER c,CONSUMER_SCHEDULE cs,CONSUMER_SCHEDULE_DATE csd,PROVIDER p,CONSUMER_PROVIDER_MAP cpm where  c.PHONE_NUM ='".$username."' and c.CONSUMER_ID=cs.CONSUMER_ID and cs.CONSUMER_SCHEDULE_ID=csd.CONSUMER_SCHEDULE_ID and csd.CONSUMER_SCHEDULE_ID=cpm.CONSUMER_SCHEDULE_ID and cpm.PROVIDER_ID=p.PROVIDER_ID";
	try {
		$db = getDB();
		$stmt = $db->query($sql);
		$schedule = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		return $schedule;
	} catch(PDOException $e) {
	  //  error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}';
		return null;
	}
	
}
?>