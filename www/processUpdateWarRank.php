<?
require('init.php');
require('session.php');

$warId = $_POST['warId'];
try{
	$war = new War($warId);
	$warId = $war->get('id');
}catch(Exception $e){
	$error = 'No war with id ' . $warId . ' found.';
	echo json_encode(array('error' => $error));
	exit;
}

$clanId = $_POST['clanId'];
if($war->isClanInWar($clanId)){
	$clan1 = new Clan($clanId);
	$clanId = $clan1->get('id');
	$clan2 = $war->getEnemy($clanId);
	$clanIdText = '&clanId=' . $clan1->get('id');
}else{
	$clanId = null;
	$clanIdText = '';
	$clan1 = $war->get('clan1');
	$clan2 = $war->get('clan2');
}

$userCanEdit = $war->isEditable() && userHasAccessToUpdateWar($war);
if(!$userCanEdit){
	$error = NO_ACCESS;
	echo json_encode(array('error' => $error));
	exit;
}

$playerId = $_POST['playerId'];
if($war->isPlayerInWar($playerId)){
	$player = new Player($playerId);
	$playerId = $player->get('id');
}else{
	$error = 'Player not in war.';
	echo json_encode(array('error' => $error));
	exit;
}

$clan = $war->getPlayerWarClan($player->get('id'));
$warRank = $war->getPlayerRank($player->get('id'));

$action = $_POST['action'];
if($action=='up' && $warRank>1){
	$newWarRank = $warRank-1;
}elseif($action=='down' && $warRank<count($war->getPlayers($clan))){
	$newWarRank = $warRank+1;
}else{
	$error = 'Invalid action.';
	echo json_encode(array('error' => $error));
	exit;
}
$otherPlayer = $war->getPlayerByRank($newWarRank, $clan->get('id'));
$war->updatePlayerRank($otherPlayer->get('id'), $warRank);
$war->updatePlayerRank($player->get('id'), $newWarRank);

$clanRank = $player->get('warRank', $clan->get('id'));
$newClanRank = $otherPlayer->get('warRank', $clan->get('id'));

$clan->updatePlayerWarRank($player->get('id'), $newClanRank);
$clan->updatePlayerWarRank($otherPlayer->get('id'), $clanRank);

echo json_encode(array(
	'message' => 'War rank successfully changed.', 
	'player1' => array(
		'id' => $player->get('id'),
		'rank' => "$newWarRank.&nbsp;" . displayName($player->get('name')),
		'hideUp' => $newWarRank<=1,
		'hideDown' => $newWarRank>=count($war->getPlayers($clan))
	),
	'player2' => array(
		'id' => $otherPlayer->get('id'),
		'rank' => "$warRank.&nbsp;" . displayName($otherPlayer->get('name')),
		'hideUp' => $warRank<=1,
		'hideDown' => $warRank>=count($war->getPlayers($clan))
	)
));
exit;