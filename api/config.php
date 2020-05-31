<?php
$dbhost="localhost";
$dblogin="root";
$dbpass="";
$dbname="startatom";
$config["statlogin"]="iwannasomestat";
$config["statpass"]="gimmeit";
$config["statid"]=2;
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $db = new mysqli($dbhost, $dblogin, $dbpass, $dbname);
    $db->set_charset("utf8");
}
catch (Exception $e)
{
    error_log($e->getMessage());
    exit(json_encode(array("error"=>true,"errortext"=>"Ошибка при подключении к бд")));
}