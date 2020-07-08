<?php
   session_start();
   
   require_once "config.php";
   
   // Check if the user is already logged in, if yes then redirect him to welcome page
	if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
		header("location: dashboard.php");
		exit;
	}
 
	$username = $password = "";
	$username_err = $password_err = "";
 
// Processing form data when form is submitted
	if($_SERVER["REQUEST_METHOD"] == "POST"){
 
		// Check if username is empty
		if(empty(trim($_POST["username"]))){
			$username_err = "Please enter username.";
		} else{
			$username = trim($_POST["username"]);
		}
		
		// Check if password is empty
		if(empty(trim($_POST["password"]))){
			$password_err = "Please enter your password.";
		} else{
			$password = sha1(($_POST["password"]));
		}
		
		// Validate credentials
		if(empty($username_err) && empty($password_err)){
			// Prepare a select statement
			$sql = "SELECT id, name, password, endpoint FROM users WHERE name = ?";
			
			if($stmt = mysqli_prepare($link, $sql)){
				// Bind variables to the prepared statement as parameters
				mysqli_stmt_bind_param($stmt, "s", $param_username);
				
				// Set parameters
				$param_username = $username;
				
				// Attempt to execute the prepared statement
				if(mysqli_stmt_execute($stmt)){
					// Store result
					mysqli_stmt_store_result($stmt);
					
					// Check if username exists, if yes then verify password
					if(mysqli_stmt_num_rows($stmt) == 1){                    
						// Bind result variables
						mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $personal_endpoint);
						if(mysqli_stmt_fetch($stmt)){
							if($password == $hashed_password){
								// Password is correct, so start a new session
								session_start();
								// Store data in session variables
								$_SESSION["loggedin"] = true;
								$_SESSION["userId"] = $id;
								$_SESSION["username"] = $username;            
								$_SESSION["endpoint"] = $personal_endpoint;								
								
								// Redirect user to welcome page
								header("location: dashboard.php");
							} else{
								// Display an error message if password is not valid
								$password_err = "The password you entered was not valid.";
							echo "<script>alert('The password you entered was not valid.')</script>";
							}
						}
					} else{
						// Display an error message if username doesn't exist
						$username_err = "No account found with that username.";
						echo "<script>alert('No account found with that username.')</script>";
					}
				} else{
					echo "<script>alert('Oops! Something went wrong. Please try again later.')</script>";
				}

				// Close statement
				mysqli_stmt_close($stmt);
			}
		}
		
		// Close connection
		mysqli_close($link);
}
?>
<link rel="stylesheet" type="text/css" href="login.css">
<div class="noise"></div>
<div class="overlay"></div>
<div class="terminal">
  <h1>HACKING AS A <span class="errorcode">SERVICE</span></h1>
  <p class="output">You are trying to access the team server web distributed interface.</p>
  <p class="output">This is a personal project for educationnal purposes only</p>
  <p class="output">We are BCT.</p>
  
	<div class = "container">
      
		<form class = "form-signin" role = "form" 
            action = "login.php" method = "post">
            <input type = "text" class = "form-control" name = "username" required autofocus></br>
            <input type = "password" class = "form-control" name = "password">
             <p><input type="submit" value="OK"></p>
         </form>
         
	</div> 
	  
</div>