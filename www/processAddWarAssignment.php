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
	$clanIdText = "&clanId=$clanId";
}else{
	$clanId = null;
	$clan1 = $war->get('clan1');
	$clan2 = $war->get('clan2');
	$clanIdText = '';
}

if($_POST['cancel']){
	header('Location: /war.php?warId=' . $war->get('id') . $clanIdText);
	exit;
}

if(!$war->isPreparationDay()){
	$_SESSION['curError'] = 'Adding war assignments is only available during preparation day.';
	header('Location: /war.php?warId=' . $war->get('id') . $clanIdText);
	exit;
}

if(!userHasAccessToUpdateWar($war)){
	$_SESSION['curError'] = NO_ACCESS;
	header('Location: /war.php?warId=' . $war->get('id') . $clanIdText);
	exit;
}

$attackerId = $_POST['playerId'];
try{
	$attacker = new player($attackerId);
	$attackerId = $attacker->get('id');
}catch(Exception $e){
	$_SESSION['curError'] = 'No attacker with id ' . $playerId . ' found.';
	header('Location: /war.php?warId=' . $war->get('id') . $clanIdText);
	exit;
}

if(!$war->isPlayerInWar($attacker->get('id'))){
	$_SESSION['curError'] = htmlspecialchars($attacker->get('name')) . ' not in war.';
	header('Location: /war.php?warId=' . $war->get('id') . $clanIdText);
	exit;
}

$defenderId = $_POST['defenderId'];
try{
	$defender = new player($defenderId);
	$defenderId = $defender->get('id');
}catch(Exception $e){
	$_SESSION['curError'] = 'No defender selected.';
	header('Location: /addWarAttack.php?warId=' . $war->get('id') . '&playerId=' . $attacker->get('id') . $clanIdText);
	exit;
}

if(!$war->isPlayerInWar($defender->get('id'))){
	$_SESSION['curError'] = htmlspecialchars($defender->get('name')) . ' not in war.';
	header('Location: /addWarAttack.php?warId=' . $war->get('id') . '&playerId=' . $attacker->get('id') . $clanIdText);
	exit;
}

$attackerClan = $war->getPlayerWarClan($attacker->get('id'));
$defenderClan = $war->getPlayerWarClan($defender->get('id'));

if($attackerClan->get('id') == $defenderClan->get('id')){
	$_SESSION['curError'] = 'Attacker and defender cannot be from the same clan.';
	header('Location: /addWarAttack.php?warId=' . $war->get('id') . '&playerId=' . $attacker->get('id') . $clanIdText);
	exit;
}

$attackerAssignments = $war->getAssignments($attacker->get('id'));
if(count($attackerAssignments) >= 2){
	$_SESSION['curError'] = htmlspecialchars($attacker->get('name')) . ' has already been assigned 2 attakcs.';
	header('Location: /war.php?warId=' . $war->get('id') . $clanIdText);
	exit;
}

if(isset($attackerAssignments[0]) && $attackerAssignments[0]->get('assignedPlayerId') == $defender->get('id')){
	$_SESSION['curError'] = htmlspecialchars($attacker->get('name')) . ' has already been assigned to attack ' . htmlspecialchars($defender->get('name')) . '.';
	header('Location: /war.php?warId=' . $war->get('id') . $clanIdText);
	exit;
}

try{
	$war->addAssignment($attacker, $defender);
	$_SESSION['curMessage'] = 'New assignment added successfully.';
}catch(Exception $e){
	$_SESSION['curError'] = $e->getMessage();
}
header('Location: /war.php?warId=' . $war->get('id') . $clanIdText);
exit;