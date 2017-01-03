<?
require('init.php');
require('session.php');

$clanId = $_GET['clanId'];
try{
	$clan = new clan($clanId);
	$clanId = $clan->get('id');
}catch(Exception $e){
	$_SESSION['curError'] = 'No clan with id ' . $clanId . ' found.';
	header('Location: /clans.php');
	exit;
}

if(!userHasAccessToUpdateClan($clan)){
	$_SESSION['curError'] = NO_ACCESS;
    header('Location: /clan.php?clanId=' . $clanId);
    exit;
}

try{
	$lootReport = $clan->generateLootReport();
	$_SESSION['curMessage'] = 'Loot Report Successfully Generated.';
	header('Location: /lootReport.php?lootReportId=' . $lootReport->get('id'));
}catch(Exception $e){
	$_SESSION['curError'] = $e->getMessage();
	header('Location: /clan.php?clanId=' . $clanId);
}
exit;