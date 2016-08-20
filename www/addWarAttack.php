<?
require('init.php');
require('session.php');

$warId = $_GET['warId'];
try{
	$war = new war($warId);
	$warId = $war->get('id');
}catch(Exception $e){
	$_SESSION['curError'] = 'No war with id ' . $warId . ' found.';
	header('Location: /wars.php');
	exit;
}

$clanId = $_GET['clanId'];
if($war->isClanInWar($clanId)){
	$clan1 = new clan($clanId);
	$clanId = $clan1->get('id');
	$clan2 = $war->getEnemy($clanId);
}else{
	$clanId = null;
	$clan1 = $war->get('clan1');
	$clan2 = $war->get('clan2');
}

if(!$war->isBattleDay()){
	$_SESSION['curError'] = 'Adding war attacks is only available during battle day.';
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
		exit;
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
		exit;
	}
}

if(!userHasAccessToUpdateWar($war)){
	$_SESSION['curError'] = NO_ACCESS;
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
		exit;
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
		exit;
	}
}

$attackerId = $_GET['playerId'];
try{
	$attacker = new player($attackerId);
	$attackerId = $attacker->get('id');
}catch(Exception $e){
	$_SESSION['curError'] = 'No player with id ' . $attackerId . ' found.';
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
	}
	exit;
}

try{
	$attackerClan = $war->getPlayerWarClan($attacker->get('id'));
}catch(WarPlayerException $e){
	$_SESSION['curError'] = 'Player not in war.';
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
	}
	exit;
}

$defenderClan = $war->getEnemy($attackerClan->get('id'));
$defenders = $war->getPlayers($defenderClan);
if(count($defenders) == 0){
	$_SESSION['curError'] = 'No members in opposite clan to attack.';
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
	}
	exit;
}

$attackerAttacks = $war->getPlayerAttacks($attacker);
if(count($attackerAttacks) >= 2){
	$_SESSION['curError'] = 'Attacker has already used both attacks.';
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
	}
	exit;
}

if(isset($attackerAttacks[0])){
	foreach ($defenders as $rank => $defender) {
		if($attackerAttacks[0]['defenderId'] == $defender->get('id')){
			unset($defenders[$rank]);
		}
	}
}
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
		<li class="active">Add War Attack</li>
	</ol>
	<?require('showMessages.php');?>
	<h1>Add War Attack</h1><br>
	<div class="">
		<form class="form-horizontal" action="/processAddWarAttack.php" method="POST">
			<input hidden name="warId" value="<?=$war->get('id');?>">
			<input hidden name="playerId" value="<?=$attacker->get('id');?>">
			<?if(isset($clanId)){?>
				<input hidden name="clanId" value="<?=$clan1->get('id');?>">
			<?}?>
			<div class="col-md-12">
				<div class="col-md-6">
					<h4>Select Defender:</h4><br>
					<table class="table table-hover">
						<tbody>
							<?foreach ($defenders as $rank => $defender) {?>
								<tr style="cursor: pointer;">
									<td onclick="selectMember(<?=$defender->get('id');?>);">
										<div class="checkbox">
											<label>
												<input class="defender" data-stars-available="<?=$defenderStarsAvailable[$defender->get('id')];?>" id="<?=$defender->get('id');?>" type="checkbox" name="defenderId" value="<?=$defender->get('id');?>"><?=($rank+1) . ". " . htmlspecialchars($defender->get('name'));?>
											</label>
										</div>
									</td>
								</tr>
							<?}?>
						</tbody>
					</table>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-sm-4 control-lable text-right" for="stars">Stars:</label>
						<div class="col-sm-8">
							<?for ($i=0; $i <= 3; $i++){?>
								<div class="col-sm-3">
									<input id="<?=$i;?>stars" onclick="selectStars(<?=$i;?>);" name="stars" value="<?=$i;?>" class="stars" type="checkbox">
									&nbsp;<?=$i;?>&nbsp;<i class="fa fa-star"></i>
								</div>
							<?}?>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12 text-right btn-actions">
							<br>
							<button type="submit" class="btn btn-default" name="cancel" value="cancel">Cancel</button>
							<button type="submit" class="btn btn-success" name="submit" value="submit">Submit</button>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
function selectMember(id){
	$(".defender").each(function(){
		$(this).attr('checked', false);
	});
	var defender = $('#' + id);
	if(defender.is(':checked')){
		defender.prop('checked', false);
	}else{
		defender.prop('checked', true);
	}
}
function selectStars(stars){
	$(".stars").each(function(){
		$(this).attr('checked', false);
	});
	$('#' + stars + 'stars').prop('checked', true);
}
</script>
<?
require('footer.php');