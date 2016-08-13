<?
require('init.php');
require('session.php');

function unsetAll(){
	unset($_SESSION['enemyClanTag']);
	unset($_SESSION['size']);
	unset($_SESSION['clanId']);
}

$enemyClanTag = $_POST['enemyClanTag'];
$size = $_POST['size'];
$clanId = $_POST['clanId'];

$_SESSION['enemyClanTag'] = $enemyClanTag;
$_SESSION['size'] = $size;
$_SESSION['clanId'] = $clanId;

try{
	$clan = new clan($clanId);
	$clanId = $clan->get('id');
	if(!userHasAccessToUpdateClan($clan)){
		$_SESSION['curError'] = NO_ACCESS;
		header('Location: /clan.php?clanId=' . $clanId);
		exit;
	}
}catch(Exception $e){
	$_SESSION['curError'] = 'No clan with id ' . $clanId . ' found.';
	unsetAll();
	header('Location: /clans.php');
	exit;
}

if($_POST['cancel']){
	unsetAll();
	header('Location: /clan.php?clanId=' . $clan->get('id'));
	exit;
}

if(strlen($enemyClanTag) == 0){
	$_SESSION['curError'] = 'Enemy Clan Tag cannot be blank.';
	header('Location: /addWar.php?clanId=' . $clan->get('id'));
	exit;
}

try{
	$enemyClan = new clan($enemyClanTag);
}catch(Exception $e){
	$enemyClan = new Clan();
	$enemyClan->create($enemyClanTag);
	if(refreshClanInfo($enemyClan, true) === false){
		$enemyClan->delete();
		$_SESSION['curError'] = 'Enemy Clan Tag was not found in Clash of Clans.';
		header('Location: /addWar.php?clanId=' . $clan->get('id'));
		exit;
	}
}

if($enemyClan->get('id') == $clan->get('id')){
	$_SESSION['curError'] = 'Clan cannot war against itself.';
	header('Location: /addWar.php?clanId=' . $clan->get('id'));
	exit;
}

$war = new War();
unsetAll();
try{
	$war->create($clan, $enemyClan, $size);
	$_SESSION['curMessage'] = 'Clan War created successfully.';
	header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clan->get('id'));
}catch(Exception $e){
	error_log($e->getMessage());
	try{
		$war->delete();
		$_SESSION['curError'] = 'There was an error trying to create the war. Please try again.';
	}catch(Exception $e){
		error_log($e->getMessage());
		$_SESSION['curError'] = 'There was an unexpected error.';
	}
	header('Location: /clan.php?clanId=' . $clan->get('id'));
}
exit;