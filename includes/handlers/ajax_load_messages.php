<?php
include("../../config/config.php"); // database configuration file
include("../classes/User.php");
include("../classes/Message.php");

$limit = 7; // Number of messages to load

$message = new Message($con, $_REQUEST['userLoggedIn']);
echo $message->getConvosDropdown($_REQUEST, $limit);;

?>