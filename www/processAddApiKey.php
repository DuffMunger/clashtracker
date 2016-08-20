<?
require('init.php');
require('session.php');

if(!isset($loggedInUser) || !$loggedInUser->isAdmin()){
	$_SESSION['curError'] = NO_ACCESS;
	header('Location: /home.php');
	exit;
}

$ips = explode(' ', $_POST['ips']);
$key = $_POST['key'];

foreach ($ips as $ip) {
	if(strlen($ip)>0){
		try{
			$apiKey = new apiKey($ip);
			$apiKey->delete();
		}catch(Exception $e){
			//ignore
		}
		try{
			$apiKey = new APIKey();
			$apiKey->create($ip, $key);
		}catch(Exception $e){
			$_SESSION['curError'] = $e->getMessage();
			break;
		}
	}
}

if(!isset($_SESSION['curError'])){
	$_SESSION['curMessage'] = 'API Keys successfully added.';
}
header('Location: /dev.php');
exit;