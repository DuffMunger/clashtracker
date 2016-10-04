<?
require('config.php');
require('../vendor/autoload.php');

function rankFromCode($code){
	$ranks = array('1' => 'Leader',
		'2' => 'Co-leader',
		'3' => 'Elder',
		'4' => 'Member',
		'5' => 'Left',
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
				if(is_array($parameter)){
					$procedure .= "\"(";
					foreach ($parameter as $param){
						$procedure .= "'" . $db->escape_string($param) . "',";
					}
					$procedure = rtrim($procedure, ",");
					$procedure .= ")\"";
				}else{
					$procedure .= "'" . $db->escape_string($parameter) . "',";
				}
			}else{
				$procedure .= "NULL,";
			}
		}
		$procedure = rtrim($procedure, ",") . ');';
		error_log($procedure);
		return $procedure;
	}else{
		throw new OperationException('buildProcedure first argument must be the procedure name.');
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
	return strtotime('-180 hours');
}

function dayAgo(){
	return strtotime('-1 day');
}

function hourAgo(){
	return strtotime('-1 hour');
}

function monthAgo(){
	return strtotime('-1 month');
}

function yearAgo(){
	return strtotime('-1 year');
}

function sortPlayersByWarScore($players){
	$players = array_values($players);
	for ($i=1; $i < count($players); $i++) { 
		$j=$i;
		while ($j>0 && $players[$j-1]->getScore() < $players[$j]->getScore()){
			$temp = $players[$j];
			$players[$j] = $players[$j-1];
			$players[$j-1] = $temp;
			$j--;
		}
	}
	foreach ($players as $key => $player) {
		if($player->get('numberOfWars') < 2){
			unset($players[$key]);
		}
	}
	return $players;
}

function validPassword($password){
	if(strlen($password)<8){
		throw new PasswordException('Password is too short. Passwords must be at least 8 characters long.');
	}
	if(preg_match('/[a-z]/', $password) != 1){
		throw new PasswordException('Password must contain at least one lower case character.');
	}
	if(preg_match('/[A-Z]/', $password) != 1){
		throw new PasswordException('Password must contain at least one upper case character.');
	}
	if(preg_match('/[0-9]/', $password) != 1){
		throw new PasswordException('Password must contain at least one number.');
	}
	if(preg_match('/[^A-Za-z0-9]/', $password) != 1){
		throw new PasswordException('Password must contain at least one special character.');
	}
	$lowerPassword = strtolower($password);
	if(preg_match('/(.)\1{2,}/', $lowerPassword) == 1){
		throw new PasswordException('Password must not repeat 3 or more of the same character');
	}
	$commons = ['porsche', 'fire', 'bird', 'prince', 'rose', 'bud', 'guitar', 'butter', 'beach', 'jaguar', 'chelsea', 'united', 'amateur', 'great', '1234', 'black', 'turtle', 'cool', 'pussy', 'diamond', 'steelers', 'muffin', 'cooper', 'nascar', 'tiffany', 'redsox', '1313', 'dragon', 'zxcvbn', 'star', 'scorpio', 'cameron', 'tomcat', 'test', 'mountain', 'golf', 'shannon', 'madison', 'mustang', 'computer', 'bond', '007', 'murphy', '987654', 'letmein', 'amanda', 'bear', 'frank', 'brazil', 'baseball', 'wizard', 'tiger', 'hannah', 'lauren', 'master', 'doctor', 'dave', 'japan', 'michael', 'money', 'gateway', 'eagle', 'naked', 'football', 'phoenix', 'gators', 'squirt', 'shadow', 'mickey', 'angel', 'mother', 'monkey', 'bailey', 'junior', 'nathan', 'apple', 'abc123', 'knight', 'thx1138', 'raiders', 'pass', 'iceman', 'steve', 'badboy', 'forever', 'bonnie', '6969', 'purple', 'debbie', 'peaches', 'jordan', 'andrea', 'spider', 'viper', 'harley', 'horny', 'melissa', 'ou812', 'kevin', 'ranger', 'dakota', 'booger', 'jake', 'matt', 'iwantu', '1212', 'jennifer', 'player', 'flyers', 'suckit', 'hunter', 'sunshine', 'fish', 'gregory', 'beaver', 'fuck', 'morgan', 'porn', '4321', 'matrix', 'whatever', '4128', 'boomer', 'teens', 'young', 'runner', 'batman', 'scooby', 'nicholas', 'swimming', 'trustno1', 'edward', 'jason', 'lucky', 'dolphin', 'thomas', 'charles', 'walter', 'helpme', 'gordon', 'tigger', 'cum', 'casper', 'robert', 'booboo', 'boston', 'monica', 'stupid', 'access', 'coffee', 'braves', 'midnight', 'shit', 'love', 'yankee', 'college', 'saturn', 'buster', 'bulldog', 'baby', 'gemini', 'ncc1701', 'barney', 'cunt', 'soccer', 'rabbit', 'victor', 'brian', 'august', 'hockey', 'peanut', 'tucker', 'mark', 'killer', 'john', 'canada', 'george', 'mercedes', 'sierra', 'blazer', 'sexy', 'gandalf', '5150', 'leather', 'andrew', 'spanky', 'doggie', '232323', 'hunting', 'charlie', 'winter', 'kitty', 'brandy', 'gunner', 'beavis', 'rainbow', 'asshole', 'compaq', 'horney', 'cock', '112233', 'carlos', 'bubba', 'happy', 'arthur', 'dallas', 'tennis', '2112', 'sophie', 'cream', 'jessica', 'james', 'fred', 'ladies', 'calvin', 'panties', 'mike', 'naughty', 'shaved', 'pepper', 'brandon', 'giants', 'surfer', 'fender', 'tits', 'booty', 'samson', 'austin', 'anthony', 'member', 'blonde', 'kelly', 'william', 'boobs', 'paul', 'daniel', 'ferrari', 'donald', 'golden', 'mine', 'cookie', 'bigdaddy', 'king', 'summer', 'chicken', 'bronco', 'fire', 'racing', 'heather', 'penis', 'sandra', 'hammer', 'chicago', 'voyager', 'pookie', 'eagle', 'joseph', 'packers', 'hentai', 'joshua', 'diablo', 'einstein', 'newyork', 'maggie', 'sexsex', 'trouble', 'little', 'biteme', 'hardcore', 'white', 'redwings', 'enter', 'topgun', 'chevy', 'smith', 'ashley', 'willie', 'winston', 'sticky', 'thunder', 'welcome', 'warrior', 'cocacola', 'cowboy', 'chris', 'green', 'sammy', 'animal', 'silver', 'panther', 'super', 'slut', 'richard', 'yamaha', 'qazwsx', '8675309', 'private', 'justin', 'magic', 'skippy', 'orange', 'banana', 'lakers', 'marvin', 'merlin', 'driver', 'rachel', 'power', 'michelle', 'marine', 'slayer', 'enjoy', 'corvette', 'scott', 'girl', 'bigdog', 'vagina', 'apollo', 'cheese', 'david', 'asdf', 'toyota', 'parker', 'maddog', 'video', 'travis', 'qwert', 'hooters', 'london', 'hotdog', 'time', 'patrick', 'wilson', 'paris', 'sydney', 'martin', 'butthead', 'marlboro', 'rock', 'women', 'freedom', 'dennis', 'srinivas', 'voodoo', 'ginger', 'fuck', 'internet', 'extreme', 'magnum', 'blow', 'job', 'captain', 'action', 'redskins', 'juice', 'nicole', 'carter', 'erotic', 'abgrtyu', 'sparky', 'chester', 'jasper', 'dirty', 'yellow', 'smokey', 'monster', 'ford', 'dreams', 'camaro', 'xavier', 'teresa', 'maxwell', 'secret', 'jeremy', 'arsenal', 'music', 'dick', 'falcon', 'snoopy', 'bill', 'wolf', 'russia', 'taylor', 'blue', 'crystal', 'nipple', 'peter', 'rebecca', 'winner', 'pussies', 'alex', '123123', 'samantha', 'cock', 'florida', 'mistress', 'bitch', 'house', 'beer', 'eric', 'phantom', 'hello', 'miller', 'legend', 'scooter', 'flower', 'theman', 'movie', 'please', 'jack', 'oliver', 'success', 'albert'];
	foreach ($commons as $common){
		if(strpos($lowerPassword, $common) !== false){
			throw new PasswordException('Password must not contain frequently used password phrases.');
		}
	}
	return true;
}

function generateRandomPassword(){
	$lowerCase = 'abcdefghijklmnopqrstuvwxyz';
	$upperCase = strtoupper($lowerCase);
	$nums = '1234567890';
	$specialChars = '!@#$%^&*()';
	$types = array($lowerCase, $upperCase, $nums, $specialChars);
	$password = "";
	for ($i=0; $i < 10; $i++) { 
		$type = rand(0,count($types)-1);
		$chars = str_split($types[$type]);
		$char = rand(0,count($chars)-1);
		$char = $chars[$char];
		$password.=$char;
	}
	try{
		validPassword($password);
	}catch(Exception $e){
		return generateRandomPassword();
	}
	return $password;
}

function randomTag(){
	$tag = '#';
	$chars = str_split("026789CDEGHJLPQRSUVWY");
	$length = count($chars)-1;
	$maxLen = rand(8,10);
	for ($i=0; $i < $maxLen; $i++) { 
		$index = rand(0, $length);
		$tag .= $chars[$index];
	}
	return $tag;
}

function userHasAccessToUpdatePlayer($player, $anyone=true){
	global $loggedInUser;
	global $loggedInUserPlayer;
	$accessType = $player->get('accessType');
	if($accessType=='AN' && $anyone){
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

function userHasAccessToUpdateClan($clan, $anyone=true){
	global $loggedInUser;
	global $loggedInUserClan;
	$accessType = $clan->get('accessType');
	if($accessType=='AN' && $anyone){
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

function convertBackType($code){
	$clanType = array(
		'AN' => 'open',
		'IN' => 'inviteOnly',
		'CL' => 'closed');
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

function convertBackFrequency($code){
	$warFrequency = array(
		'NS' => 'unknown',
		'AL' => 'always',
		'NE' => 'never',
		'TW' => 'moreThanOncePerWeek',
		'OW' => 'oncePerWeek',
		'RA' => 'lessThanOncePerWeek');
	return $warFrequency[$code];
}

function convertRank($code){
	$ranks = array(
		'member' => '4',
		'admin' => '3',
		'coLeader' => '2',
		'leader' => '1');
	return $ranks[$code];
}

function convertBackRank($code){
	$ranks = array(
		'4' => 'member',
		'3' => 'admin',
		'2' => 'coLeader',
		'1' => 'leader');
	return $ranks[$code];
}

function convertLocation($location){
	if(isset($location)){
		return $location;
	}else{
		return 'Not Set';
	}
}

function refreshClanInfo($clan, $force=false){
	global $loggedInUser;
	if(!$force && isset($loggedInUser) && $loggedInUser->isAdmin()){
		return true; //Temporary to help reduce API calls (#37)
	}
	try{
		if(hourAgo() > strtotime($clan->get('dateModified')) || $force || DEVELOPEMENT){
			$api = new ClanAPI();
			$clanInfo = $api->getClanInformation($clan->get('tag'));
			if($clanInfo->isWarLogPublic){
				$warLogInfo = $api->getWarLog($clan->get('tag'));
			}
		}else{
			return true;
		}
	}catch(APIException $e){
		error_log($e->getReasonMessage());
	}catch(Exception $e){
		error_log($e->getMessage());
	}
	if(!isset($clanInfo)){
		return false;
	}
	$clan->updateFromApi($clanInfo);
	if($clanInfo->members > 0){
		$tags = [];
		foreach($clanInfo->memberList as $apiMember){
			$tags[] = $apiMember->tag;
		}
		$players = Player::getPlayersAndTheirClansFromTags($tags);
		$members = $clan->getMembers();
		foreach($clanInfo->memberList as $apiMember){
			$player = $players[$apiMember->tag];
			if(!isset($player)){
				$player = new Player();
				$player->create($apiMember->name, $apiMember->tag);
				$playerClan = null;
			}else{
				$playerClan = $player->get('clan');
			}
			if(!isset($playerClan) || $playerClan->get('id') != $clan->get('id')){
				$clan->addPlayer($player);
			}
			unset($members[$player->get('id')]);
			$player->updateFromApi($apiMember);
		}
		foreach ($members as $member){
			$member->leaveClan();
		}
	}
	if(isset($warLogInfo)){
		$apiWars = $warLogInfo->items;
		$wars = $clan->getWars();
		foreach ($apiWars as $apiWar){
			foreach ($wars as $key => $war){
				if(warsMatch($apiWar, $war)){
					unset($wars[$key]);
					$war->updateFromApi($apiWar);
					continue;
				}
			}
		}
	}
	return true;
}

function warsMatch($apiWar, $war){
	if(($apiWar->clan->tag == $war->get('clan1')->get('tag') && $apiWar->opponent->tag == $war->get('clan2')->get('tag')) || ($apiWar->clan->tag == $war->get('clan2')->get('tag') && $apiWar->opponent->tag == $war->get('clan1')->get('tag'))){
		return $apiWar->teamSize == $war->get('size');
	}else{
		return false;
	}
}

function cpr($var, $limit=2, $tab=""){
	$val = '';
	if(is_array($var)){
		if($limit<0){
			return "DEPTH LIMIT REACHED";
		}
		$val .= "Array\n" . $tab . "(\n";
		foreach ($var as $key => $value) {
			$val .= $tab . "\t[" . $key . '] => ' . cpr($value, $limit-1, $tab."\t\t") . "\n";
		}
		$val .= $tab . ")";
	}elseif(is_object($var)){
		if($limit<0){
			return "DEPTH LIMIT REACHED";
		}
		$class = get_class($var);
		global $classes;
		if(in_array($class, $classes)){
			$reflect = new ReflectionClass($var);
			$props = $reflect->getProperties();
			$varArray = array();
			foreach ($props as $prop) {
				$key = $prop->name;
				$value = null;
				try{
					$value = $var->get($key);
				}catch(Exception $e){
					//ignore
				}
				if(isset($value)){
					$varArray[$key] = $value;
				}
			}
		}else{
			$varArray = get_object_vars($var);
		}
		$val .= $class . " Object\n" . $tab . "(\n";
		foreach ($varArray as $key => $value) {
			$val .= $tab . "\t[" . $key . '] => ' . cpr($value, $limit-1, $tab."\t\t") . "\n";
		}
		$val .= $tab . ")";
	}else{
		if(is_null($var)){
			$val .= "NULL";
		}elseif($var === true){
			$val .= "TRUE";
		}elseif($var === false){
			$val .= "FALSE";
		}else{
			$val .= $var;
		}
	}
	return $val;
}

function email($to, $subject, $message, $from){
	if(HEROKU && PRODUCTION){
		try{
			$sendgrid_username = $_ENV['SENDGRID_USERNAME'];
			$sendgrid_password = $_ENV['SENDGRID_PASSWORD'];
			$sendgrid = new SendGrid($sendgrid_username, $sendgrid_password, array("turn_off_ssl_verification" => true));
			$email = new SendGrid\Email();
			$email->addTo($to)->
					setFrom($from)->
					setSubject($subject)->
					setText($message)->
					addHeader('X-Sent-Using', 'SendGrid-API')->
					addHeader('X-Transport', 'web');
			$response = $sendgrid->send($email);
			$responseBody = $response->getBody();
			return isset($responseBody) && $responseBody['message'] == 'success';
		}catch(Exception $e){
			error_log($e->getMessage());
			return false;
		}
	}else{
		return false;
	}
}

function correctDateFormat($date){
	if(is_numeric($date)){
		return date('Y-m-d H:i:s', $date);
	}else{
		return date('Y-m-d H:i:s', strtotime($date));
	}
}

function displayName($name){
	$name = htmlspecialchars($name);
	return str_replace(' ', '&nbsp;', $name);
}

function sanitizeClanWarMessage($message){
	$tags = array('b','strong','i','em','mark','small','del','ins','sub','sup');
	$stack = array();
	$sanitized = '';
	for ($i=0; $i < strlen($message); $i++) {
		if(substr($message,$i,1)=="\n"){
			$sanitized .= '<br>';
			continue;
		}
		if(count($stack)>0){
			$endTag = endTag(end($stack));
			if($endTag == substr($message,$i,strlen($endTag))){
				array_pop($stack);
				$sanitized .= $endTag;
				$i+=strlen($endTag)-1;
				continue;
			}
		}
		foreach ($tags as $tag) {
			$startTag = startTag($tag);
			if($startTag == substr($message,$i,strlen($startTag))){
				array_push($stack, $tag);
				$sanitized .= $startTag;
				$i+=strlen($startTag);
				continue;
			}
		}
		$sanitized .= htmlspecialchars(substr($message,$i,1));
	}
	$stack = array_reverse($stack);
	foreach ($stack as $tag) {
		$sanitized .= endTag($tag);
	}
	return $sanitized;
}

function startTag($tag){
	return "<$tag>";
}

function endTag($tag){
	return "</$tag>";
}

function linkify($string){
	return MakeItLink::transform($string);
}
