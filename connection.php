<?php

/* code snipet to connect mysql database on php */

$servername = "127.0.0.1";
$username 	= "root";
$password 	= "";
$dbname 	= "employee";

$conn = mysqli_connect($servername, $username, $password, $dbname);

/* check connection, exit if failed and return error*/
if (!$conn)
{
	die("Connection FAILED:" . mysqli_connect_error());
}
//echo "Connection OK"

?>