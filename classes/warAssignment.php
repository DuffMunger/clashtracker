<?
class WarAssignment{
	private $warId;
	private $playerId;
	private $assignedPlayerId;
	private $message;
	private $dateCreated;
	private $dateModified;

	private $acceptGet = array(
		'war_id' => 'warId',
		'player_id' => 'player_id',
		'assigned_player_id' => 'assignedPlayerId',
		'message' => 'message',
		'date_created' => 'dateCreated',
		'date_modified' => 'dateModified'
	);

	private $acceptSet = array(
		'assigned_player_id' => 'assignedPlayerId',
		'message' => 'message'
	);

	public function create($war, $player, $assignedPlayer, $message=''){
		if(isset($this->warId)){
			throw new FunctionCallException('ID set, cannot create.');
		}
		$warId = $war->get('id');
		$playerId = $player->get('id');
		$assignedPlayerId = $assignedPlayer->get('id');
		if(!is_string($message)){
			throw new ArgumentException('Message must be a string.');
		}
		global $db;
		$procedure = buildProcedure('p_war_assignment_create', $warId, $playerId, $assignedPlayerId, $message, date('Y-m-d H:i:s', time()));
		if(($db->multi_query($procedure)) !== TRUE){
			throw new SQLQueryException('The database encountered an error. ' . $db->error);
		}
		$result = $db->store_result()->fetch_object();
		while ($db->more_results()){
			$db->next_result();
		}
		$this->loadByObj($result);
	}

	public function __construct($obj=null){
		if(isset($obj)){
			$this->loadByObj($obj);
		}
	}

	public function loadByObj($assignmentObj){
		$this->warId = $assignmentObj->war_id;
		$this->playerId = $assignmentObj->player_id;
		$this->assignedPlayerId = $assignmentObj->assigned_player_id;
		$this->message = $assignmentObj->message;
		$this->dateCreated = $assignmentObj->date_created;
		$this->dateModified = $assignmentObj->date_modified;
	}

	public function get($prpty){
		if(!isset($this->warId)){
			throw new FunctionCallException('ID not set for get.');
		}
		if(in_array($prpty, $this->acceptGet)){
			return $this->prpty;
		}elseif($prpty == 'war'){
			return $this->getWar();
		}elseif($prpty == 'player'){
			return $this->getPlayer();
		}elseif($prpty == 'assignedPlayer'){
			return $this->getAssignedPlayer();
		}
		throw new OperationException('Property is not in accept get.');
	}

	private function getWar(){
		if(isset($this->war)){
			return $this->war;
		}
		$this->war = new War($this->warId);
		return $this->war;
	}

	private function getPlayer(){
		if(isset($this->player)){
			return $this->player;
		}
		$this->player = new Player($this->playerId);
		return $this->player;
	}

	private function getAssignedPlayer(){
		if(isset($this->assignedPlayer)){
			return $this->assignedPlayer;
		}
		$this->assignedPlayer = new Player($this->assignedPlayerId);
		return $this->assignedPlayer;
	}

	public function set($prpty, $value){
		if(!isset($this->warId)){
			throw new FunctionCallException('ID not set for set.');
		}
		if(!in_array($prpty, $this->acceptSet)){
			throw new OperationException('Property not in accept set.');
		}
		$procedure = buildProcedure('p_war_assignment_set', $this->warId, $this->playerId, $this->assignedPlayerId, array_search($prpty, $this->acceptSet), $value, date('Y-m-d H:i:s', time()));
		if(($db->multi_query($procedure)) !== TRUE){
			throw new SQLQueryException('The database encountered an error. ' . $db->error);
		}
		while ($db->more_results()){
			$db->next_result();
		}
		$this->$prpty = $value;
	}

	public function delete(){
		if(!isset($this->warId)){
			throw new FunctionCallException('ID not set for delete.');
		}
		$procedure = buildProcedure('p_war_assignment_delete', $this->warId, $this->playerId, $this->assignedPlayedId);
		if(($db->multi_query($procedure)) === TRUE){
			throw new SQLQueryException('The database encountered an error. ' . $db->error);
		}
		while ($db->more_results()){
			$db->next_result();
		}
	}

	public function convertToWarAttack($stars){
		if($stars > 3 || $stars < 0){
			throw new ArgumentException('Attack stars must be between 0 and 3.');
		}
		$war = $this->get('war');
		$war->addAttack($this->playerId, $this->assignedPlayerId, $stars);
		$this->delete();
	}
}