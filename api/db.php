<?php
function getDB() {
	$dbhost="sql33.hostinger.in";
	$dbuser="u757636915_yom";
	$dbpass="yom@123";
	$dbname="u757636915_yom";
	$dbConnection = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
	$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbConnection;
}
?>