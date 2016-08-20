<?
require('init.php');
require('session.php');

$warId = $_POST['warId'];
try{
	$war = new war($warId);
	$warId = $war->get('id');
}catch(Exception $e){
	$_SESSION['curError'] = 'No war with id ' . $warId . ' found.';
	header('Location: /wars.php');
	exit;
}

$clanId = $_POST['clanId'];
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

if(!userHasAccessToUpdateClan($war->get('clan1'))){
	$_SESSION['curError'] = NO_ACCESS;
	header('Location: /war.php?warId=' . $war->get('id') . $clanIdText);
	exit;
}

if(!$war->isBattleDay()){
	$_SESSION['curError'] = 'Editing war attacks is only available during battle day.';
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
		exit;
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
		exit;
	}
}

if($_POST['cancel']){
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
	}
	exit;
}

$attackerId = $_POST['attackerId'];
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

$defenderId = $_POST['defenderId'];
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

$stars = $_POST['stars'];
if($stars != $attack['totalStars']){
	$war->updateAttack($attackerId, $defenderId, $stars);
	$_SESSION['curMessage'] = 'War attack succesfully updated.';
}

if(isset($clanId)){
	header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
}else{
	header('Location: /war.php?warId=' . $war->get('id'));
}
exit;