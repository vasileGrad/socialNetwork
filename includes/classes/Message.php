<?php 
class Message {
	private $user_obj;
	private $con;

	public function __construct($con, $user){
		$this->con = $con;
		$this->user_obj = new User($con, $user);
	}

	public function getMostRecentUser() {
		$userLoggedIn = $this->user_obj->getUsername();

		// the userLoggedIn is the person who the message was to or the message was from 
		$query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to='$userLoggedIn' OR user_from ='$userLoggedIn' ORDER BY id DESC LIMIT 1"); // Get the most recent one

		if(mysqli_num_rows($query) == 0)
			return false;

		$row = mysqli_fetch_array($query);
		$user_to = $row['user_to'];
		$user_from = $row['user_from'];

		// Return which one of them is not the userLoggedIn
		if($user_to != $userLoggedIn)
			return $user_to;
		else
			return $user_from;
	}

	public function sendMessage($user_to, $body, $date) {

		if($body != "") {
			$userLoggedIn = $this->user_obj->getUsername();
			$query = mysqli_query($this->con, "INSERT INTO messages VALUES ('', '$user_to', '$userLoggedIn', '$body', '$date', 'no', 'no', 'no')");
		}
	}	

	public function getMessages($otherUser) {
		$userLoggedIn = $this->user_obj->getUsername();
		$data = "";

		$query = mysqli_query($this->con, "UPDATE messages SET opened='yes' WHERE user_to='$userLoggedIn' AND user_from='$otherUser'");

		// As long as there are messages between these users we will gonna retrieve it
		$get_messages_query = mysqli_query($this->con, "SELECT * FROM messages WHERE (user_to='$userLoggedIn' AND user_from='$otherUser') OR (user_from='$userLoggedIn' AND user_to='$otherUser')");

		// Iterate through the messages
		while($row = mysqli_fetch_array($get_messages_query)) {
			$user_to = $row['user_to'];
			$user_from = $row['user_from'];
			$body = $row['body'];

			// Change the color of the div to return depending on whether it was from the user logged in or to the user logged in

			// We are using conditional statement
			$div_top = ($user_to == $userLoggedIn) ? "<div class='message' id='green'>" : "<div class='message' id='blue'>";
			$data = $data . $div_top . $body . "</div><br><br>";			
		}
		return $data;
	}

	public function getLatestMessage($userLoggedIn, $user2) {
		$details_array = array();

		$query = mysqli_query($this->con, "SELECT body, user_to, date FROM messages WHERE (user_to='$userLoggedIn' AND user_from='$user2') OR (user_to='$user2' AND user_from='$userLoggedIn') ORDER BY id DESC LIMIT 1"); // Get the latest message

		$row = mysqli_fetch_array($query);
		$send_by = ($row['user_to'] == $userLoggedIn) ? "They said: " : "You said: ";

		// Timeframe
		$date_time_now = date("Y-m-d H:i:s");
		$start_date = new DateTime($row['date']); // Time of post
		$end_date = new DateTime($date_time_now); // Current time
		$interval = $start_date->diff($end_date); // Difference between dates

		// how longer it was posted
		// if it is one year over
		if($interval->y >= 1) {
			if($interval == 1)
				$time_message = $interval->y . " year ago"; // 1 year ago
			else
				$time_message = $interval->y . " years ago"; // 1+ year ago 
		}
		// if it's not at least one year ago
		else if($interval->m >= 1){
			if($interval->d == 0)
				$days = " ago";
			else if($interval->d == 1)
				$days = $interval->d . " day ago";
			else
				$days = $interval->d . " days ago";

			if($interval->m == 1)
				$time_message = $interval->m . " month". $days;
			else 
				$time_message = $interval->m . " months". $days;
		}
		// at least one day old
		else if($interval->d >= 1){
			if($interval->d == 1)
				$time_message = "Yesterday";
			else
				$time_message = $interval->d . " days ago";
		}
		// if it's not even a day old
		// if it's an hour old
		else if($interval->h >= 1){
			if($interval->h == 1)
				$time_message = $interval->h . " hour ago";
			else
				$time_message = $interval->h . " hours ago";
		}
		// if it's a minute old
		else if($interval->i >= 1){
			if($interval->i == 1)
				$time_message = $interval->i . " minute ago";
			else
				$time_message = $interval->i . " minutes ago";
		}
		// seconds ago
		else {
			if($interval->s < 30)
				$time_message = "Just now";
			else
				$time_message = $interval->s . " seconds ago";
		}

		array_push($details_array, $send_by);
		array_push($details_array, $row['body']);
		array_push($details_array, $time_message);

		return $details_array;
	}

	public function getConvos() {
		$userLoggedIn = $this->user_obj->getUsername();
		$return_string = "";
		$convos = array(); // Initialises with an empty array

		$query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to='$userLoggedIn' OR user_from='userLoggedIn' ORDER BY id DESC");

		while($row = mysqli_fetch_array($query)) {
			// We gonna add the name of the person's name to this array $convos
			$user_to_push = ($row['user_to'] != $userLoggedIn) ? $row['user_to'] : $row['user_from']; // we push the other one user

			// We check that the username is not in the array 
			if(!in_array($user_to_push, $convos)) {
				array_push($convos, $user_to_push); // add the usernames into the array
				// we have all the conversations that user had
			}
		}

		foreach ($convos as $username) {
			$user_found_obj = new User($this->con, $username); // the user object with that object
			// get the latest messeges between these two users
			$latest_message_details = $this->getLatestMessage($userLoggedIn, $username);
			// $this->getLatestMessage() - allows to call a different function within this class

			// Adding dots
			// If the length of the body id >= 12 characters than put ...
			$dots = (strlen($latest_message_details[1]) >= 12) ? "..." : "";
			$split = str_split($latest_message_details[1], 12); // split the amount of characters that you give it
			$split = $split[0] . $dots;  // The first 12 characters and than...

			$return_string .= "<a href='messages.php?u=$username'> <div class='user_found_messages'>
								<img src='" . $user_found_obj->getProfilePic() . "' style='border-radius: 5px; margin-right: 5px;'>
								" . $user_found_obj->getFirstAndLastName() . "
								<span class='timestamp_smaller' id='grey'> " . $latest_message_details[2] . "</span> 
								<p id='grey' style='margin: 0;'>" . $latest_message_details[0] . $split . " </p>
								</div>
								</a>";

		}

		return $return_string;

	}

	public function getConvosDropdown($data, $limit) {

		$page = $data['page'];
		$userLoggedIn = $this->user_obj->getUsername();
		$return_string = "";
		$convos = array(); // Initialises with an empty array

		// If it is the first page
		if($page == 1) 
			$start = 0;
		else  // If it is not the first page
			$start = ($page -1) * limit;

		// set the viewed column to 'yes' in the messages database
		$set_viewed_query = mysqli_query($this->con, "UPDATE messages SET viewed='yes' WHERE user_to='$userLoggedIn'");

		$query = mysqli_query($this->con, "SELECT user_to, user_from FROM messages WHERE user_to='$userLoggedIn' OR user_from='userLoggedIn' ORDER BY id DESC");

		while($row = mysqli_fetch_array($query)) {
			// We gonna add the name of the person's name to this array $convos
			$user_to_push = ($row['user_to'] != $userLoggedIn) ? $row['user_to'] : $row['user_from']; // we push the other one user

			// We check that the username is not in the array 
			if(!in_array($user_to_push, $convos)) {
				array_push($convos, $user_to_push); // add the usernames into the array
				// we have all the conversations that user had
			}
		}

		$num_iterations = 0; // Number of messages checked
		$count = 1; // Number of messages posted

		foreach ($convos as $username) {
			
			// If hasn't reached the start point yet than continue
			// num_iterations++ - first uses this value than increace it
			if($num_iterations++ < $start)
				continue;

			// If we've reached the limit
			if($count > $limit)
				break;
			else
				$count++;

			$is_unread_query = mysqli_query($this->con, "SELECT opened FROM messages WHERE user_to='userLoggedIn' AND user_from='username' ORDER BY id DESC");
			$row = mysqli_fetch_array($is_unread_query); // we wonna get 1 result 

			// All the unread messages will be highlighted to this color
			// If has been read don't do anything
			$style = ($row['opened'] == 'no') ? "background-color: #DDEFF;": "";


			$user_found_obj = new User($this->con, $username); // the user object with that object
			// get the latest messeges between these two users

			$latest_message_details = $this->getLatestMessage($userLoggedIn, $username);
			// $this->getLatestMessage() - allows to call a different function within this class

			// Adding dots
			// If the length of the body id >= 12 characters than put ...
			$dots = (strlen($latest_message_details[1]) >= 12) ? "..." : "";
			$split = str_split($latest_message_details[1], 12); // split the amount of characters that you give it
			$split = $split[0] . $dots;  // The first 12 characters and than...

			$return_string .= "<a href='messages.php?u=$username'> 
			<div class='user_found_messages' style='" . $style ."'>
								<img src='" . $user_found_obj->getProfilePic() . "' style='border-radius: 5px; margin-right: 5px;'>
								" . $user_found_obj->getFirstAndLastName() . "
								<span class='timestamp_smaller' id='grey'> " . $latest_message_details[2] . "</span> 
								<p id='grey' style='margin: 0;'>" . $latest_message_details[0] . $split . " </p>
								</div>
								</a>";

		}

		// If posts were loaded
		if($count > $limit) // If we reached the limit
			// .= return the value and add to it
			// this is the return string that it tells that we need to return more strings or not 
			$return_string .= "<input type='hidden' class='nextPageDropDownData' value='"  . ($page + 1) ."'> <input type='hidden' class='noMoreDropDownData' value='false'>";
		else // if there are posts
			$return_string .= "<input type='hidden' class='noMoreDropDownData' value='true'><p style='text-align: center;'>No more messages to load!</p>";

		return $return_string;
	}

}

?>

