<?php
	session_start();

	/*
		At the first time of visiting the website index you have two opsions to choose, User or Admin button.
		User button will direct you to User Panel and the Admin button will direct you to Admin Panel where
		you can add phrase data and other details about phrases in database.
		But to access the Admin Panel you should submit the admin credencials Username and Password.

		At the beggining the credencial_warn and the open_mod has the values 0.
		Credencial_warn gain the value 1 if the username/password combination is incorrect.
		The credencial_warn value is part of javascript and if the value is 1 it will display again the login
		panel but with a warning message.

		The open_mod will be one if the session is opened when the username/password combination is correct.
		The user is redirected to Admin Panel page and there the open_mod gain the valiu 1.
		Open_mod value is part of javascript and if the value is 1 it will not display the login panel if the 
		Admin button is click but it will redirect the user immediately to Admin Panel

	*/
	$credencial_warn = 0;
	$open_mod = 0;

	if(isset($_SESSION['open'])){$open_mod = 1;}		
	if(isset($_POST['username']) && isset($_POST['password'])){

			$username = $_POST['username'];
			$password = $_POST['password'];

			if($username == "lado" && $password == "eraldo"){

				$_SESSION['username'] = $username;
				header('Location: admin.php');
			}
			else{
				$credencial_warn = 1;
			}
	}
	
?>
<!DOCTYPE html>
<html>
<head>
	<title>Eraldo</title>
	<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Calligraffitti" rel="stylesheet">
	<style type="text/css">
		* {
			margin: 0;
			padding: 0;
		}
		html, body {
			height: 100%;
			min-height: 650px;
			background-image: url('img/notebook.png');
			border-image-repeat: repeat;
		}
		.wrap {
			width: 930px;
			height: 100%;
			background-color: #0080ff;
			/* For browsers that do not support gradients */
		    background: -webkit-linear-gradient(#07233f, #5598db); /* For Safari 5.1 to 6.0 */
		    background: -o-linear-gradient(#07233f, #5598db); /* For Opera 11.1 to 12.0 */
		    background: -moz-linear-gradient(#07233f, #5598db); /* For Firefox 3.6 to 15 */
		    background: linear-gradient(#07233f, #5598db); /* Standard syntax (must be last) */
			margin: 0px auto;
			padding-top: 20px;
			box-sizing: border-box;
		}
		.wrapper {
			width: 720px;
			height: 420px;
			margin: 0px auto;
			border-radius: 7px;
			background-color: #fff; 
			background-image: url('img/what-the-hex-dark.png');
			border-image-repeat: repeat;
		}
		.wrapper h1, .wrap h2 {
			font-family: 'Roboto', sans-serif;
			text-align: center;
			color: #efefef;
		}
		.wrapper h1 {
			padding-top: 20px;
			text-shadow: 2px 2px 15px #fff;
		}
		.wrap h2 {
			padding: 4px 0;		
			font-family: 'Calligraffitti', cursive;
			letter-spacing: 2px;	
		}
		.wrapper img {
			width: 70%;
			border: none;
		    display: block;
		    margin: 30px auto;
		}
		.wrapper .panel {
			width: 100%;
		}
		.wrapper .panel .user, .wrapper .panel .admin {
			width: 50%;
			float: left;	
		}
		#butt_user{
			float: right;
			margin-right: 50px;
		}
		#butt_admin{
			float: left;
			margin-left: 50px;
		}
		.panel input[type=submit] {
			width: 150px;
			height: 35px;
			font-family: 'Roboto', sans-serif;
			font-size: 18px;
			font-weight: bold;
			cursor: pointer;
			box-shadow: rgb(84, 163, 247) 0px 1px 0px 0px inset;
    		background: linear-gradient(rgb(0, 125, 193) 5%, rgb(0, 97, 167) 100%) rgb(0, 125, 193);
		    border-radius: 3px;
		    border: 1px solid rgb(18, 77, 119);
		    display: inline-block;
		    color: rgb(255, 255, 255);
		    padding: 6px 24px;
		    text-decoration: none;
		    text-shadow: rgb(21, 70, 130) 0px 1px 0px;
		}
		.wrap .quot {
			width: 100%;
			background-color: #1e1e1e;
			margin-top: 110px; 
			border-radius: 7px;
		}

		/* The Modal (background) */
		.modal {
		    display: none; /* Hidden by default */
		    position: fixed; /* Stay in place */
		    z-index: 1; /* Sit on top */
		    padding-top: 50px; /* Location of the box */
		    left: 0;
		    top: 0;
		    width: 100%; /* Full width */
		    height: 100%; /* Full height */
		    overflow: auto; /* Enable scroll if needed */
		    background-color: rgb(0,0,0); /* Fallback color */
		    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
		}
		/* Modal Content */
		.modal-content {
		    background-color: #ccc;
		    margin: auto;
		    padding: 25px 20px;
		    border: 1px solid #888;
		    width: 400px;
		}
		/* The Close Button */
		.close {
		    color: #aaaaaa;
		    float: right;
		    font-size: 28px;
		    font-weight: bold;
		}
		.close:hover,
		.close:focus {
		    color: #000;
		    text-decoration: none;
		    cursor: pointer;
		}
		.modal-content table {
			width: 100%;
			font-family: 'Roboto', sans-serif; 
		}
		.modal-content th {
			font-size: 26px;
			text-decoration: underline;
			padding-bottom: 20px;
			padding-right: 30px;
		}
		.modal-content td {
			font-weight: bold;
			padding-bottom: 10px;
		}
		.modal-content input {
			float: right;
			margin-right: 100px;
		}
		.modal-content input[type=submit] {
			padding: 2px 5px;
			cursor: pointer;
		}
		.warning {
			color: #A90000;
			text-shadow: 2px 2px 2px #C90000;
			font-family: 'Roboto' , sans-serif;
			font-size: 11px;
		}
	</style>
</head>
<body>
	<div class="wrap">
		<div class="wrapper">
			<h1>Eraldo Web</h1>
			<img src="img/sfond.png">
			
			<div class="panel">
				<div class="user">
					<form action="user.php" method="post">
						<input type="submit" id="butt_user" value="User">
					</form>
				</div>
				<div class="admin">
					<input type="submit" id="butt_admin" value="Admin" onclick="displayDivModal()">
				</div>
			</div>
			
		</div>
		<div class="quot">
				<h2>"Ngjyra e lapsit te nxenesit eshte me e shenjte se gjaku i luftetarit."</h2>
		</div>
		<div id="myModal" class="modal">
		<form method="post" action="index.php">
			  <!-- Modal content -->
			  <div class="modal-content">
			    <span class="close">&times;</span>
			    <table id="login_tab">
			    	<tr><th colspan=2>Login</th></tr>
			    	<tr>
			    		<td>Username: </td><td><input id="usr_id" type="text" name="username"></td>
			    	</tr>
			    	<tr>
			    		<td>Password: </td><td><input id="pass_id" type="password" name="password"></td>
			    	</tr>
			    	<tr>
			    		<td><input type="submit" value="Submit"></td><td><span class="warning">Username ose Password i gabuar!</span></td>
			    	</tr>
			    </table>
			  </div>
			</form>
		</div>
	</div>
	<script type="text/javascript">

		var modal = document.getElementById('myModal');
		var user_input = document.getElementById('usr_id');
		var pass_input = document.getElementById('pass_id');

		// Get the <span> element that closes the login modal
		var span = document.getElementsByClassName("close")[0];
		var span_warn = document.getElementsByClassName("warning")[0];

		// This part will set the two essential variables for login panel interaction 
		<?php echo "var warning = $credencial_warn;\n\t\tvar open = $open_mod;\n";?>

		span_warn.style.display = "none";

		// When the user clicks the button and the open_mod value is 0, open the login modal
		// Else it will redirect the user to Admin Panel 
		function displayDivModal() {
			if(open == 0)
		    	modal.style.display = "block";
		    else
		    	window.location = "admin.php";
		}

		// When the user clicks on <span> (x), close the login modal
		span.onclick = function() {
		    modal.style.display = "none";
		}

		// The function that change the warning message style when username/password combination is incorrect
		function applyWarningStyle() {
			span_warn.style.display = "block";
			user_input.style.borderColor = "#A90000";
			pass_input.style.borderColor = "#A90000";
		}
		
		// If warningn value is 1 wich mean that the admin credencial aren't correct submit again the login panel with warning message
		if(warning == 1){
			displayDivModal();
			applyWarningStyle();
		}

	</script>
</body>
</html>
