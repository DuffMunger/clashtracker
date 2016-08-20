<?
require('init.php');
require('session.php');

$warId = $_GET['warId'];
try{
	$war = new war($warId);
	$warId = $war->get('id');
}catch(Exception $e){
	$_SESSION['curError'] = 'No war with id ' . $warId . ' found.';
	header('Location: /wars.php');
	exit;
}

$clanId = $_GET['clanId'];
if($war->isClanInWar($clanId)){
	$clan1 = new clan($clanId);
	$clanId = $clan1->get('id');
	$clanIdText = '&clanId=' . $clanId;
}else{
	$clanId = null;
	$clanIdText = '';
}

if(!userHasAccessToUpdateWar($war)){
	$_SESSION['curError'] = NO_ACCESS;
	header('Location: /war.php?warId=' . $warId . $clanIdText);
	exit;
}

try{
	if($war->isPreparationDay()){
		$warPlayers = $war->getPlayers();
		$size = $war->get('size');
		if(count($warPlayers) == $size * 2){
			$war->set('status', War::BATTLE);
			$_SESSION['curMessage'] = 'Successfully transitioned war to Battle Day.';
		}else{
			$_SESSION['curError'] = 'Cannot transition to Battle Day until all players are added to war.';
			header('Location: /war.php?warId=' . $warId . $clanIdText);
			exit;
		}
	}elseif($war->isBattleDay()){
		$war->set('status', War::COMPLETE);
		$_SESSION['curMessage'] = 'Successfully completed war.';
	}
}catch(Exception $e){
	$_SESSION['curError'] = $e->getMessage();
}
header('Location: /war.php?warId=' . $warId . $clanIdText);
exit;
