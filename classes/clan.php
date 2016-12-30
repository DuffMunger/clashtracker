<?
class Clan{
	private $id;
	private $name;
	private $tag;
	private $description;
	private $clanType;
	private $minimumTrophies;
	private $warFrequency;
	private $dateCreated;
	private $dateModified;
	private $apiUpdated;
	private $members;
	private $clanLevel;
	private $clanPoints;
	private $warWins;
	private $badgeUrl;
	private $location;
	private $accessType;
	private $minRankAccess;
	private $firstAttackWeight;
	private $secondAttackWeight;
	private $totalStarsWeight;
	private $newStarsWeight;
	private $defenceWeight;
	private $numberOfDefencesWeight;
	private $attacksUsedWeight;
	private $rankAttackedWeight;
	private $rankDefendedWeight;

	private $acceptGet = array(
		'id' => 'id',
		'name' => 'name',
		'tag' => 'tag',
		'description' => 'description',
		'clan_type' => 'clanType',
		'minimum_trophies' => 'minimumTrophies',
		'war_frequency' => 'warFrequency',
		'date_created' => 'dateCreated',
		'date_modified' => 'dateModified',
		'api_updated' => 'apiUpdated',
		'access_type' => 'accessType',
		'min_rank_access' => 'minRankAccess',
		'members' => 'members',
		'clan_level' => 'clanLevel',
		'clan_points' => 'clanPoints',
		'war_wins' => 'warWins',
		'location' => 'location',
		'badge_url' => 'badgeUrl',
		'first_attack_weight' => 'firstAttackWeight',
		'second_attack_weight' => 'secondAttackWeight',
		'total_stars_weight' => 'totalStarsWeight',
		'new_stars_weight' => 'newStarsWeight',
		'defence_weight' => 'defenceWeight',
		'number_of_defences_weight' => 'numberOfDefencesWeight',
		'attacks_used_weight' => 'attacksUsedWeight',
		'rank_attacked_weight' => 'rankAttackedWeight',
		'rank_defended_weight' => 'rankDefendedWeight'
	);

	private $acceptSet = array(
		'name' => 'name',
		'description' => 'description',
		'clan_type' => 'clanType',
		'minimum_trophies' => 'minimumTrophies',
		'war_frequency' => 'warFrequency',
		'members' => 'members',
		'clan_level' => 'clanLevel',
		'clan_points' => 'clanPoints',
		'war_wins' => 'warWins',
		'location' => 'location',
		'access_type' => 'accessType',
		'min_rank_access' => 'minRankAccess',
		'badge_url' => 'badgeUrl',
		'first_attack_weight' => 'firstAttackWeight',
		'second_attack_weight' => 'secondAttackWeight',
		'total_stars_weight' => 'totalStarsWeight',
		'new_stars_weight' => 'newStarsWeight',
		'defence_weight' => 'defenceWeight',
		'number_of_defences_weight' => 'numberOfDefencesWeight',
		'attacks_used_weight' => 'attacksUsedWeight',
		'rank_attacked_weight' => 'rankAttackedWeight',
		'rank_defended_weight' => 'rankDefendedWeight'
	);

	public function create($tag, $name="", $description=null, $clanType='AN', $minimumTrophies=0, $warFrequency='NS'){
		global $db;
		if(!isset($this->id)){
			if(strlen($tag) > 0){
				$tag = correctTag($tag);
				$procedure = buildProcedure('p_clan_create', $name, $tag, $description, $clanType, $minimumTrophies, $warFrequency, date('Y-m-d H:i:s', time()));
				if(($db->multi_query($procedure)) === TRUE){
					$result = $db->store_result()->fetch_object();
					while ($db->more_results()){
						$db->next_result();
					}
					$this->id = $result->id;
					$this->load();
				}else{
					throw new SQLQueryException('The database encountered an error. ' . $db->error);
				}
			}else{
				throw new ArgumentException('Clan tag cannot be blank.');
			}
		}else{
			throw new FunctionCallException('ID set, cannot create.');
		}
	}

	public function __construct($id=null){
		if($id!=null){
			if(is_numeric($id)){
				$this->id = $id;
				$this->load();
			}else{
				$this->tag = $id;
				$this->loadByTag();
			}
		}
	}

	public function load(){
		global $db;
		if(isset($this->id)){
			$procedure = buildProcedure('p_clan_load', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				if ($results->num_rows) {
					$record = $results->fetch_object();
					$results->close();
					$this->id = $record->id;
					$this->name = $record->name;
					$this->tag = $record->tag;
					$this->description = $record->description;
					$this->clanType = $record->clan_type;
					$this->minimumTrophies = $record->minimum_trophies;
					$this->warFrequency = $record->war_frequency;
					$this->dateCreated = $record->date_created;
					$this->dateModified = $record->date_modified;
					$this->apiUpdated = $record->api_updated;
					$this->members = $record->members;
					$this->clanPoints = $record->clan_points;
					$this->clanLevel = $record->clan_level;
					$this->warWins = $record->war_wins;
					$this->badgeUrl = $record->badge_url;
					$this->location = $record->location;
					$this->accessType = $record->access_type;
					$this->minRankAccess = $record->min_rank_access;
					$this->firstAttackWeight = $record->first_attack_weight;
					$this->secondAttackWeight = $record->second_attack_weight;
					$this->totalStarsWeight = $record->total_stars_weight;
					$this->newStarsWeight = $record->new_stars_weight;
					$this->defenceWeight = $record->defence_weight;
					$this->numberOfDefencesWeight = $record->number_of_defences_weight;
					$this->rankAttackedWeight = $record->rank_attacked_weight;
					$this->rankDefendedWeight = $record->rank_defended_weight;
					$this->attacksUsedWeight = $record->attacks_used_weight;
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

	public function loadByTag($tag=null){
		global $db;
		if(isset($this->tag) || $tag != null){
			if($tag == null){
				$tag = $this->tag;
			}
			$tag = correctTag($tag);
			$procedure = buildProcedure('p_clan_load_by_tag', $tag);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				if ($results->num_rows) {
					$record = $results->fetch_object();
					$results->close();
					$this->id = $record->id;
					$this->name = $record->name;
					$this->tag = $record->tag;
					$this->description = $record->description;
					$this->clanType = $record->clan_type;
					$this->minimumTrophies = $record->minimum_trophies;
					$this->warFrequency = $record->war_frequency;
					$this->dateCreated = $record->date_created;
					$this->dateModified = $record->date_modified;
					$this->apiUpdated = $record->api_updated;
					$this->members = $record->members;
					$this->clanPoints = $record->clan_points;
					$this->clanLevel = $record->clan_level;
					$this->warWins = $record->war_wins;
					$this->badgeUrl = $record->badge_url;
					$this->location = $record->location;
					$this->accessType = $record->access_type;
					$this->minRankAccess = $record->min_rank_access;
					$this->firstAttackWeight = $record->first_attack_weight;
					$this->secondAttackWeight = $record->second_attack_weight;
					$this->totalStarsWeight = $record->total_stars_weight;
					$this->newStarsWeight = $record->new_stars_weight;
					$this->defenceWeight = $record->defence_weight;
					$this->numberOfDefencesWeight = $record->number_of_defences_weight;
					$this->rankAttackedWeight = $record->rank_attacked_weight;
					$this->rankDefendedWeight = $record->rank_defended_weight;
					$this->attacksUsedWeight = $record->attacks_used_weight;
				}else{
					throw new NoResultFoundException('No clan found with tag ' . $tag);
				}
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('Tag not set for load.');
		}
	}

	public function loadByObj($clanObj){
		$this->id = $clanObj->id;
		$this->name = $clanObj->name;
		$this->tag = $clanObj->tag;
		$this->description = $clanObj->description;
		$this->clanType = $clanObj->clan_type;
		$this->minimumTrophies = $clanObj->minimum_trophies;
		$this->warFrequency = $clanObj->war_frequency;
		$this->dateCreated = $clanObj->date_created;
		$this->dateModified = $clanObj->date_modified;
		$this->apiUpdated = $record->api_updated;
		$this->members = $clanObj->members;
		$this->clanPoints = $clanObj->clan_points;
		$this->clanLevel = $clanObj->clan_level;
		$this->warWins = $clanObj->war_wins;
		$this->badgeUrl = $clanObj->badge_url;
		$this->location = $clanObj->location;
		$this->accessType = $clanObj->access_type;
		$this->minRankAccess = $clanObj->min_rank_access;
		$this->firstAttackWeight = $clanObj->first_attack_weight;
		$this->secondAttackWeight = $clanObj->second_attack_weight;
		$this->totalStarsWeight = $clanObj->total_stars_weight;
		$this->newStarsWeight = $clanObj->new_stars_weight;
		$this->defenceWeight = $clanObj->defence_weight;
		$this->numberOfDefencesWeight = $clanObj->number_of_defences_weight;
		$this->rankAttackedWeight = $clanObj->rank_attacked_weight;
		$this->rankDefendedWeight = $clanObj->rank_defended_weight;
		$this->attacksUsedWeight = $clanObj->attacks_used_weight;
	}

	public function get($prpty){
		if(isset($this->id)){
			if(in_array($prpty, $this->acceptGet)){
				return $this->$prpty;
			}elseif($prpty == 'pastAndCurrentMembers'){
				return $this->getPastAndCurrentMembers();
			}elseif($prpty == 'currentMembers'){
				return $this->getMembers();
			}else{
				throw new OperationException('Property is not in accept get.');
			}
		}else{
			throw new FunctionCallException('ID not set for get.');
		}
	}

	public function set($prpty, $value){
		global $db;
		if(isset($this->id)){
			if(in_array($prpty, $this->acceptSet)){
				$procedure = buildProcedure('p_clan_set', $this->id, array_search($prpty, $this->acceptSet), $value, date('Y-m-d H:i:s', time()));
				if(($db->multi_query($procedure)) === TRUE){
					while ($db->more_results()){
						$db->next_result();
					}
					$this->$prpty = $value;
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

	public function updateFromApi($clanInfo){
		global $db;
		if(isset($this->id)){
			if($this->name == $clanInfo->name
				&& $this->clanType == convertType($clanInfo->type)
				&& $this->description == $clanInfo->description
				&& $this->warFrequency == convertFrequency($clanInfo->warFrequency)
				&& $this->minimumTrophies == $clanInfo->requiredTrophies
				&& $this->members == $clanInfo->members
				&& $this->clanPoints == $clanInfo->clanPoints
				&& $this->clanLevel == $clanInfo->clanLevel
				&& $this->warWins == $clanInfo->warWins
				&& $this->badgeUrl == $clanInfo->badgeUrls->small
				&& $this->location == convertLocation($clanInfo->location->name)){
				return; //no changes will be made
			}
			$procedure = buildProcedure('p_clan_update_bulk', 
										$this->id,
										$clanInfo->name,
										convertType($clanInfo->type),
										$clanInfo->description,
										convertFrequency($clanInfo->warFrequency),
										$clanInfo->requiredTrophies,
										$clanInfo->members,
										$clanInfo->clanPoints,
										$clanInfo->clanLevel,
										$clanInfo->warWins,
										$clanInfo->badgeUrls->small,
										convertLocation($clanInfo->location->name),
										date('Y-m-d H:i:s', time()),
										date('Y-m-d H:i:s', hourAgo()));
			if(($db->multi_query($procedure)) === TRUE){
				while ($db->more_results()){
					$db->next_result();
				}
				$this->name = $clanInfo->name;
				$this->clanType = convertType($clanInfo->type);
				$this->description = $clanInfo->description;
				$this->warFrequency = convertFrequency($clanInfo->warFrequency);
				$this->minimumTrophies = $clanInfo->requiredTrophies;
				$this->members = $clanInfo->members;
				$this->clanPoints = $clanInfo->clanPoints;
				$this->clanLevel = $clanInfo->clanLevel;
				$this->warWins = $clanInfo->warWins;
				$this->badgeUrl = $clanInfo->badgeUrls->small;
				$this->location = convertLocation($clanInfo->location->name);
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for update.');
		}
	}

	public function addPlayer($player, $rank=4){
		global $db;
		if(isset($this->id)){
			$playerId = $player->get('id');
			$playerClan = $player->getClan();
			if(isset($playerClan) && $playerClan->get('id') != $this->id){
				$player->leaveClan();
			}
			$isNewMember = true;
			$members = $this->getPastAndCurrentMembers();
			foreach ($members as $member) {
				if($member->get('id') == $playerId){
					$isNewMember = false;
					break;
				}
			}
			$procedure = buildProcedure('p_clan_add_player', $this->id, $playerId, $rank, date('Y-m-d H:i:s', time()));
			if(($db->multi_query($procedure)) === TRUE){
				while ($db->more_results()){
					$db->next_result();
				}
				if($isNewMember){
					$this->pastAndCurrentMembers[] = $player;
					$this->currentMembers[] = $player;
					$this->highestWarRank = $this->getHighestWarRank() + 1;
					$this->updatePlayerWarRank($playerId, $this->highestWarRank);
				}
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for add.');
		}
	}

	public function getMembers($force=false, $sort=null){
		global $db;
		if(isset($sort)){
			$sorts = array(
				'name_desc' => 'name desc',
				'trophies_desc' => 'trophies desc',
				'donations_desc' => 'donations desc',
				'received_desc' => 'received desc',
				'rank_desc' => 'rank desc',
				'name' => 'name',
				'trophies' => 'trophies',
				'donations' => 'donations',
				'received' => 'received',
				'rank' => 'rank');
			$sort = $sorts[$sort];
		}
		if(!isset($sort)){
			$sort = 'trophies desc';
		}
		if(isset($this->id)){
			if(isset($this->currentMembers) && !$force){
				return $this->currentMembers;
			}
			$procedure = buildProcedure('p_clan_get_current_members', $this->id, $sort);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				$this->currentMembers = array();
				if ($results->num_rows) {
					while ($memberObj = $results->fetch_object()) {
						$member = new Player();
						$member->loadByObj($memberObj, $this);
						$this->currentMembers[$member->get('id')] = $member;
					}
				}
				return $this->currentMembers;
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for get.');
		}
	}

	public function getPastAndCurrentMembers(){
		global $db;
		if(isset($this->id)){
			if(isset($this->pastAndCurrentMembers)){
				return $this->pastAndCurrentMembers;
			}
			$procedure = buildProcedure('p_clan_get_past_and_current_members', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				$this->pastAndCurrentMembers = array();
				if ($results->num_rows) {
					while ($memberObj = $results->fetch_object()) {
						$member = new Player();
						$member->loadByObj($memberObj);
						$this->pastAndCurrentMembers[] = $member;
					}
				}
				return $this->pastAndCurrentMembers;
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for get.');
		}
	}

	public function getWars(){
		global $db;
		if(isset($this->id)){
			if(isset($this->wars)){
				return $this->wars;
			}
			$procedure = buildProcedure('p_clan_get_wars', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				$this->wars = array();
				if ($results->num_rows) {
					while ($warObj = $results->fetch_object()) {
						$war = new War();
						$war->loadByObj($warObj);
						$this->wars[] = $war;
					}
				}
				return $this->wars;
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for wars.');
		}
	}

	public static function getClans($sort=null, $pageSize=50){
		global $db;
		if(isset($sort)){
			$sorts = array(
				'name_desc' => 'name desc',
				'clan_points_desc' => 'clan_points desc',
				'war_wins_desc' => 'war_wins desc',
				'members_desc' => 'members desc',
				'minimum_trophies_desc' => 'minimum_trophies desc',
				'name' => 'name',
				'clan_points' => 'clan_points',
				'war_wins' => 'war_wins',
				'members' => 'members',
				'minimum_trophies' => 'minimum_trophies');
			$sort = $sorts[$sort];
		}
		if(!isset($sort)){
			$sort = 'clan_points desc';
		}
		$procedure = buildProcedure('p_get_clans', $sort, $pageSize);
		if(($db->multi_query($procedure)) === TRUE){
			$results = $db->store_result();
			while ($db->more_results()){
				$db->next_result();
			}
			$clans = array();
			if ($results->num_rows) {
				while ($clanObj = $results->fetch_object()) {
					$clan = new Clan();
					$clan->loadByObj($clanObj);
					$clans[] = $clan;
				}
			}
			return $clans;
		}else{
			throw new SQLQueryException('The database encountered an error. ' . $db->error);
		}
	}

	public function isPlayerInClan($player){
		try{
			if(is_numeric($player)){
				$player = new player($player);
			}
			$player->get('id');
		}catch(Exception $e){
			return false;
		}
		$clan = $player->getClan();
		if(isset($clan)){
			return $clan->get('id') == $this->id;
		}else{
			return false;
		}
	}

	public function updatePlayerWarRank($playerId, $warRank){
		if(isset($this->id)){
			global $db;
			$procedure = buildProcedure('p_clan_update_player_war_rank', $this->id, $playerId, $warRank, date('Y-m-d H:i:s', time()));
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

	public function getHighestWarRank(){
		if(isset($this->id)){
			if(isset($this->highestWarRank)){
				return $this->highestWarRank;
			}
			global $db;
			$procedure = buildProcedure('p_clan_get_highest_war_rank', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				$this->highestWarRank = 0;
				if ($results->num_rows) {
					$record = $results->fetch_object();
					$results->close();
					$this->highestWarRank = $record->war_rank;
				}
				return $this->highestWarRank;
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for war ranks.');
		}
	}

	public function playerJoined($playerId){
		if(isset($this->id)){
			global $db;
			$procedure = buildProcedure('p_clan_player_joined', $this->id, $playerId);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				$joined = null;
				if ($results->num_rows) {
					$record = $results->fetch_object();
					$results->close();
					$joined = $record->date_joined;
				}
				return $joined;
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for war ranks.');
		}
	}

	public static function searchClans($query, $warFrequency=null, $minMembers=null, $maxMembers=null, $minClanLevel=null, $minClanPoints=null){
		global $db;
		$query = trim($query);
		$queries = explode(' ', $query);
		$query = str_replace(' ', '%', $query);
		array_unshift($queries, $query);
		$queries = array_unique($queries);
		$clans = array();
		$where = '';
		if(!is_null($warFrequency)) 	$where .= " and war_frequency = '" . $db->escape_string(convertFrequency($warFrequency)) . "'";
		if(!is_null($minMembers)) 		$where .= " and members >= '" . $db->escape_string($minMembers) . "'";
		if(!is_null($maxMembers)) 		$where .= " and members <= '" . $db->escape_string($maxMembers) . "'";
		if(!is_null($minClanLevel)) 	$where .= " and clan_level >= '" . $db->escape_string($minClanLevel) . "'";
		if(!is_null($minClanPoints)) 	$where .= " and clan_points >= '" . $db->escape_string($minClanPoints) . "'";
		foreach ($queries as $query) {
			if(strlen($query)>1 || count($queries)==1){
				$query = $db->escape_string($query);
				$procedure = "select * from clan where (lower(name) like lower('%".$query."%') or lower(tag) like lower('%".$query."%'))" . $where . ' limit 50;';
				error_log($procedure);
				if(($db->multi_query($procedure)) === TRUE){
					$results = $db->store_result();
					while ($db->more_results()){
						$db->next_result();
					}
					if ($results->num_rows) {
						while ($clanObj = $results->fetch_object()) {
							$clan = new Clan();
							$clan->loadByObj($clanObj);
							$clans[] = $clan;
						}
					}
				}else{
					throw new SQLQueryException('The database encountered an error. ' . $db->error);
				}
			}
		}
		$foundIds = array();
		foreach ($clans as $i => $clan) {
			if(in_array($clan->get('id'), $foundIds)){
				unset($clans[$i]);
			}else{
				$foundIds[] = $clan->get('id');
			}
		}
		$clans = array_splice($clans, 0, 50);
		return $clans;
	}

	public function delete(){
		if(isset($this->id)){
			$wars = $this->getWars();
			$members = $this->getPastAndCurrentMembers();
			if(count($wars) + count($members)==0){
				global $db;
				$procedure = buildProcedure('p_clan_delete', $this->id);
				if(($db->multi_query($procedure)) === TRUE){
					while ($db->more_results()){
						$db->next_result();
					}
				}else{
					throw new SQLQueryException('The database encountered an error. ' . $db->error);
				}
			}
		}else{
			throw new FunctionCallException('ID not set for delete.');
		}
	}

	public function revokeAllAccess(){
		global $db;
		if(isset($this->id)){
			$procedure = buildProcedure('p_clan_disallow_all_users', $this->id);
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
		$this->set('accessType', 'AN');
		$this->set('minRankAccess', null);
	}

	public function getAllowedUsers(){
		global $db;
		if(isset($this->id)){
			if($this->accessType == 'US'){
				$procedure = buildProcedure('p_clan_get_allowed_users', $this->id);
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
			}elseif($this->accessType == 'CL'){
				$clanMembers = $this->getMembers();
				$users = array();
				foreach ($clanMembers as $member) {
					$clanRank = $member->getClanRank();
					if($clanRank <= $this->minRankAccess){
						$user = $member->getLinkedUser();
						if(isset($user)){
							$users[] = $user;
						}
					}
				}
				return $users;
			}else{
				return array();
			}
		}else{
			throw new FunctionCallException('ID not set for get.');
		}
	}

	public function grantUserAccess($user){
		global $db;
		if(isset($this->id)){
			$procedure = buildProcedure('p_clan_allow_user', $this->id, $user->get('id'));
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
			$procedure = buildProcedure('p_clan_disallow_user', $this->id, $user->get('id'));
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

	public function getLinkedUser(){
		global $db;
		if(isset($this->id)){
			$procedure = buildProcedure('p_clan_get_linked_user', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				$result = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				if ($result->num_rows){
					$userObj = $result->fetch_object();
					return new User($userObj->id);
				}else{
					return null;
				}
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for getting linked player.');
		}
	}

public function getPlayersAvailableForLootReport($type){
		global $db;
		if(isset($this->id)){
			$startDate = date('Y-m-d H:i:s', strtotime('-8 days'));
			$endDate = date('Y-m-d H:i:s', strtotime('-5 days'));
			$procedure = buildProcedure('p_clan_players_for_loot_report', $this->id, $type, $startDate, $endDate);
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
						$players[] = $player;
					}
				}
				return $players;
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for getting players available for loot report.');
		}
	}

	public function getPlayersAvailableForGoldReport(){
		return $this->getPlayersAvailableForLootReport('GO');
	}

	public function getPlayersAvailableForElixirReport(){
		return $this->getPlayersAvailableForLootReport('EL');
	}

	public function getPlayersAvailableForDarkElixirReport(){
		return $this->getPlayersAvailableForLootReport('DE');
	}

	public function canGenerateLootReport(){
		$lootReports = $this->getLootReports();
		if(count($lootReports)>0){
			$lootReport = $lootReports[0];
			$date = strtotime($lootReport->get('dateCreated'));
			if($date > strtotime('-3 days')){
				return false;
			}
		}
		$playersAvailableForLootReport = $this->getPlayersAvailableForGoldReport();
		$playersAvailableForLootReport = array_merge($playersAvailableForLootReport, $this->getPlayersAvailableForElixirReport());
		$playersAvailableForLootReport = array_merge($playersAvailableForLootReport, $this->getPlayersAvailableForDarkElixirReport());
		return count($playersAvailableForLootReport)>0;
	}

	public function generateLootReport(){
		if(isset($this->id)){
			$lootReport = new LootReport();
			$lootReport->create($this);
			return $lootReport;
		}else{
			throw new FunctionCallException('ID not set for generating loot report.');
		}
	}

	public function getLootReports(){
		global $db;
		if(isset($this->id)){
			if(isset($this->lootReports)){
				return $this->lootReports;
			}
			$procedure = buildProcedure('p_clan_get_loot_reports', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				$this->lootReports = array();
				if ($results->num_rows) {
					while ($lootReportObj = $results->fetch_object()) {
						$lootReport = new LootReport();
						$lootReport->loadByObj($lootReportObj);
						$this->lootReports[] = $lootReport;
					}
				}
				return $this->lootReports;
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set to getloot Reports.');
		}
	}

	public function canRequestAccess(){
		global $loggedInUser;
		return $this->accessType == 'US' && isset($loggedInUser) && !userHasAccessToUpdateClan($this) && !$this->userHasRequested($loggedInUser);
	}

	public function requestAccess($user, $message=""){
		global $db;
		if(isset($this->id)){
			$procedure = buildProcedure('p_clan_edit_request_create', $this->id, $user->get('id'), $message);
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
			$procedure = buildProcedure('p_clan_edit_request_delete', $this->id, $user->get('id'));
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
			$procedure = buildProcedure('p_clan_get_edit_requests', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				$editRequests = array();
				if ($results->num_rows) {
					while ($editRequestObj = $results->fetch_object()) {
						$editRequest = new stdClass();
						$editRequest->clan = $this;
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

	public function userHasRequested($user){
		$requests = $this->getRequests();
		if(!isset($user)){
			return false;
		}
		$userId = $user->get('id');
		foreach ($requests as $request) {
			if($request->user->get('id') == $userId){
				return true;
			}
		}
		return false;
	}
}