<?
require('init.php');
require('session.php');

$warId = $_GET['warId'];
try{
	$war = new War($warId);
	$warId = $war->get('id');
}catch(Exception $e){
	$_SESSION['curError'] = "No war with id $warId found.";
	header('Location: /wars.php');
	exit;
}

$clanId = $_GET['clanId'];
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

$playerId = $_GET['playerId'];
try{
	$player = new Player($playerId);
	$playerId = $player->get('id');
}catch(Exception $e){
	$_SESSION['curError'] = "No player with id $playerId found.";
	header("Location: /war.php?warId=$warId$clanIdText");
	exit;
}

$assignedPlayerId = $_GET['assignedPlayerId'];
try{
	$assignedPlayer = new Player($assignedPlayerId);
	$assignedPlayerId = $assignedPlayer->get('id');
}catch(Exception $e){
	$_SESSION['curError'] = "No player with id $assignedPlayerId found.";
	header("Location: /war.php?warId=$warId$clanIdText");
	exit;
}

$assignments = $war->getAssignments($playerId);
$assignment = null;
foreach ($assignments as $assignment) {
	if($assignedPlayerId == $assignment->get('assignedPlayerId')){
		break;
	}
}

if(is_null($assignment)){
	$_SESSION['curError'] = htmlspecialchars($player->get('name')) . ' hasn\'t been assigned to attack ' . htmlspecialchars($assignedPlayer->get('name')) . '.';
	header("Location: /war.php?warId=$warId$clanIdText");
	exit;
}

$canEdit = userHasAccessToUpdateClan($war->get('clan1'));
require('header.php');
?>
<div class="col-md-12">
	<ol class="breadcrumb">
		<li><a href="/home.php">Home</a></li>
		<?if(isset($clanId)){?>
			<li><a href="/clans.php">Clans</a></li>
			<li><a href="/clan.php?clanId=<?=$clan1->get('id');?>"><?=htmlspecialchars($clan1->get('name'));?></a></li>
			<li><a href="/wars.php?clanId=<?=$clan1->get('id');?>">Wars</a></li>
			<li><a href="/war.php?warId=<?=$war->get('id');?>&clanId=<?=$clan1->get('id');?>"><?=htmlspecialchars($clan1->get('name'));?> vs. <?=htmlspecialchars($clan2->get('name'));?></a></li>
		<?}else{?>
			<li><a href="/wars.php">Wars</a></li>
			<li><a href="/war.php?warId=<?=$war->get('id');?>"><?=htmlspecialchars($clan1->get('name'));?> vs. <?=htmlspecialchars($clan2->get('name'));?></a></li>
		<?}?>
		<li class="active"><?=htmlspecialchars($player->get('name'));?>'s Assignment</li>
	</ol>
	<?require('showMessages.php');?>
	<h1><?=htmlspecialchars($player->get('name'));?>'s Assignment</h1><br>
	<form class="form-horizontal" action="/processEditWarAssignment.php" method="POST">
		<input hidden name="warId" value="<?=$war->get('id');?>">
		<input hidden name="playerId" value="<?=$playerId;?>">
		<input hidden name="assignedPlayerId" value="<?=$assignedPlayerId;?>">
		<?if(isset($clanId)){?>
			<input hidden name="clanId" value="<?=$clanId;?>">
		<?}?>
		<div class="col-md-12">
			<div class="col-md-6">
				<h4><?=htmlspecialchars($player->get('name')) . ' is assigned to attacked ' . htmlspecialchars($assignedPlayer->get('name'));?></h4>
				<div class="form-group">
					<?if($war->isBattleDay() && $canEdit){?>
						<label class="col-sm-4 control-lable" for="stars">Stars:</label>
						<div class="col-sm-8">
							<?for ($i=0; $i <= 3; $i++){?>
								<div class="col-sm-3">
									<input id="<?=$i;?>stars" onclick="selectStars(<?=$i;?>);" name="stars" value="<?=$i;?>" class="stars" type="checkbox">
									&nbsp;<?=$i;?>&nbsp;<i class="fa fa-star"></i>
								</div>
							<?}?>
						</div>
					<?}?>
				</div>
			</div>
			<div class="col-md-6">
				<h4>Message:</h4><br>
				<div class="breadcrumb">
					<p id="message"><?=$assignment->get('message');?></p>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 text-right btn-actions">
				<br>
				<button type="submit" class="btn btn-default" name="cancel" value="cancel">Back</button>
				<?if($war->isEditable() && $canEdit){?>
					<button type="submit" class="btn btn-danger" name="delete" value="delete">Delete</button>
				<?}if($war->isBattleDay() && $canEdit){?>
					<button type="submit" class="btn btn-success" name="delete" value="delete">Record Result</button>
				<?}?>
			</div>
		</div>
	</form>
</div>
<script type="text/javascript">
function selectStars(stars){
	$(".stars").each(function(){
		$(this).attr('checked', false);
	});
	$('#' + stars + 'stars').prop('checked', true);
}
</script>
<?
require('footer.php');