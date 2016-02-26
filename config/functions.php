<?
require('config.php');

function rankFromCode($code){
	$ranks = array('LE' => 'Leader',
		'CO' => 'Co-leader',
		'EL' => 'Elder',
		'ME' => 'Member',
		'KI' => 'Kicked',
		'EX' => 'Left',
		null => '');
	return $ranks[$code];
}

function clanTypeFromCode($code){
	$clanType = array('AN' => 'Anyone can join',
		'IN' => 'Invite Only',
		'CL' => 'Closed');
	return $clanType[$code];
}

function lootTypeFromCode($code){
	$clanType = array('GO' => 'Gold',
		'EL' => 'Elixir',
		'DE' => 'Dark Elixir');
	return $clanType[$code];
}

function warFrequencyFromCode($code){
	$warFrequency = array('NS' => 'Not Set',
		'AL' => 'Always',
		'NE' => 'Never',
		'TW' => 'Twice a week',
		'OW' => 'Once a week',
		'RA' => 'Rarely');
	return $warFrequency[$code];
}

function buildProcedure(){
	if(func_num_args() > 0){
		global $db;
		$parameters = func_get_args();
		$procedureName = array_shift($parameters);
		$procedure = 'CALL ' . $procedureName . '(';
		foreach ($parameters as $parameter) {
			if(isset($parameter)){
				$procedure .= "'" . $db->escape_string($parameter) . "',";
			}else{
				$procedure .= "NULL,";
			}
		}
		$procedure = rtrim($procedure, ",") . ');';
		error_log($procedure);
		return $procedure;
	}else{
		throw new illegalOperationException('buildProcedure first argument must be the procedure name.');
	}
}

function correctTag($tag){
	if($tag[0] != '#'){
		$tag = '#' . $tag;
	}
	$tag = strtoupper($tag);
	$tag = str_replace('O', '0', $tag);
	return $tag;
}

function weekAgo(){
	return strtotime('-1 week');
}

function dayAgo(){
	return strtotime('-1 day');
}

function monthAgo(){
	return strtotime('-1 month');
}

function yearAgo(){
	return strtotime('-1 year');
}

function sortPlayersByRank($players, $order='desc'){
	for ($i=1; $i < count($players); $i++) { 
		$j=$i;
		while ($j>0 && rankIsLower($players[$j-1]->get('rank'), $players[$j]->get('rank'))){
			$temp = $players[$j];
			$players[$j] = $players[$j-1];
			$players[$j-1] = $temp;
			$j--;
		}
	}
	if($order == 'desc'){
		return $players;
	}else{
		return array_reverse($players);
	}
}

function rankIsHigher($rank1, $rank2){
	switch ($rank1) {
		case 'LE':
			return $rank2 != 'LE';
			break;
		case 'CO':
			return $rank2 != 'LE' && $rank2 != 'CO';
			break;
		case 'EL':
			return $rank2 == 'ME';
			break;
		default:
			return false;
			break;
	}
}

function rankIsLower($rank1, $rank2){
	switch ($rank1) {
		case 'CO':
			return $rank2 == 'LE';
			break;
		case 'EL':
			return $rank2 != 'ME' && $rank2 != 'EL';
			break;
		case 'ME':
			return $rank2 != 'ME';
			break;
		default:
			return false;
			break;
	}
}

function validPassword($password){
	return strlen($password)>8;
}

function generateRandomPassword(){
	$lowerCase = 'abcdefghijklmnopqrstuvwxyz';
	$upperCase = strtoupper($lowerCase);
	$nums = '1234567890';
	$types = array($lowerCase, $upperCase, $nums);
	$password = "";
	for ($i=0; $i < 10; $i++) { 
		$type = rand(0,count($types)-1);
		$chars = str_split($types[$type]);
		$char = rand(0,count($chars)-1);
		$char = $chars[$char];
		$password.=$char;
	}
	return $password;
}

function userHasAccessToUpdatePlayer($player){
	global $loggedInUser;
	global $loggedInUserPlayer;
	$accessType = $player->get('accessType');
	if($accessType=='AN'){
		return true;
	}else{
		if(isset($loggedInUser)){
			if(isset($loggedInUserPlayer) && $loggedInUserPlayer->get('id') == $player->get('id')){
				return true;
			}else{
				$allowedUsers = $player->getAllowedUsers();
				foreach ($allowedUsers as $user) {
					if($loggedInUser->get('id') == $user->get('id')){
						return true;
					}
				}
				return false;
			}
		}else{
			return false;
		}
	}
	return false;
}

function userHasAccessToUpdateClan($clan){
	global $loggedInUser;
	global $loggedInUserClan;
	$accessType = $clan->get('accessType');
	if($accessType=='AN'){
		return true;
	}else{
		if(isset($loggedInUser)){
			if(isset($loggedInUserClan) && $loggedInUserClan->get('id') == $clan->get('id')){
				return true;
			}else{
				$allowedUsers = $clan->getAllowedUsers();
				foreach ($allowedUsers as $user) {
					if($loggedInUser->get('id') == $user->get('id')){
						return true;
					}
				}
				return false;
			}
		}else{
			return false;
		}
	}
	return false;
}

function userHasAccessToUpdateWar($war){
	if(!userHasAccessToUpdateClan($war->get('clan1'))){
		global $loggedInUser;
		if(isset($loggedInUser)){
			$allowedUsers = $war->getAllowedUsers();
			foreach ($allowedUsers as $user) {
				if($loggedInUser->get('id') == $user->get('id')){
					return true;
				}
			}
		}
		return false;
	}else{
		return true;
	}
}

function convertType($code){
	$clanType = array(
		'open' => 'AN',
		'inviteOnly' => 'IN',
		'closed' => 'CL');
	return $clanType[$code];
}

function convertFrequency($code){
	$warFrequency = array(
		'unknown' => 'NS',
		'always' => 'AL',
		'never' => 'NE',
		'moreThanOncePerWeek' => 'TW',
		'oncePerWeek' => 'OW',
		'lessThanOncePerWeek' => 'RA');
	return $warFrequency[$code];
}

function convertRank($code){
	$ranks = array(
		'member' => 'ME',
		'admin' => 'EL',
		'coLeader' => 'CO',
		'leader' => 'LE');
	return $ranks[$code];
}

function refreshClanInfo($clan){
	try{
		$api = new clanApi();
		$clanInfo = $api->getClanInformation($clan->get('tag'));
	}catch(Exception $e){
		error_log($e->getMessage());
		return -1;
	}
	$clan->updateFromApi($clanInfo);
	$members = $clan->getMembers();
	foreach ($clanInfo->memberList as $apiMember) {
		$count = 0;
		foreach ($members as $key => $temp) {
			if($apiMember->name == $temp->get('name')){
				$count++;
				$member = $temp;
				unset($members[$key]);
			}
		}
		if($count==1){
			$member->updateFromApi($apiMember);
		}elseif ($count==0) {
			//TODO: The player needs to be added to the clan
			//		Need the player tag though, which is not provided yet
		}
	}
	foreach ($members as $member) {
		$member->leaveClan();
	}
	$clan->getMembers(true);//reload the members after some have left
	return 0;
}