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
	$clan2 = $war->getEnemy($clanId);
	$clanIdText = '&clanId=' . $clan1->get('id');
}else{
	$clanId = null;
	$clanIdText = '';
	$clan1 = $war->get('clan1');
	$clan2 = $war->get('clan2');
}

if(!$war->isBattleDay()){
	$_SESSION['curError'] = 'Removing war attacks is only available during battle day.';
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
		exit;
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
		exit;
	}
}

if(!userHasAccessToUpdateWar($war)){
	$_SESSION['curError'] = NO_ACCESS;
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
		exit;
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
		exit;
	}
}

$attackerId = $_GET['attackerId'];
if($war->isPlayerInWar($attackerId)){
	$attacker = new player($attackerId);
	$attackerId = $attacker->get('id');
}else{
	$_SESSION['curError'] = 'Attacker not in war.';
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
	}
	exit;
}

$defenderId = $_GET['defenderId'];
if($war->isPlayerInWar($defenderId)){
	$defender = new player($defenderId);
	$defenderId = $defender->get('id');
}else{
	$_SESSION['curError'] = 'Defender not in war.';
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
	}
	exit;
}

$attack = $war->getAttack($attackerId, $defenderId);
if(!isset($attack)){
	$_SESSION['curError'] = htmlspecialchars($attacker->get('name')) . ' never attacked ' . htmlspecialchars($defender->get('name')) . ' in this war.';
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
	}
	exit;
}

$war->removeAttack($attacker, $defender);
$_SESSION['curMessage'] = 'War attack successfully removed.';
if(isset($clanId)){
	header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
}else{
	header('Location: /war.php?warId=' . $war->get('id'));
}
exit;