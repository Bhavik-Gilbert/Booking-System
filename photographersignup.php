<?php
session_start();
$message="";
	
if(!empty($_POST)){
	#connects page to database
	include 'Enitity/connect.php';
	#checks if submit has been pressed
	if (isset($_POST['Submit'])) {
		#collects data from form
		$Firstname = $_POST['Firstname'];
		$Surname = $_POST['Surname'];
		$Email = $_POST['Email'];
		$PhoneNumber = $_POST['PhoneNumber'];
		$Username = $_POST['Username'];
		$Password =($_POST['Password']);
		$message = "";
		
		#creates condition for strong password
		$uppercase = preg_match('@[A-Z]@', $Password);
		$lowercase = preg_match('@[a-z]@', $Password);
		$number    = preg_match('@[0-9]@', $Password);
		$specialChars = preg_match('@[^\w]@', $Password);

		#hashes password
		$Format = "$2y$10$";
		$HashLength = 55;
		$Unique = md5(uniqid(mt_rand(),true));
		$Base64  = base64_encode($Unique);
		$Modified = str_replace('+','.',$Base64);
		$Generate = substr($Modified,0,$HashLength);
		$Formatting = $Format.$Generate;
		$Hash = crypt($Password, $Formatting);
		
		#stops data repetition in the database/double signup
		$Check1 = mysqli_query($con, "SELECT * FROM photographer WHERE Firstname='".$Firstname."' and Surname='".$Surname."'") or die(mysqli_error($con));
		$NameCheck  = mysqli_fetch_array($Check1);
		$Check2 = mysqli_query($con, "SELECT * FROM photographer WHERE Email='".$Email."'") or die(mysqli_error($con));
		$MailCheck  = mysqli_fetch_array($Check2);
		$Check3 = mysqli_query($con, "SELECT * FROM login WHERE Username='".$Username."'")
		or die(mysqli_error($con));
		$UserCheck  = mysqli_fetch_array($Check3);
		$Check4 = mysqli_query($con, "SELECT * FROM photographer WHERE PhoneNumber='".$PhoneNumber."'") or die(mysqli_error($con));
		$PhoneCheck  = mysqli_fetch_array($Check4);

		#validates all values in the form
		if (empty($Firstname) || empty($Surname) || (empty($Email) &&  empty($PhoneNumber)) || empty($Username) || empty($Password)){
			$message .= "Please fill in all of the fields <br>";
		}
		if($_POST['Password'] !== $_POST['Password1'])
			{$message .= "Your passwords do not match <br>";
			}
		if (is_numeric($Firstname)){
			$message .= "Invalid Value for Firstname Field <br>";
		}
		if (is_numeric($Surname)){
			$message .= "Invalid Value for Surname Field <br>";
		}
		if(is_array($NameCheck)){
			$message .= "This name is already registered in the system <br>";
		}
		if (!filter_var($Email, FILTER_VALIDATE_EMAIL)){
			$message .= "Invalid Value for Email Field <br>";
		}
		if(is_array($MailCheck)){
			$message .= "This email is already registered in the system <br>";
		}
		if ((!is_numeric($PhoneNumber)) || (strlen($PhoneNumber)<11)){
			$message .= "Invalid Value for PhoneNumber Field. It should be a UK number in 07 or 02 form <br>";
		}
		if(is_array($PhoneCheck)){
			$message .= "This phone number is already registered in the system <br>";
		}
		if(is_array($Check4)){
			$message .= "This phone number is already registered in the system <br>";
		}
		if(is_array($UserCheck)){
			$message .= "This username is taken <br>";
		}
		if (strlen($_POST['Password'])<8){
			$message .= "Password is too short, it must be at least 8 characters long <br>";
		}
		if(!$uppercase || !$lowercase || !$number || !$specialChars){
			$message .= "Password must include 1 upper case, 1 lower case, 1 number and 1 special character <br>";
		}
		if(empty($message)){
			#adds data to login table in database
			$sql = mysqli_query($con, "INSERT INTO login (Username, Password, Type) 
			VALUES ('$Username' , '$Hash', 'Photographer')") or die (mysqli_error($con));
			
			#gets LoginID from new record to add to photographer table later
			$select = mysqli_query($con, "SELECT * FROM login WHERE Username = '".$Username."'")
			or die(mysqli_error($con));
			$selection = mysqli_fetch_array($select);
			$LoginID = $selection['LoginID'];

			#adds data to photographer table in database
			$sql = mysqli_query($con, "INSERT INTO photographer (Firstname, Surname , Email , PhoneNumber, LoginID) 
			VALUES ('$Firstname', '$Surname' , '$Email' , '$PhoneNumber' , '$LoginID')") or die (mysqli_error($con));
			$message = "New record created successfully. Please log in to access the rest of the site";
		}
	}
}
?>

<html>
<head>
<link rel="stylesheet" type = "text/css" href="CSS/Style.css">
<link rel="stylesheet" type = "text/css" href="CSS/table.css">
<title>Signup Page</title>
</head>
<body>
<?php
include 'Enitity/menu.php';
#redirects consumers to the account page
if($_SESSION["Type"]=="Consumer") {
	header("Location:Account.php");
}
#redirects users who are not logged in to the referal page
if(!isset($_SESSION["Username"])) {
	header("Location:Refer.php");
}
#creates form for users to fill in their details with
?>
<h1 style="text-align:center">Signup</h1>
</body>
<form name="frmsign" method="post" action="" align="center">
<?php #presents error message in the occurrence of an error ?>
<div class="message"><?php if($message!="") { echo $message; } ?></div>

<div class="input-group">
<label>Firstname</label>
<input type="text" name="Firstname">
</div>
 
<div class="input-group">
<label>Surname</label>
<input type="text" name="Surname">
</div>

<div class="input-group">
<label>Email</label>
<input type="text" name="Email">
</div>

<div class="input-group">
<label>Phone Number</label>
<input type="text" name="PhoneNumber">
</div>


<div class="input-group">
<label>Username</label>
<input type="text" name="Username">
</div>

<div class="input-group">
<label>Password</label>
<input type="password" name="Password">
</div>

<div class="input-group">
<label>Clarify Password</label>
<input type="password" name="Password1">
</div>

<div class="input-group">
<button class="btn" type="submit" name="Submit" style="display: block; margin-left: auto;
    margin-right: auto; width: 5em">Submit</button>
</div>
</form>
<br><br><br>
</html>
</DOCTYPE>
