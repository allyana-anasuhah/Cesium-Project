<?php

include_once('db/lib/dbPdo.class.php');

//db connect
$DB_SERVERNAME = "localhost\SQLEXPRESS";
$DB_USERNAME = "admin";
$DB_PASSWORD = "1234pass";
$DB_DBNAME = "Cesium_Apps";

$connArr = array('dsn'=>"sqlsrv:Server=$DB_SERVERNAME;database=$DB_DBNAME", 'username'=>$DB_USERNAME, 'pass'=>$DB_PASSWORD);


global $CONN, $CON;
$CONN  = new dbconnect($connArr);

$connectionInfo = array("Database" => $DB_DBNAME, "UID" => $DB_USERNAME, "PWD" => $DB_PASSWORD);
$CON = sqlsrv_connect($DB_SERVERNAME, $connectionInfo);

if ($CON) {
} else {
    die(print_r(sqlsrv_errors(), true));
}
?>