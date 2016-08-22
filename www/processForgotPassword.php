<?
require('init.php');
require('session.php');
if(isset($loggedInUser)){
	$_SESSION['curError'] = NO_ACCESS;
	header('Location: /home.php');
	exit;
}

if($_POST['cancel']){
	header('Location: /login.php');
	exit;
}

$email = $_POST['email'];

$_SESSION['email'] = $email;

try{
	$user = new User($email);
}catch(Exception $e){
	$_SESSION['curError'] = 'User account not found with specified email.';
	header('Location: /forgotPassword.php');
	exit;
}

try{
	$newPassword = generateRandomPassword();
	$user->changePassword($newPassword);
}catch(Exception $e){
	$_SESSION['curError'] = 'There was an error trying to reset your password. Please try again or email ' + User::getAdmin()->get('email') + ' if the problem persists.';
	header('Location: /forgotPassword.php');
	exit;
}
$link = "http://clashtracker.ca/login.php";
$subject = "Forgotten Password";
$message = "Hello,\n\n\tWe have received a request to reset the password on your Clash Tracker account. Your new password is " . $newPassword . ". We recommend changing you password immediately after using this one to sign in. You can click on the below link to sign in now: " . $link . "\nPlease do not reply to this email.\nClash on,\n\nClash&nbsp;Tracker Account Support\n";
if(email($email, $subject, $message, 'password@clashtracker.ca')){
	$_SESSION['curMessage'] = 'Password reset email successfully sent. It may take a few minutes to arrive.';
	header('Location: /login.php');
}else{
	if(!HEROKU || !PRODUCTION){
		$_SESSION['curError'] = 'Your password was reset however sending emails is disabled on the development version of Clash Tracker. Check the Error Logs for your new password.';
		error_log($newPassword);
	}else{
		$_SESSION['curError'] = 'There was an error trying to send you your new password. Please email ' + User::getAdmin()->get('email') + ' for help.';
	}
	header('Location: /forgotPassword.php');
}
exit;