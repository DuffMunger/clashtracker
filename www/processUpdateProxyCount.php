<?
require('init.php');
require('session.php');

if(!isset($loggedInUser) || $loggedInUser->get('email') != 'alexinmann@gmail.com'){
	$_SESSION['curError'] = NO_ACCESS;
	header('Location: /home.php');
	exit;
}

$api = new api();
foreach ($_POST as $env => $count) {
	try{
		$api->updateProxyCount($env, $count);
	}catch(Exception $e){
		$_SESSION['curError'] = $e->getMessage();
	}
}

if(!isset($_SESSION['curError'])){
	$_SESSION['curMessage'] = 'Successfully update proxy request count.';
}
header('Location: /dev.php');
exit;