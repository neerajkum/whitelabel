<?php
include_once 'db.php';
function getPUser($username) {
	$sql = "SELECT *  FROM PROVIDER where PHONE_NUM ='".$username."'";
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
function getPUserProfile($username) {
	$sql = "SELECT NAME,PHONE_NUM,EMAIL_ADDRESS,BIRTH_DATE,HEIGHT,WEIGHT,ADDRESS,GENDER,QULIFICATION,EXPERIENCE FROM PROVIDER where PHONE_NUM ='".$username."'";
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



function checkPUserNameAndPassword ($username, $password) {
	$sql = "SELECT * FROM PROVIDER where PHONE_NUM ='".$username."' and PASSWORD ='".$password."'";
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

function getProviderLoginForUser ($providerId) {
	$sql = "SELECT * FROM PROVIDER_LOGIN where PROVIDER_ID ='".$providerId."'";
	try {
		$db = getDB();
		$stmt = $db->query($sql);
		$providerLogin = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
	} catch(PDOException $e) {
		error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}';
		$providerLogin =  null;
	}
	return $providerLogin;
}

function updateInProviderLogin ($providerLoginId, $providerAuthKey, $pushDeviceId, $deviceOS, $osVersion, $ip) {
	$sql = "UPDATE PROVIDER_LOGIN SET AUTH_KEY = :authKey, PUSH_DEVICE_ID = :pushDeviceId, DEVICE_OS = :deviceOS, OS_VERSION = :osVersion, LOGIN_DATE = :loginDate, IP_ADDRESS = :ipAddress WHERE PROVIDER_LOGIN_ID = :providerLoginId";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("providerLoginId", $providerLoginId);
		$stmt->bindParam("authKey", $providerAuthKey);
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

	return $providerLoginId;
}

function insertInProviderLogin ($providerId, $providerAuthKey, $pushDeviceId, $deviceOS, $osVersion, $ip) {
	$sql = "INSERT INTO PROVIDER_LOGIN (PROVIDER_ID, AUTH_KEY, PUSH_DEVICE_ID, DEVICE_OS, OS_VERSION, LOGIN_DATE, IP_ADDRESS) VALUES (:providerId, :authKey, :pushDeviceId, :deviceOS, :osVersion, :loginDate, :ipAddress)";
	try {
		$db = getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("providerId", $providerId);
		$stmt->bindParam("authKey", $providerAuthKey);
		$stmt->bindParam("pushDeviceId", $pushDeviceId);
		$stmt->bindParam("deviceOS", $deviceOS);
		$stmt->bindParam("osVersion", $osVersion);
		$time = date('Y/m/d H:i:s');
		$stmt->bindParam("loginDate", $time);
		$stmt->bindParam("ipAddress", $ip);
		$stmt->execute();
		$providerLoginId = $db->lastInsertId();
		$db = null;
		//return $users;
	} catch(PDOException $e) {
		error_log($e->getMessage(), 3, 'C:\xampp\php\logs\php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}';
		$providerLoginId = null;
	}

	return $providerLoginId;
}

function authenticateProvider ($username, $providerAuthKey) {
	$sql = "SELECT p.provider_id FROM provider p, provider_login pl WHERE pl.provider_id = p.provider_id AND p.phone_num = '".$username."' AND pl.AUTH_KEY = '".$providerAuthKey."'";
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


function getPUserSchedule($username)
{ $sql = "SELECT psd.SCHEDULE_DATE,ps.CLIENT_NAME,ps.CLIENT_PHN,psd.START_TIME,psd.END_TIME,ps.VENUE,psd.CLASS_STATUS FROM PROVIDER p,PROVIDER_SCHEDULE ps,PROVIDER_SCHEDULE_DATE psd where  p.PHONE_NUM ='".$username."' and p.PROVIDER_ID=ps.PROVIDER_ID and ps.PROVIDER_SCHEDULE_ID=psd.PROVIDER_SCHEDULE_ID ";
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