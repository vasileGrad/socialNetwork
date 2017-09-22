<?php
ob_start(); // Turns on output buffering
session_start();

$timezone = date_default_timezone_set("Europe/Rome");

$con = mysqli_connect("localhost", "root", "", "social"); // Connection variable

if(mysqli_connect_error())
{
	echo "Failed to connect: " . mysqli_connect_error();
}


//ob_start(); // Turns on output buffering
// php is loaded to the browser in sections
// saves the php data when the page is loaded 
// and will pass all the php code to the browser at the end of the file
?>

