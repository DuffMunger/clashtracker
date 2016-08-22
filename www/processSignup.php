<?
require('init.php');
require('session.php');
if(isset($loggedInUser)){
	$_SESSION['curError'] = NO_ACCESS;
	header('Location: /home.php');
	exit;
}

function unsetAll(){
	unset($_SESSION['email']);
}

if($_POST['cancel']){
	header('Location: /login.php');
	exit;
}

$email = $_POST['email'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

$_SESSION['email'] = $email;

if($password != $confirmPassword){
	$_SESSION['curError'] = 'Passwords do not match.';
	header('Location: /signup.php');
	exit;
}

try{
	try{
		$user = new User($email);
		$_SESSION['curError'] = 'Account already exists with email: ' . $email;
		header('Location: /signup.php');
		exit;
	}catch(Exception $e){
		$user = new User();
		$user->create($email, $password);
	}
}catch(Exception $e){
	$_SESSION['curError'] = $e->getMessage();
	header('Location: /signup.php');
	exit;
}

$_SESSION['user_id'] = $user->get('id');
if(!DEVELOPMENT){
	email(User::getAdmin()->get('email'), 'New Clash Tracker User!', 'There is a new user using Clash Tracker! Their email is ' . $email . ". Welcome them to the site!\n\nClash on!", $senderSignup);
}
unsetAll();
header('Location: /home.php');
exit;
