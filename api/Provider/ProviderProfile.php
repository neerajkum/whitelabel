<?php
include_once 'db.php';
include_once 'Consumer/ConsumerProfile.php';
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


function getPUserSchedule($username, $startDate, $endDate)
{      $sql1="SELECT csd.SCHEDULE_ID, csd.SCHEDULE_DATE, csd.START_TIME, csd.END_TIME,csd.CLASS_STATUS FROM CONSUMER_SCHEDULE_DATE csd, CONSUMER_SCHEDULE cs, PROVIDER p where csd.SCHEDULE_DATE BETWEEN '".$startDate."' and '".$endDate."' and csd.SCHEDULE_ID=cs.SCHEDULE_ID and cs.PROVIDER_ID=p.PROVIDER_ID and p.PHONE_NUM='".$username."' order by csd.SCHEDULE_DATE";
       $db = getDB();
	   $stmt = $db->query($sql1);
	   $schedule = $stmt->fetchAll(PDO::FETCH_OBJ);
	   return $schedule;

}

function InsertConsumerByProvider($name, $phone, $address)
{
$user = getUser($phone);

	if ($user !=null) {
			$sql = "SELECT CONSUMER_ID FROM CONSUMER where PHONE_NUM='".$phone."'";
			$db = getDB();
			$stmt = $db->query($sql);
		    $id = $stmt->fetchColumn(0);
	}
else {
$sql = "INSERT INTO CONSUMER (NAME, PHONE_NUM, EMAIL_ADDRESS, PASSWORD, ADDRESS, CREATED_DT, LAST_UPDATED_DT) VALUES (:name, :phone, 'email', 'password', :address, :createdDt, :lastUpdatedDt)";
		try {
			$db = getDB();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("name", $name);
			$stmt->bindParam("phone", $phone);
			$stmt->bindParam("address", $address);
			date_default_timezone_set('Asia/Kolkata');
			$time = date('Y/m/d H:i:s');
			$stmt->bindParam("createdDt", $time);
			$stmt->bindParam("lastUpdatedDt", $time);
			$stmt->execute();
			$id = $db->lastInsertId();
			$db = null;


		} catch(PDOException $e) {
			error_log($e->getMessage(), 3, '/var/tmp/php.log');
			//echo '{"error":{"text":'. $e->getMessage() .'},"message":'. $update .'}';
			$id= null;
} }
		return $id;
	}


?>