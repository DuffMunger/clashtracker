<?
require('init.php');
require('session.php');

$email = $_POST['email'];
$subject = $_POST['subject'];
$message = $_POST['message'];

if(strlen($message)==0){
	$_SESSION['curError'] = 'Message cannot be empty.';
	header('Location: /help.php');
	exit;
}

if(strlen($email)>0){
	$message .= "\n\nYou can reply to this question by emailing " . $email;
}
$message .= "\n\nClash on!";

if(strlen($subject)==0){
	$subject = 'New Clash Tracker Question';
}

if(PRODUCTION){
	if(email(User::getAdmin()->get('email'), $subject, $message, 'support@clashtracker.ca')){
		$_SESSION['curMessage'] = 'Question was successfully submitted. I\'ll get back to you as soon as I can.';
	}else{
		$_SESSION['curError'] = 'There was a problem submitting the question. Please try <a href="http://www.twitter.com/clashsolo"><i class="fa fa-twitter" style="color: #3C90E8;"></i>&nbsp;Twitter</a>.';
	}
}else{
	$_SESSION['curError'] = 'Submitting questions is disabled for the development version of Clash Tracker.';
}
header('Location: /help.php');
exit;
