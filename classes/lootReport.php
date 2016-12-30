<?
class LootReport{
	private $id;
	private $clanId;
	private $dateCreated;

	private $acceptGet = array(
		'id' => 'id',
		'clan_id' => 'clanId',
		'date_created' => 'dateCreated'
	);

	public function create($clan){
		global $db;
		if(!isset($this->id)){
			$clanId = $clan->get('id');
			if($clan->canGenerateLootReport()){
				$date = date('Y-m-d H:i:s', time());
				$procedure = buildProcedure('p_loot_report_create', $clanId, $date);
				if(($db->multi_query($procedure)) === TRUE){
					$result = $db->store_result()->fetch_object();
					while ($db->more_results()){
						$db->next_result();
					}
					$this->id = $result->id;
					$this->clanId = $result->clan_id;
					$this->dateCreated = $result->date_created;
					try{
						$this->updatePlayerLootStats();
						$this->generate();
					}catch(Exception $e){
						$this->delete();
						throw $e;
					}
				}else{
					throw new SQLQueryException('The database encountered an error. ' . $db->error);
				}
			}else{
				throw new ArgumentException('Clan must be able to generate loot report.');
			}
		}else{
			throw new FunctionCallException('ID set, cannot create.');
		}
	}

	public function clan(){
		if(isset($this->clan)){
			return $this->clan;
		}
		$this->clan = new clan($this->clanId);
		return $this->clan;
	}

	private function updatePlayerLootStats(){
		$players = $this->clan()->getMembers();
		foreach ($players as $player) {
			refreshPlayerInfo($player);
		}
	}

	private function generate(){
		if(isset($this->id)){
			$sinceTime = date('Y-m-d H:i:s', strtotime('-8 days'));
			$types = array('GO', 'EL', 'DE');
			foreach ($types as $type) {
				$players = $this->clan()->getPlayersAvailableForLootReport($type);
				foreach ($players as $player) {
					$loot = $player->getStat($type, $sinceTime);
					$amount = $loot[0]['statAmount'] - $loot[count($loot)-1]['statAmount'];
					$this->recordPlayerResult($player, $type, $amount);
				}
			}
			$this->load();
		}else{
			throw new FunctionCallException('ID not set, cannot generate.');
		}
	}

	public function recordPlayerResult($player, $type, $amount){
		global $db;
		if(isset($this->id)){
			$procedure = buildProcedure('p_loot_report_record_player_result', $player->get('id'), $this->id, $type, $amount);
			if(($db->multi_query($procedure)) === TRUE){
				while ($db->more_results()){
					$db->next_result();
				}
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set, cannot generate.');
		}
	}

	public function __construct($id=null){
		if(isset($id)){
			$this->id = $id;
			$this->load();
		}
	}

	private function load(){
		global $db;
		if(isset($this->id)){
			$procedure = buildProcedure('p_loot_report_load', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				if ($results->num_rows) {
					$record = $results->fetch_object();
					$results->close();
					$this->id = $record->id;
					$this->clanId = $record->clan_id;
					$this->dateCreated = $record->date_created;
					$this->getResults();
				}else{
					throw new NoResultFoundException('No loot report found with id ' . $this->id);
				}
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for load.');
		}
	}

	public function loadByObj($lootReportObj){
		$this->id = $lootReportObj->id;
		$this->clanId = $lootReportObj->clan_id;
		$this->dateCreated = $lootReportObj->date_created;
	}

	private function getResults(){
		global $db;
		if(isset($this->id)){
			if(isset($this->results)){
				return $this->results;
			}
			$procedure = buildProcedure('p_loot_report_get_results', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				$results = $db->store_result();
				while ($db->more_results()){
					$db->next_result();
				}
				$this->results = array();
				$loadedPlayers = array();
				if ($results->num_rows) {
					while ($resultObj = $results->fetch_object()) {
						$lootType = $resultObj->loot_type;
						if(!isset($this->results[$lootType])){
							$this->results[$lootType] = array();
						}
						$result = array();
						if(isset($loadedPlayers[$resultObj->id])){
							$player = $loadedPlayers[$resultObj->id];
						}else{
							$player = new Player();
							$player->loadByObj($resultObj);
							$loadedPlayers[$resultObj->id] = $player;
						}
						$result['player'] = $player;
						$result['amount'] = $resultObj->loot_amount;
						$this->results[$lootType][] = $result;
					}
				}
				return $this->results;
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for results.');
		}
	}

	public function get($prpty){
		if(isset($this->id)){
			if(in_array($prpty, $this->acceptGet)){
				return $this->$prpty;
			}elseif($prpty == 'clan'){
				return $this->clan();
			}elseif($prpty == 'results'){
				return $this->getResults();
			}else{
				throw new OperationException('Property is not in accept get.');
			}
		}else{
			throw new FunctionCallException('ID not set for get.');
		}
	}

	public function delete(){
		if(isset($this->id)){
			global $db;
			$procedure = buildProcedure('p_loot_report_delete', $this->id);
			if(($db->multi_query($procedure)) === TRUE){
				while ($db->more_results()){
					$db->next_result();
				}
			}else{
				throw new SQLQueryException('The database encountered an error. ' . $db->error);
			}
		}else{
			throw new FunctionCallException('ID not set for delete.');
		}
	}
}