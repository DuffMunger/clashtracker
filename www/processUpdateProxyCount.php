<?
require('init.php');
require('session.php');

if(!isset($loggedInUser) || !$loggedInUser->isAdmin()){
	$_SESSION['curError'] = NO_ACCESS;
	header('Location: /home.php');
	exit;
}

$api = new API();
foreach ($_POST as $env => $count) {
	try{
		$api->updateProxyCount($env, $count);
	}catch(Exception $e){
		$_SESSION['curError'] = $e->getMessage();
	}
}

if(!isset($_SESSION['curError'])){
	$_SESSION['curMessage'] = 'Successfully updated proxy request count.';
}
header('Location: /dev.php');
exit;