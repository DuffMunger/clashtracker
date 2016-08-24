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
}