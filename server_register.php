<?php 
session_start();
	// variable declaration
	$username = "";
	$email    = "";
	$errors = array(); 
	$_SESSION['success'] = "";
	// connect to database
	$db = mysqli_connect('localhost', 'root', 'misdan', 'lapan_api');
	//random api key dengan panjang 32
	function randomString($length = 32) {
	$str = "";
	$characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
	$max = count($characters) - 1;
	for ($i = 0; $i < $length; $i++) {
		$rand = mt_rand(0, $max);
		$str .= $characters[$rand];
	}
	return $str;
	}

	// REGISTER USER
	if (isset($_POST['reg_user'])) {
		// receive all input values from the form
		$username = mysqli_real_escape_string($db, $_POST['username']);
		$email = mysqli_real_escape_string($db, $_POST['email']);
		$password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
		$password_2 = mysqli_real_escape_string($db, $_POST['password_2']);

		//mengenerate api key
		$api_key = randomString();

		// form validation: ensure that the form is correctly filled
		if (empty($username)) { array_push($errors, "Username is required"); }
		if (empty($email)) { array_push($errors, "Email is required"); }
		if (empty($password_1)) { array_push($errors, "Password is required"); }
		if ($password_1 != $password_2) {
			array_push($errors, "The two passwords do not match");
		}

		//periksa username, email dan token
		$sql_u = "SELECT * FROM users WHERE username='$username'";
	  	$sql_e = "SELECT * FROM users WHERE email='$email'";
	  	$sql_f = "SELECT * FROM users WHERE api_key='$api_key'";

	  	$res_u = mysqli_query($db, $sql_u);
	  	$res_e = mysqli_query($db, $sql_e);
	  	$res_f = mysqli_query($db, $sql_f);

	  	if (mysqli_num_rows($res_u) > 0) {
	  		array_push($errors, "Username already taken");	
	  	}
	  	else if(mysqli_num_rows($res_e) > 0){
	  	  	array_push($errors, "Email already taken");	
	  	}
	  	else if(mysqli_num_rows($res_f) >0){
	  		array_push($errors, "Sorry something wrong, please try again");	
	  	}
		// register user if there are no errors in the form
		else {
			$password = md5($password_1);//encrypt the password before saving in the database
			$query = "INSERT INTO users (username, email, password,api_key) 
					  VALUES('$username', '$email', '$password','$api_key')";
			mysqli_query($db, $query);

			//sent email
			// Now we are ready to build our welcome email
		    $to = $email;
		    $subject = "Registration API LAPAN";
		    $body = 
		    "Dear " . $username . ",

This is your API Key : " . $api_key ."

This is an automatic email, please do not reply to this email.

Kind Regards,
IT Team LAPAN
";

		    //$headers = array('Content-Type: text/html; charset=UTF-8');
		    $from = "Do-not-reply@lapan.co.id";

		    mail ($to, $subject, $body, null, "-f ".$from." ");

			//memasukan data ke session
			$_SESSION['username'] = $username;
			$_SESSION['api'] = $api_key;
			$_SESSION['success'] = "You are now logged in, we also sent the detail to inbox/spam in your email";
			header('location: index.php');
		}
	}
?>