<?
require('init.php');
require('session.php');
if($_POST['cancel']){
	unsetAll();
	header('Location: /clans.php');
	exit;
}

function unsetAll(){
	unset($_SESSION['clanTag']);
}

$clanTag = $_POST['clanTag'];

$_SESSION['clanTag'] = $clanTag;

if(strlen($clanTag) == 0){
	$_SESSION['curError'] = 'Clan Tag cannot be blank.';
	header('Location: /clans.php');
	exit;
}

try{
	$clan = new clan($clanTag);
	$_SESSION['curMessage'] = 'Clan already created with clan tag: ' . correctTag($clanTag);
	header('Location: /clan.php?clanId=' . $clan->get('id'));
	exit;
}catch(Exception $e){
	$clan = new Clan();
	$clan->create($clanTag);
}
unsetAll();
if(refreshClanInfo($clan, true) === false){
	$clan->delete();
	$_SESSION['curError'] = 'Clan Tag was not found in Clash of Clans.';
	header('Location: /clans.php');
	exit;
}

$_SESSION['curMessage'] = 'Clan created successfully.';
header('Location: /clan.php?clanId=' . $clan->get('id'));
exit;