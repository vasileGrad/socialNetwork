<?php  
include("../../config/config.php");
include("../classes/User.php");

// these two come from the POST request we past to this page
$query = $_POST['query'];
$userLoggedIn = $_POST['userLoggedIn'];// these are coming from demo.php file in the function getUser() - query:value, userLoggedIn:user  - these are passed in this page

$names = explode(" ", $query);

// !== - not equal the same value and the same type
// If there is _ we assume that is a username and we ckeck for a username
if(strpos($query, "_") !== false) 
	$usersReturned = mysqli_query($con, "SELECT * FROM users WHERE username LIKE '$query%' AND user_closed='no' LIMIT 8");
else if(count($names) == 2)  // we assume that we search for the first and last name
	$usersReturned = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' AND last_name LIKE '%$names[1]%') AND user_closed='no' LIMIT 8");
else // if we have 1 name in this array or more
	$usersReturned = mysqli_query($con, "SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' OR last_name LIKE '%$names[0]%') AND user_closed='no' LIMIT 8");

// If the query is not empty
if($query != "") { 
	while ($row = mysqli_fetch_array($usersReturned)) {
		
		$user = new User($con, $userLoggedIn);

		if($row['username'] != $userLoggedIn) // If we haven't found the result themselves
			$mutual_friends = $user->getMutualFriends($row['username']) ." friends in common";
		else
			$mutual_friends = "";

		// If they are friends
		if($user->isFriend($row['username']))
			echo "<div class='resultDisplay'>
					<a href='messages.php?u='" . $row['username'] . "' style='color: #fff'>
						<div class='liveSearchProfilePic'>
							<img src='". $row['profile_pic'] . "'>
						</div>

						<div class='liveSearchText'> ".$row['first_name'] . " " . $row['last_name']."
							<p style='margin: 0;'>". $row['username'] . "</p>
							<p id='grey'>". $mutual_friends . "</p>
						</div>
					<a>
				</div>";
	}
}


?>