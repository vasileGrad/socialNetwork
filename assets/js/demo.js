$(document).ready(function() {

	// Button for profile post
	$('#submit_profile_post').click(function() {

		$.ajax({
			type: "POST",
			url: "includes/handlers/ajax_submit_profile_post.php",
			data: $('form.profile_post').serialize(),
			success: function(msg) {
				$("#post_form").modal('hide');
				location.reload();
			},
			error: function() {
				alert('Failure');
			}
		});

	});

});

function getUsers(value, user) {
	$.post("includes/handlers/ajax_friend_search.php", {query:value, userLoggedIn:user}, function(data) {
		$(".results").html(data);
		// .results - is on messages.php  // echo "<div class='results'></div>";
	});
}

function getDropDownData(user, type) {
	// checking the css if the height value is 0

	if($(".dropdown_data_window").css("height") == "0px") {

		var pageName;

		if(type == 'notification') {

		}else if (type == 'message') {
			pageName = "ajax_load_messages.php"; // this is the page that we are sending to
			$("span").remove("#unread_message");
		}

		// creat the ajax request to retrieve the messages
		var ajaxreq = $.ajax({
			url: "includes/handlers/" + pagName,
			type: "POST",
			data: "page=1&userLoggedIn=" + user,
			cache: false,

			success: function(response) {
				$(".dropdown_data_window").html(response);
				$(".dropdown_data_window").css({"padding" : "0px", "height" : "280px", "border" : "1px solid #DADADA"});
				$("#dropdown_data_type").val(type);
			}

		});
	}else { // If it is open, the height is not 0
		$(".dropdown_data_window").html("");
		$(".dropdown_data_window").css({"padding" : "0px", "height" : "0px", "border" : "none"});		
	}
}