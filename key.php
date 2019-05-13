<?php
require_once("Rest.inc.php");
require_once("database_configuration.php");
date_default_timezone_set('america/sao_paulo');
$timestamp = time(); 
echo "\n"; 
echo(date("F d, Y h:i:s A", $timestamp)); 
echo "<br>";
$token = bin2hex(openssl_random_pseudo_bytes(8));
echo "Read Only Token valid for 1 hour "."<br>".$token;
$insert_query = "INSERT INTO token (
    token,ArrivalDate)";

$insert_query .= " values (
    '".$token."',
    now());";
//echo $insert_query;
mysqli_query($conn, $insert_query);



?>