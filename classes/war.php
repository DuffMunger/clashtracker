<?
class War{
	private $id;
	private $firstClanId;
	private $clan1;
	private $secondClanId;
	private $clan2;
	private $size;
	private $dateCreated;
	private $dateModified;
	private $clanAttacks;
	private $playerAttacks;
	private $warAttacks;
	private $starsLocked;
	private $destruction;
	private $experience;
	private $messages;
	private $status;
	const PREPARATION = 0;
	const BATTLE = 1;
	const COMPLETE = 2;

	private $acceptGet = array(
		'id' => 'id',
		'first_clan_id' => 'firstClanId',
		'second_clan_id' => 'secondClanId',
		'size' => 'size',
		'date_created' => 'dateCreated',
		'date_modified' => 'dateModified',
		'stars_locked' => 'starsLocked',
		'status' => 'status'
	);

	private $acceptSet = array(
		'size' => 'size',
		'first_clan_message' => 'firstClanMessage',
		'second_clan_message' => 'secondClanMessage',
		'first_clan_stars' => 'firstClanStars',
		'second_clan_stars' => 'secondClanStars',
		'clan_stars' => 'clanStars',
		'clan_message' => 'clanMessage',
		'status' => 'status'
	);

	public function create($clan1, $clan2, $size){
		global $db;
		if(!isset($this->id)){
			$clan1Id = $clan1->get('id');
			$clan2Id = $clan2->get('id');
			$procedure = buildProcedure('p_war_create', $clan1Id, $clan2Id, $size, date('Y-m-d H:i:s', time()));
			if(($db->multi_query($procedure)) === TRUE){
				$result = $db->store_result()->fetch_object();
				while ($db->more_results()){
					$db->next_result();
				}
				$this->id = $result->id;
				$this->load();
				$this->updateClanWarStats($clan1);
				$this->updateClanWarStats($clan2);
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID set, cannot create.');
		}
	}

	public function __construct($id=null){
		$this->clanWarPlayers = array();
		$this->clanStars = array();
		$this->playerRanks = array();
		$this->playerDefences = array();
		$this->messages = array();
		if(isset($id)){
			$this->id = $id;
			$this->load();
		}
	}

	public function load(){
		global $db;
		if(isset($this->id)){
			$procedure = buildProcedure('p_war_load', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				if ($results->num_rows) {
					$record = $results->fetch_object();
					$results->close();
					$this->id = $record->id;
					$this->firstClanId = $record->first_clan_id;
					$this->secondClanId = $record->second_clan_id;
					$this->size = $record->size;
					$this->messages[$this->firstClanId] = $record->first_clan_message;
					$this->messages[$this->secondClanId] = $record->second_clan_message;
					$this->dateCreated = $record->date_created;
					$this->dateModified = $record->date_modified;
					$this->clanStars[$this->firstClanId] = $record->first_clan_stars;
					$this->clanStars[$this->secondClanId] = $record->second_clan_stars;
					$this->starsLocked = $record->stars_locked == 1;
					$this->destruction[$this->firstClanId] = $record->first_clan_destruction;
					$this->destruction[$this->secondClanId] = $record->second_clan_destruction;
					$this->experience[$this->firstClanId] = $record->first_clan_experience;
					$this->experience[$this->secondClanId] = $record->second_clan_experience;
					$this->status = $record->status;
				}else{
					throw new NoResultFoundException('No clan found with id ' . $this->id);
				}
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for load.');
		}
	}

	public function loadByObj($warObj){
		$this->id = $warObj->id;
		$this->firstClanId = $warObj->first_clan_id;
		$clan = new stdClass();
		$clan->id = $this->firstClanId;
		$clan->name = $warObj->first_clan_name;
		$clan->tag = $warObj->first_clan_tag;
		$this->clan1 = new Clan();
		$this->clan1->loadByObj($clan);
		$this->secondClanId = $warObj->second_clan_id;
		$clan->tag = $warObj->second_clan_tag;
		$clan->id = $this->secondClanId;
		$clan->name = $warObj->second_clan_name;
		$this->clan2 = new Clan();
		$this->clan2->loadByObj($clan);
		$this->size = $warObj->size;
		$this->messages[$this->firstClanId] = $warObj->first_clan_message;
		$this->messages[$this->secondClanId] = $warObj->second_clan_message;
		$this->dateCreated = $warObj->date_created;
		$this->dateModified = $warObj->date_modified;
		$this->clanStars[$this->firstClanId] = $warObj->first_clan_stars;
		$this->clanStars[$this->secondClanId] = $warObj->second_clan_stars;
		$this->starsLocked = $warObj->stars_locked == 1;
		$this->destruction[$this->firstClanId] = $warObj->first_clan_destruction;
		$this->destruction[$this->secondClanId] = $warObj->second_clan_destruction;
		$this->experience[$this->firstClanId] = $warObj->first_clan_experience;
		$this->experience[$this->secondClanId] = $warObj->second_clan_experience;
		$this->status = $warObj->status;
	}

	public function get($prpty){
		if(isset($this->id)){
			if(in_array($prpty, $this->acceptGet)){
				return $this->$prpty;
			}elseif($prpty == 'clan1'){
				if(!isset($this->clan1)){
					$this->clan1 = new clan($this->firstClanId);
					return $this->clan1;
				}else{
					return $this->clan1;
				}
			}elseif($prpty == 'clan2'){
				if(!isset($this->clan2)){
					$this->clan2 = new clan($this->secondClanId);
					return $this->clan2;
				}else{
					return $this->clan2;
				}
			}else{
				throw new OperationException('Property is not in accept get.');
			}
		}else{
			throw new FunctionCallException('ID not set for get.');
		}
	}

	public function set($prpty, $value, $clanId=null){
		global $db;
		if(isset($this->id)){
			if(in_array($prpty, $this->acceptSet)){
				if($prpty == 'clanStars' || $prpty == 'clanMessage'){
					if($clanId == $this->firstClanId){
						$prpty = 'first' . ucfirst($prpty);
					}elseif($clanId == $this->secondClanId){
						$prpty = 'second' . ucfirst($prpty);
					}else{
						throw new WarClanException('Clan not in war.');
					}
				}
				$procedure = buildProcedure('p_war_set', $this->id, array_search($prpty, $this->acceptSet), $value, date('Y-m-d H:i:s', time()));
				if(($db->multi_query($procedure)) === TRUE){
					while ($db->more_results()){
						$db->next_result();
					}
					if(strpos($prpty, 'ClanStars') !== false){
						$this->clanStars[$clanId] = $value;
					}elseif(strpos($prpty, 'ClanMessage') !== false){
						$this->messages[$clanId] = $value;
					}else{
						$this->$prpty = $value;
					}
				}else{
					throw new SQLQueryException('The database encountered an error. ' . $db->error);
				}
			}else{
				throw new OperationException('Property is not in accept set.');
			}
		}else{
			throw new FunctionCallException('ID not set for set.');
		}
	}

	public function getMessage($clanId){
		$message =  $this->messages[$clanId];
		if(strlen($message)>0){
			return $message;
		}
		return null;
	}

	public function isClanInWar($clanId){
		return ($clanId == $this->firstClanId || $clanId == $this->secondClanId);
	}

	public function addPlayer($playerId){
		if(isset($this->id)){
			$player = new player($playerId);
			$playerId = $player->get('id');
			$clan = $player->getClan();
			if(isset($clan) && $this->isClanInWar($clan->get('id'))){
				if(count($this->getPlayers($clan)) < $this->size){
					global $db;
					$procedure = buildProcedure('p_war_add_player', $this->id, $playerId, $clan->get('id'), date('Y-m-d H:i:s', time()));
					if(($db->multi_query($procedure)) === TRUE){
						while ($db->more_results()){
							$db->next_result();
						}
						$this->warPlayers[] = $player;
						$this->clanWarPlayers[$clan->get('id')][] = $player;
						$this->updateRanks($clan);
					}else{
						throw new SQLQueryException('The database encountered an error. ' . $db->error);
					}
				}else{
					throw new WarPlayerException('Cannot add player to war. Already ' . $this->size . ' members for this clan.');
				}
			}else{
				throw new WarPlayerException('Cannot add player to war. Player not in either war clan.');
			}
		}else{
			throw new FunctionCallException('ID not set to add players.');
		}
	}

	public function removePlayer($playerId){
		if(isset($this->id)){
			$player = new player($playerId);
			$playerId = $player->get('id');
			if($this->isPlayerInWar($playerId)){
				global $db;
				$procedure = buildProcedure('p_war_remove_player', $this->id, $playerId, date('Y-m-d H:i:s', time()));
				$playerClan = $this->getPlayerWarClan($playerId);
				if(($db->multi_query($procedure)) === TRUE){
					while ($db->more_results()){
						$db->next_result();
					}
					$this->updateRanks($playerClan);
				}else{
					throw new SQLQueryException('The database encountered an error. ' . $db->error);
				}
			}else{
				throw new WarPlayerException('Cannot remove player from war. Player not in war.');
			}
		}else{
			throw new FunctionCallException('ID not set to remove players.');
		}
	}

	public function getPlayers($clan=null){
		global $db;
		if(isset($this->id)){
			if(isset($clan)){
				$clanId = $clan->get('id');
				if(!$this->isClanInWar($clanId)){
					throw new WarClanException('Clan not in war.');
				}
				if(isset($this->clanWarPlayers[$clanId])){
					return $this->clanWarPlayers[$clanId];
				}
			}else{
				$clanId = '%';
				if(isset($this->warPlayers)){
					return $this->warPlayers;
				}
			}
			$procedure = buildProcedure('p_war_get_players', $this->id, $clanId);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				$players = array();
				if ($results->num_rows) {
					while ($playerObj = $results->fetch_object()) {
						$player = new Player();
						$player->loadByObj($playerObj);
						$this->playerRanks[$player->get('id')] = $playerObj->rank;
						$players[] = $player;
					}
				}
				if(isset($clan)){
					$this->clanWarPlayers[$clanId] = $players;
				}else{
					$this->warPlayers = $players;
				}
				return $players;
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set to get war players.');
		}
	}

	public function isPlayerInWar($playerId){
		$warPlayers = $this->getPlayers();
		foreach ($warPlayers as $warPlayer) {
			if($warPlayer->get('id') == $playerId){
				return true;
			}
		}
		return false;
	}

	public function addAttack($attackerId, $defenderId, $stars){
		global $db;
		if(isset($this->id)){
			if($this->isPlayerInWar($attackerId) && $this->isPlayerInWar($defenderId)){
				$attacker = new player($attackerId);
				$attackerId = $attacker->get('id');
				$attackerClan = $this->getPlayerWarClan($attacker->get('id'));
				$defender = new player($defenderId);
				$defenderId = $defender->get('id');
				$defenderClan = $this->getPlayerWarClan($defender->get('id'));
				if($attackerClan->get('id') != $defenderClan->get('id')){
					if($stars >=0 && $stars <= 3){
						$procedure = buildProcedure('p_war_add_attack', $this->id, $attackerId, $defenderId, $attackerClan->get('id'), $defenderClan->get('id'), $stars, date('Y-m-d H:i:s', time()));
						if(($db->multi_query($procedure)) === TRUE){
							while ($db->more_results()){
								$db->next_result();
							}
						}else{
							throw new SQLQueryException('The database encountered an error. ' . $db->error);
						}
					}else{
						throw new Exception('Invalid amount of stars for an attack. Must be between 0-3.');
					}
				}else{
					throw new WarPlayerException('Attacker and defender cannot be from the same clan.');
				}
			}else{
				throw new WarPlayerException('Attacker and/or defender not in war.');
			}
		}else{
			throw new FunctionCallException('ID not set to add war attacks.');
		}
	}

	public function updateAttack($attackerId, $defenderId, $stars){
		$this->addAttack($attackerId, $defenderId, $stars);
	}

	public function removeAttack($attacker, $defender){
		global $db;
		if(isset($this->id)){
			$attackerId = $attacker->get('id');
			$defenderId = $defender->get('id');
			$procedure = buildProcedure('p_war_remove_attack', $this->id, $attackerId, $defenderId, date('Y-m-d H:i:s', time()));
			if(($db->multi_query($procedure)) === TRUE){
				while ($db->more_results()){
					$db->next_result();
				}
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set to remove war attacks.');
		}
	}

	public function getAttacks($clan=null){
		global $db;
		if(isset($this->id)){
			if(isset($clan)){
				$clanId = $clan->get('id');
				if(!$this->isClanInWar($clanId)){
					throw new WarClanException('Clan not in war.');
				}
				if(isset($this->clanAttacks[$clanId])){
					return $this->clanAttacks[$clanId];
				}
			}else{
				if(isset($this->warAttacks)){
					return $this->warAttacks;
				}
			}
			$procedure = buildProcedure('p_war_get_attacks', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				$this->clanAttacks = array();
				$this->clanAttacks[$this->firstClanId] = array();
				$this->clanAttacks[$this->secondClanId] = array();
				$this->playerAttacks = array();
				$this->playerDefences = array();
				$this->warAttacks = array();
				if ($results->num_rows) {
					while ($warAttackObj = $results->fetch_object()) {
						$warAttack = array();
						$warAttack['warId'] = $warAttackObj->war_id;
						$warAttack['attackerId'] = $warAttackObj->attacker_id;
						$warAttack['defenderId'] = $warAttackObj->defender_id;
						$warAttack['attackerClanId'] = $warAttackObj->attacker_clan_id;
						$warAttack['defenderClanId'] = $warAttackObj->defender_clan_id;
						$totalStars = $warAttackObj->stars;
						$warAttack['totalStars'] = $totalStars;
						$defenderDefences = $this->getPlayerDefences($warAttack['defenderId']);
						$starsAchievedSoFar = 0;
						foreach ($defenderDefences as $defence) {
							if($defence['attackerId'] != $warAttack['attackerId']){
								$starsAchievedSoFar += $defence['newStars'];
							}else{
								break;
							}
						}
						$newStars = max(0, $totalStars - $starsAchievedSoFar);
						$warAttack['newStars'] = $newStars;
						$warAttack['dateCreated'] = $warAttackObj->date_created;
						$warAttack['dateModified'] = $warAttackObj->date_modified;
						$warAttack['attackerRank'] = $warAttackObj->attacker_rank;
						$warAttack['defenderRank'] = $warAttackObj->defender_rank;

						$this->warAttacks[] = $warAttack;
						$this->playerAttacks[$warAttack['attackerId']][] = $warAttack;
						$this->playerDefences[$warAttack['defenderId']][] = $warAttack;
						$this->clanAttacks[$warAttack['attackerClanId']][] = $warAttack;
					}
				}
				if(isset($clan)){
					return $this->clanAttacks[$clanId];
				}else{
					return $this->warAttacks;
				}
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set to get war attacks.');
		}
	}

	public function getAttack($attackerId, $defenderId){
		if(isset($this->id)){
			if(!isset($this->playerAttacks)){
				$this->getAttacks();
			}
			$attacks = $this->playerAttacks[$attackerId];
			$firstAttack = $attacks[0];
			if(isset($firstAttack)){
				if($firstAttack['defenderId'] == $defenderId){
					return $firstAttack;
				}
			}
			$secondAttack = $attacks[1];
			if(isset($secondAttack)){
				if($secondAttack['defenderId'] == $defenderId){
					return $secondAttack;
				}
			}
			return null;
		}else{
			throw new FunctionCallException('ID not set to remove war attacks.');
		}
	}

	public function getPlayerAttacks($player){
		if(isset($this->id)){
			$playerId = $player->get('id');
			if(!$this->isPlayerInWar($playerId)){
				throw new WarPlayerException('Player not in war.');
			}
			if(!isset($this->playerAttacks)){
				$this->getAttacks();
			}
			return $this->playerAttacks[$playerId];
		}else{
			throw new FunctionCallException('ID not set to get war attacks.');
		}
	}

	public function getPlayerDefences($playerId){
		if(isset($this->id)){
			if(!$this->isPlayerInWar($playerId)){
				throw new WarPlayerException('Player not in war.');
			}
			if(!isset($this->playerDefences)){
				$this->getAttacks();
			}
			if(isset($this->playerDefences[$playerId])){
				return $this->playerDefences[$playerId];
			}else{
				return array();
			}
		}else{
			throw new FunctionCallException('ID not set to get war attacks.');
		}
	}

	public function getClanStars($clan, $force=false){
		if(!$force || $this->starsLocked){
			return (int)$this->clanStars[$clan->get('id')];
		}
		$clanAttacks = $this->getAttacks($clan);
		$count = 0;
		foreach ($clanAttacks as $attack) {
			$count += $attack['newStars'];
		}
		if($count != $this->clanStars[$clan->get('id')]){
			$this->set('clanStars', $count, $clan->get('id'));
		}
		return $count;
	}

	public function getDestruction($clan){
		return $this->destruction[$clan->get('id')];
	}

	public function getExperience($clan){
		return $this->experience[$clan->get('id')];
	}

	public function getEnemy($clanId){
		if($this->isClanInWar($clanId)){
			if($clanId == $this->firstClanId){
				return $this->get('clan2');
			}else{
				return $this->get('clan1');
			}
		}else{
			throw new WarClanException('Clan not in war.');
		}
	}

	public static function getWars($pageSize=50){
		global $db;
		$procedure = buildProcedure('p_get_wars', $pageSize);
		if(($db->multi_query($procedure)) === TRUE){
			$results = $db->store_result();
			while ($db->more_results()){
				$db->next_result();
			}
			$wars = array();
			if ($results->num_rows) {
				while ($warObj = $results->fetch_object()) {
					$war = new War();
					$war->loadByObj($warObj);
					$wars[] = $war;
				}
			}
			return $wars;
		}else{
			throw new SQLQueryException('The database encountered an error. ' . $db->error);
		}
	}

	public function getPlayerWarClan($playerId){
		if($this->isPlayerInWar($playerId)){
			global $db;
			$procedure = buildProcedure('p_war_get_player_war_clan', $this->id, $playerId);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				$clan = null;
				if ($results->num_rows) {
					$record = $results->fetch_object();
					$results->close();
					$clan = new clan($record->clan_id);
				}
				return $clan;
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new WarPlayerException('Player not in war.');
		}
	}

	public function updatePlayerRank($playerId, $rank){
		if(isset($this->id)){
			global $db;
			$procedure = buildProcedure('p_war_update_player_rank', $this->id, $playerId, $rank, date('Y-m-d H:i:s', time()));
			if(($db->multi_query($procedure)) === TRUE){
				while ($db->more_results()){
					$db->next_result();
				}
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}			
		}else{
			throw new FunctionCallException('ID not set for war ranks.');
		}
	}

	public function getHighestRank(){
		if(isset($this->id)){
			global $db;
			$procedure = buildProcedure('p_war_get_highest_rank', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				$rank = 0;
				if ($results->num_rows) {
					$record = $results->fetch_object();
					$results->close();
					$rank = $record->rank;
				}
				return $rank;
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for war ranks.');
		}
	}

	public function updateRanks($clan=null){
		if(!$this->isClanInWar($clan->get('id'))){
			$this->updateRanks($this->get('clan1'));
			$this->updateRanks($this->get('clan2'));
			return;
		}
		$warPlayers = $this->getPlayers($clan);
		$clanId = $clan->get('id');
		for ($i=1; $i < count($warPlayers); $i++) { 
			$j=$i;
			while ($j>0 && ($warPlayers[$j-1]->get('warRank', $clanId) > $warPlayers[$j]->get('warRank', $clanId))){
				$temp = $warPlayers[$j];
				$warPlayers[$j] = $warPlayers[$j-1];
				$warPlayers[$j-1] = $temp;
				$j--;
			}
		}
		$count = 1;
		foreach ($warPlayers as $player) {
			$prevRank = $this->getPlayerRank($player->get('id'));
			if($prevRank!=$count){
				$this->updatePlayerRank($player->get('id'), $count);
			}
			$count++;
		}
	}

	public function getPlayerRank($playerId){
		if(isset($this->id)){
			if($this->isPlayerInWar($playerId)){
				if(isset($this->playerRanks[$playerId])){
					return $this->playerRanks[$playerId];
				}
				global $db;
				$procedure = buildProcedure('p_war_get_player_rank', $this->id, $playerId);
				if(($db->multi_query($procedure)) === TRUE){
					$results = $db->store_result();
					while ($db->more_results()){
						$db->next_result();
					}
					$rank = 0;
					if ($results->num_rows) {
						$record = $results->fetch_object();
						$results->close();
						$rank = $record->rank;
					}
					$this->playerRanks[$playerId] = $rank;
					return $rank;
				}else{
					throw new SQLQueryException('The database encountered an error. ' . $db->error);
				}
			}else{
				throw new WarPlayerException('Player not in war.');
			}
		}else{
			throw new FunctionCallException('ID not set for war ranks.');
		}
	}

	public function getPlayerByRank($rank, $clanId){
		if(isset($this->id)){
			if($this->isClanInWar($clanId)){
				global $db;
				$procedure = buildProcedure('p_war_get_player_by_rank', $this->id, $clanId, $rank);
				if(($db->multi_query($procedure)) === TRUE){
					$results = $db->store_result();
					while ($db->more_results()){
						$db->next_result();
					}
					$player = null;
					if ($results->num_rows) {
						$record = $results->fetch_object();
						$results->close();
						$player = new player($record->player_id);
					}
					return $player;
				}else{
					throw new SQLQueryException('The database encountered an error. ' . $db->error);
				}
			}else{
				throw new WarClanException('Clan not in war.');
			}
		}else{
			throw new FunctionCallException('ID not set for war ranks.');
		}
	}

	// DEPRECATED, will be removed once #46 and #60 are complete and pushed to production
	public function oldIsEditable(){
		trigger_error('War::oldIsEditable is deprecated and will be removed at a later date.', E_USER_WARNING);
		$clan1 = $this->get('clan1');
		$clan1Wars = $clan1->getWars();
		$isClan1CurrWar = $clan1Wars[0]->get('id') == $this->id;

		$clan2 = $this->get('clan2');
		$clan2Wars = $clan2->getWars();
		$isClan2CurrWar = $clan2Wars[0]->get('id') == $this->id;

		return $isClan1CurrWar && $isClan2CurrWar;
	}

	public function isEditable(){
		return $this->isBattleDay() || $this->isPreparationDay();
	}

	public function isPreparationDay(){
		return $this->status == $this::PREPARATION;
	}

	public function isBattleDay(){
		return $this->status == $this::BATTLE;
	}

	public function isComplete(){
		return $this->status == $this::COMPLETE;
	}

	public function revokeAllAccess(){
		global $db;
		if(isset($this->id)){
			$procedure = buildProcedure('p_war_disallow_all_users', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				while ($db->more_results()){
					$db->next_result();
				}
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for revoke.');
		}	
	}

	public function resetAccess(){
		$this->revokeAllAccess();
	}

	public function getAllowedUsers(){
		global $db;
		if(isset($this->id)){
			$procedure = buildProcedure('p_war_get_allowed_users', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				$users = array();
				if ($results->num_rows) {
					while ($userObj = $results->fetch_object()) {
						$user = new User($userObj->user_id);
						$users[] = $user;
					}
				}
				return $users;
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for get.');
		}
	}

	public function grantUserAccess($user){
		global $db;
		if(isset($this->id)){
			$procedure = buildProcedure('p_war_allow_user', $this->id, $user->get('id'));
			if(($db->multi_query($procedure)) === TRUE){
				while ($db->more_results()){
					$db->next_result();
				}
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for grant.');
		}	
	}

	public function revokeUserAccess($userId){
		global $db;
		if(isset($this->id)){
			$user = new User($userId);
			$procedure = buildProcedure('p_war_disallow_user', $this->id, $user->get('id'));
			if(($db->multi_query($procedure)) === TRUE){
				while ($db->more_results()){
					$db->next_result();
				}
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for revoke.');
		}
	}

	public function requestAccess($user, $message=""){
		global $db;
		if(isset($this->id)){
			$procedure = buildProcedure('p_war_edit_request_create', $this->id, $user->get('id'), $message);
			if(($db->multi_query($procedure)) === TRUE){
				while ($db->more_results()){
					$db->next_result();
				}
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for request.');
		}
	}

	public function deleteRequest($user){
		global $db;
		if(isset($this->id)){
			$procedure = buildProcedure('p_war_edit_request_delete', $this->id, $user->get('id'));
			if(($db->multi_query($procedure)) === TRUE){
				while ($db->more_results()){
					$db->next_result();
				}
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for delete request.');
		}
	}

	public function getRequests(){
		global $db;
		if(isset($this->id)){
			$procedure = buildProcedure('p_war_get_edit_requests', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				$editRequests = array();
				if ($results->num_rows) {
					while ($editRequestObj = $results->fetch_object()) {
						$editRequest = new stdClass();
						$editRequest->war = $this;
						$user = new User();
						$user->loadByObj($editRequestObj);
						$editRequest->user = $user;
						$editRequest->message = $editRequestObj->message;
						$editRequests[] = $editRequest;
					}
				}
				return $editRequests;
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set to get requests.');
		}
	}

	public function userHasRequested($userId){
		$requests = $this->getRequests();
		foreach ($requests as $request) {
			if($request->user->get('id') == $userId){
				return true;
			}
		}
		return false;
	}

	private function updateClanWarStats($clan){
		$wars = $clan->getWars();
		if(count($wars)>1){
			$war = $wars[1];
			$war->getAttacks();
			$players = $war->getPlayers($clan);
			foreach ($players as $player) {
				$attacks = $war->getPlayerAttacks($player);
				$numOfWars = $player->get('numberOfWars');
				# These changes are a temporary fix for #62, until I have the time to do #54
				if(!isset($numOfWars) || $numOfWars == 0){
					$rankAttacked = $origRankAttacked = 0;
					$rankDefended = $origRankDefended = 0;
				}else{
					$rankAttacked = $origRankAttacked = $player->get('rankAttacked');
					$rankDefended = $origRankDefended = $player->get('rankDefended');
				}
				$player->set('numberOfWars', $numOfWars + 1);
				$player->set('attacksUsed', $player->get('attacksUsed') + count($attacks));
				$firstAttack = $attacks[0];
				if(isset($firstAttack)){
					$player->set('firstAttackTotalStars', $player->get('firstAttackTotalStars') + $firstAttack['totalStars']);
					$player->set('firstAttackNewStars', $player->get('firstAttackNewStars') + $firstAttack['newStars']);
					$rankAttacked += $firstAttack['attackerRank'] - $firstAttack['defenderRank'];
				}
				$secondAttack = $attacks[1];
				if(isset($secondAttack)){
					$player->set('secondAttackTotalStars', $player->get('secondAttackTotalStars') + $secondAttack['totalStars']);
					$player->set('secondAttackNewStars', $player->get('secondAttackNewStars') + $secondAttack['newStars']);
					$rankAttacked += $secondAttack['attackerRank'] - $secondAttack['defenderRank'];
				}
				if($rankAttacked != $origRankAttacked){
					$player->set('rankAttacked', $rankAttacked);
				}
				$defences = $war->getPlayerDefences($player->get('id'));
				$stars = 0;
				foreach ($defences as $defence) {
					$stars += $defence['newStars'];
					$rankDefended += $defence['defenderRank'] - $defence['attackerRank'];
				}
				if($rankDefended != $origRankDefended){
					$player->set('rankDefended', $rankDefended);
				}
				$player->set('numberOfDefences', $player->get('numberOfDefences') + count($defences));
				$player->set('starsOnDefence', $player->get('starsOnDefence') + $stars);
			}
		}
	}

	public function updateFromApi($apiWar){
		global $db;
		if(isset($this->id)){
			if($this->get('clan1')->get('tag') == $apiWar->clan->tag){
				$apiClan1 = $apiWar->clan;
				$apiClan2 = $apiWar->opponent;
			}elseif($this->get('clan2')->get('tag') == $apiWar->clan->tag){
				$apiClan1 = $apiWar->opponent;
				$apiClan2 = $apiWar->clan;
			}else{
				throw new ArgumentException('apiWar does not match war.');
			}
			if($apiClan1->expEarned == $this->experience[$this->firstClanId] && $apiClan2->expEarned == $this->experience[$this->secondClanId]){
				return;
			}
			if(!isset($this->experience[$this->firstClanId])){
				$this->experience[$this->firstClanId] = $apiClan1->expEarned;
			}
			if(!isset($this->experience[$this->secondClanId])){
				$this->experience[$this->secondClanId] = $apiClan2->expEarned;
			}
			$procedure = buildProcedure('p_war_update_bulk', $this->id, $apiClan1->stars, $apiClan2->stars, $apiClan1->destructionPercentage, $apiClan2->destructionPercentage, $this->experience[$this->firstClanId], $this->experience[$this->secondClanId]);
			if(($db->multi_query($procedure)) === TRUE){
				while ($db->more_results()){
					$db->next_result();
				}
				$this->starsLocked = true;
				$this->clanStars[$this->firstClanId] = $apiClan1->stars;
				$this->clanStars[$this->secondClanId] = $apiClan2->stars;
				$this->firstClanDestruction = $apiClan1->destructionPercentage;
				$this->secondClanDestruction = $apiClan2->destructionPercentage;
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for update.');
		}
	}

	public function delete(){
		if(isset($this->id)){
			$players = $this->getPlayers();
			if(count($players)==0){
				global $db;
				$procedure = buildProcedure('p_war_delete', $this->id);
				if(($db->multi_query($procedure)) === TRUE){
					while ($db->more_results()){
						$db->next_result();
					}
				}else{
					throw new SQLQueryException('The database encountered an error. ' . $db->error);
				}
			}
		}else{
			throw FunctionCallException('ID not set for delete.');
		}
	}
}