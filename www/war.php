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
$clanIdText = (isset($clanId)) ? "&clanId=$clanId" : '';

$clan1 = $war->get('clan1');
$clan2 = $war->get('clan2');
if($clan2->get('id') == $clanId){
	$temp = $clan1;
	$clan1 = $clan2;
	$clan2 = $temp;
}

$clan1Players = $war->getPlayers($clan1);
$clan2Players = $war->getPlayers($clan2);

$warPlayers = array();
foreach (array_merge($clan1Players, $clan2Players) as $player) {
	$warPlayers[$player->get('id')] = $player;
}

$clan1CanAddMore = count($clan1Players) < $war->get('size');
$clan2CanAddMore = count($clan2Players) < $war->get('size');

$warAttacks = array_reverse($war->getAttacks());

$clan1Stars = $war->getClanStars($clan1, true);
$clan2Stars = $war->getClanStars($clan2, true);

$clan1Attacks = $war->getAttacks($clan1);
$clan2Attacks = $war->getAttacks($clan2);

$clan1AttacksUsed = count($clan1Attacks);
$clan2AttacksUsed = count($clan2Attacks);

$clan1AttacksLeft = $war->get('size')*2 - $clan1AttacksUsed;
$clan2AttacksLeft = $war->get('size')*2 - $clan2AttacksUsed;

$clan1AttacksWon = 0;
$clan1AttacksLost = 0;
foreach ($clan1Attacks as $attack) {
	if($attack['totalStars'] > 0){
		$clan1AttacksWon++;
	}else{
		$clan1AttacksLost++;
	}
}

$clan2AttacksWon = 0;
$clan2AttacksLost = 0;
foreach ($clan2Attacks as $attack) {
	if($attack['totalStars'] > 0){
		$clan2AttacksWon++;
	}else{
		$clan2AttacksLost++;
	}
}

$clan2BestAttacks = array(3=>0, 2=>0, 1=>0, 0=>0);
foreach ($clan1Players as $defender) {
	$defences = getPlayerAttacks($defender->get('id'), 'defence');
	$starsAgainst = 0;
	foreach ($defences as $defence) {
		if($defence['totalStars'] > $starsAgainst){
			$starsAgainst = $defence['totalStars'];
		}
	}
	$clan2BestAttacks[$starsAgainst]++;
}

$clan1BestAttacks = array(3=>0, 2=>0, 1=>0, 0=>0);
foreach ($clan2Players as $defender) {
	$defences = getPlayerAttacks($defender->get('id'), 'defence');
	$starsAgainst = 0;
	foreach ($defences as $defence) {
		if($defence['totalStars'] > $starsAgainst){
			$starsAgainst = $defence['totalStars'];
		}
	}
	$clan1BestAttacks[$starsAgainst]++;
}

function getPlayerAttacks($playerId, $type='attack'){
	global $warAttacks;
	$warAttacks = array_reverse($warAttacks);
	$attacks = array();
	foreach ($warAttacks as $attack) {
		if($type == 'attack' && $attack['attackerId'] == $playerId){
			$attacks[] = $attack;
		}
		if($type == 'defence' && $attack['defenderId'] == $playerId){
			$attacks[] = $attack;
		}
	}
	return $attacks;
}

$isEditable = $war->isEditable();
$userCanEdit = $isEditable && userHasAccessToUpdateWar($war);

$requests = array();
$allowedUsers = array();
$canRequestAccess = false;
if($isEditable && userHasAccessToUpdateClan($war->get('clan1'))){
	$requests = $war->getRequests();
	$allowedUsers = $war->getAllowedUsers();
}elseif($isEditable && userHasAccessToUpdateClan($war->get('clan2')) && !$userCanEdit && isset($loggedInUser) && !$war->userHasRequested($loggedInUser->get('id'))){
	$canRequestAccess = true;
}

if(isset($clanId)){
	$clanMessage = $war->getMessage($clanId);
	$userCanEditClanMessage = $isEditable && userHasAccessToUpdateClan(new Clan($clanId));
}

require('header.php');
?>
<div class="col-md-12">
	<ol class="breadcrumb">
		<li><a href="/home.php">Home</a></li>
		<?if(isset($clanId)){?>
			<li><a href="/clans.php">Clans</a></li>
			<li><a href="/clan.php?clanId=<?=$clan1->get('id');?>"><?=displayName($clan1->get('name'));?></a></li>
			<li><a href="/wars.php?clanId=<?=$clan1->get('id');?>">Wars</a></li>
		<?}else{?>
			<li><a href="/wars.php">Wars</a></li>
		<?}?>
		<li class="active"><?=displayName($clan1->get('name'));?> vs. <?=displayName($clan2->get('name'));?></li>
	</ol>
	<?require('showMessages.php');?>
	<div class="visible-lg-block">
		<div class="col-sm-6 text-center">
			<h1 style="cursor: pointer;" onclick="clickRow('clan.php?clanId=<?=$clan1->get("id");?>');">
				<?=displayName($clan1->get('name'));?>
			</h1>
		</div>
		<div class="col-sm-6 text-center">
			<h1 style="cursor: pointer;" onclick="clickRow('clan.php?clanId=<?=$clan2->get("id");?>');">
				<?=displayName($clan2->get('name'));?>
			</h1>
		</div>
	</div>
	<div class="col-sm-12 text-center"><h2><i class="fa fa-star" style="color: gold;"></i> <?=$clan1Stars;?> - <?=$clan2Stars;?> <i class="fa fa-star" style="color: gold;"></i></h2></div>
	<?if(!$isEditable){?>
		<div class="col-md-6">
			<h2 class="hidden-lg" style="cursor: pointer;" onclick="clickRow('clan.php?clanId=<?=$clan1->get("id");?>');">
				<?=displayName($clan1->get('name'));?>
			</h2>
			<div class="panel panel-primary">
				<div class="panel-heading text-center">
					<h3 class=" panel-title">Attack Totals</h3>
				</div>
				<div class="panel-body">
					<p class="col-sm-6 text-center">Attacks Used</p>
					<p class="col-sm-6 text-center"><?=$clan1AttacksUsed;?></p>
					<br>
					<p class="col-sm-6 text-center">Attacks Won</p>
					<p class="col-sm-6 text-center"><?=$clan1AttacksWon;?></p>
					<br>
					<p class="col-sm-6 text-center">Attacks Lost</p>
					<p class="col-sm-6 text-center"><?=$clan1AttacksLost;?></p>
					<br>
					<p class="col-sm-6 text-center">Attacks Remaining</p>
					<p class="col-sm-6 text-center"><?=$clan1AttacksLeft;?></p>
				</div>
			</div>
			<div class="panel panel-primary">
				<div class="panel-heading text-center">
					<h3 class=" panel-title">Best Attacks</h3>
				</div>
				<div class="panel-body">
					<p class="col-sm-6 text-center">3 Stars</p>
					<p class="col-sm-6 text-center"><?=$clan1BestAttacks[3];?></p>
					<br>
					<p class="col-sm-6 text-center">2 Stars</p>
					<p class="col-sm-6 text-center"><?=$clan1BestAttacks[2];?></p>
					<br>
					<p class="col-sm-6 text-center">1 Star</p>
					<p class="col-sm-6 text-center"><?=$clan1BestAttacks[1];?></p>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<h2 class="hidden-lg" style="cursor: pointer;" onclick="clickRow('clan.php?clanId=<?=$clan2->get("id");?>');">
				<?=displayName($clan2->get('name'));?>
			</h2>
			<div class="panel panel-primary">
				<div class="panel-heading text-center">
					<h3 class=" panel-title">Attack Totals</h3>
				</div>
				<div class="panel-body">
					<p class="col-sm-6 text-center">Attacks Used</p>
					<p class="col-sm-6 text-center"><?=$clan2AttacksUsed;?></p>
					<br>
					<p class="col-sm-6 text-center">Attacks Won</p>
					<p class="col-sm-6 text-center"><?=$clan2AttacksWon;?></p>
					<br>
					<p class="col-sm-6 text-center">Attacks Lost</p>
					<p class="col-sm-6 text-center"><?=$clan2AttacksLost;?></p>
					<br>
					<p class="col-sm-6 text-center">Attacks Remaining</p>
					<p class="col-sm-6 text-center"><?=$clan2AttacksLeft;?></p>
				</div>
			</div>
			<div class="panel panel-primary">
				<div class="panel-heading text-center">
					<h3 class=" panel-title">Best Attacks</h3>
				</div>
				<div class="panel-body">
					<p class="col-sm-6 text-center">3 Stars</p>
					<p class="col-sm-6 text-center"><?=$clan2BestAttacks[3];?></p>
					<br>
					<p class="col-sm-6 text-center">2 Stars</p>
					<p class="col-sm-6 text-center"><?=$clan2BestAttacks[2];?></p>
					<br>
					<p class="col-sm-6 text-center">1 Star</p>
					<p class="col-sm-6 text-center"><?=$clan2BestAttacks[1];?></p>
				</div>
			</div>
		</div>
	<?}else{?>
		<div id="clanMessageWrapper" class="col-sm-12 <?=isset($clanMessage) ? '' : 'hidden';?>" style="margin-top: 10px;">
			<div id="clanMessageDiv">
				<div class="alert alert-info">
					<div id="clanMessage" style="text-align: center;"><?=linkify(sanitizeClanWarMessage($clanMessage));?></div>
					<?if($userCanEditClanMessage){?>
						<div style="text-align: right;">
							<a type="button" class="btn btn-xs btn-info" onclick="showEditMessageForm();">Edit</a>
						</div>
					<?}?>
				</div>
			</div>
			<?if($userCanEditClanMessage){?>
				<div id="editClanMessageDiv" hidden>
					<div class="alert alert-info">
						<textarea type="textarea" rows="<?=substr_count($clanMessage, "\n")+1;?>" style="text-align: center;" class="form-control" id="newMessage"><?=preg_replace('/\<br\>/', "\n", sanitizeClanWarMessage($clanMessage));?></textarea>
						<div style="text-align: right; margin-top: 10px;">
							<a type="button" class="btn btn-xs btn-danger" onclick="showMessage();">Cancel</a>
							<a type="button" class="btn btn-xs btn-info" onclick="saveMessage('newMessage');">Save</a>
						</div>
					</div>
				</div>
			<?}?>
		</div>
	<?}
	$showTabs = count($warAttacks) > 0 || count($requests) > 0 || count($allowedUsers) > 0 || $canRequestAccess || (!isset($clanMessage) && $userCanEditClanMessage);
	$otherTabs = count($warAttacks) > 0 || count($requests) > 0 || count($allowedUsers) > 0 || $canRequestAccess;?>
	<div id="tabs" class="col-md-12 <?=$showTabs ? '' : 'hidden';?>">
		<ul class="nav nav-pills" role="tablist">
			<li id="warPlayersTab" role="presentation" class="active" name="warPlayers">
				<a style="cursor: pointer;">War Players</a>
			</li>
			<?if(count($warAttacks) > 0){?>
				<li id="warAttacksTab" role="presentation" name="warAttacks">
					<a style="cursor: pointer;">War Events</a>
				</li>
			<?}if(count($requests) > 0){?>
				<li id="editRequestsTab" role="presentation" name="editRequests">
					<a style="cursor: pointer;">Edit Requests</a>
				</li>
			<?}if(count($allowedUsers) > 0){?>
				<li id="allowedUsersTab" role="presentation" name="allowedUsers">
					<a style="cursor: pointer;">Allowed Users</a>
				</li>
			<?}if($canRequestAccess){?>
				<li id="requestAccessTab" role="presentation" name="requestAccess">
					<a style="cursor: pointer;">Request Access</a>
				</li>
			<?}if($userCanEditClanMessage){?>
				<li id="addMessageTab" role="presentation" name="addMessage" class="<?=isset($clanMessage) ? 'hidden' : '';?>">
					<a style="cursor: pointer;">Add War Message</a>
				</li>
			<?}?>
		</ul>
	</div>
	<div id="warPlayers" class="col-md-12">
		<br>
		<div class="col-md-6">
			<h2 class="hidden-lg" style="cursor: pointer;" onclick="clickRow('clan.php?clanId=<?=$clan1->get("id");?>');">
				<?=displayName($clan1->get('name'));?>
			</h2>
			<div class="col-md-12">
				<?if($clan1CanAddMore && $userCanEdit && $war->isPreparationDay()){?>
					<a type="button" class="btn btn-success" href="/addWarPlayer.php?warId=<?=$war->get('id');?>&addClanId=<?=$clan1->get('id');?><?=$clanIdText;?>">Add Players</a><br><br>
					<?if(count($clan1Players) > 0){?>
						<div class="alert alert-warning" role="alert">
							There's not enough players in the war for this clan yet. <?if($userCanEdit){ print "You can add more by clicking the button above.";}?>
						</div>
					<?}
				}?>
			</div>
			<div class="col-md-12">
				<?if(count($clan1Players) > 0){?>
					<div class="table-responsive">
						<table class="table table-hover">
							<thead>
								<tr>
									<?if($userCanEdit){?>
										<th></th>
									<?}?>
									<th>Player</th>
									<th>First&nbsp;Attack</th>
									<th>Second&nbsp;Attack</th>
									<th>Defence</th>
									<?if($userCanEdit && $war->isPreparationDay()){?>
										<th></th>
									<?}?>
								</tr>
							</thead>
							<tbody>
								<?foreach ($clan1Players as $player) {
									$playerAttacks = getPlayerAttacks($player->get('id'));
									$firstAttack = $playerAttacks[0];
									$secondAttack = $playerAttacks[1];
									$playerDefences = getPlayerAttacks($player->get('id'), 'defence');
									$starsAgainst = -1;
									$rank = $war->getPlayerRank($player->get('id'));
									foreach ($playerDefences as $defence) {
										if($defence['totalStars'] > $starsAgainst){
											$starsAgainst = $defence['totalStars'];
										}
									}?>
									<tr class="playerRow-<?=$player->get('id');?>">
										<?if($userCanEdit){?>
											<td style="line-height: 1;">
												<?$upHidden='';
												$downHidden='';
												if($rank<=1){
													$upHidden = 'hidden';
												}if($rank>=count($war->getPlayers($clan1))){
													$downHidden = 'hidden';
												}?>
												<a id="up-<?=$player->get('id');?>" class="<?=$upHidden;?>" style="color: black; cursor: pointer;" onclick="changeOrder('<?=$player->get('id');?>', '<?=$clanId;?>', 'up');">
													<i class="fa fa-caret-up"></i><br>
												</a>
												<a id="down-<?=$player->get('id');?>" class="<?=$downHidden;?>" style="color: black; cursor: pointer;" onclick="changeOrder('<?=$player->get('id');?>', '<?=$clanId;?>', 'down');">
													<i class="fa fa-caret-down"></i><br>
												</a>
											</td>
										<?}?>
										<td class="rank-<?=$player->get('id');?>" style="cursor: pointer;" onclick="clickRow('player.php?playerId=<?=$player->get("id");?>');"><?=$rank . '.&nbsp;' . displayName($player->get('name'));?></td>
										<td>
											<?if(isset($firstAttack)){
												for($i=$firstAttack['totalStars']-$firstAttack['newStars'];$i>0;$i--){?>
													<i class="fa fa-star" style="color: silver;"></i>
												<?}
												for($i=$firstAttack['newStars'];$i>0;$i--){?>
													<i class="fa fa-star" style="color: gold;"></i>
												<?}
												for($i=$firstAttack['totalStars'];$i<3;$i++){?>
													<i class="fa fa-star-o" style="color: silver;"></i>
												<?}
											}else{
												if(count($clan2Players) > 0 && $userCanEdit && $war->isBattleDay()){?>
													<a type="button" class="btn btn-xs btn-success" href="/addWarAttack.php?warId=<?=$war->get('id');?>&playerId=<?=$player->get('id');?><?=$clanIdText;?>">Add Attack</a>
												<?}
											}?>
										</td>
										<td>
											<?if(isset($secondAttack)){
												for($i=$secondAttack['totalStars']-$secondAttack['newStars'];$i>0;$i--){?>
													<i class="fa fa-star" style="color: silver;"></i>
												<?}
												for($i=$secondAttack['newStars'];$i>0;$i--){?>
													<i class="fa fa-star" style="color: gold;"></i>
												<?}
												for($i=$secondAttack['totalStars'];$i<3;$i++){?>
													<i class="fa fa-star-o" style="color: silver;"></i>
												<?}
											}elseif(isset($firstAttack)){
												if(count($clan2Players) > 0 && $userCanEdit && $war->isBattleDay()){?>
													<a type="button" class="btn btn-xs btn-success" href="/addWarAttack.php?warId=<?=$war->get('id');?>&playerId=<?=$player->get('id');?><?=$clanIdText;?>">Add Attack</a>
												<?}
											}?>
										</td>
										<td>
											<?if($starsAgainst==3){?>
												<i class="fa fa-star" style="color: gold;"></i> <i class="fa fa-star" style="color: gold;"></i> <i class="fa fa-star" style="color: gold;"></i>
											<?}elseif($starsAgainst==2){?>
												<i class="fa fa-star" style="color: gold;"></i> <i class="fa fa-star" style="color: gold;"></i> <i class="fa fa-star-o" style="color: silver;"></i>
											<?}elseif($starsAgainst==1){?>
												<i class="fa fa-star" style="color: gold;"></i> <i class="fa fa-star-o" style="color: silver;"></i> <i class="fa fa-star-o" style="color: silver;"></i>
											<?}elseif($starsAgainst==0){?>
												<i class="fa fa-star-o" style="color: silver;"></i> <i class="fa fa-star-o" style="color: silver;"></i> <i class="fa fa-star-o" style="color: silver;"></i>
											<?}?>
										</td>
										<?if($userCanEdit && $war->isPreparationDay()){?>
											<td>
												<a type="button" class="btn btn-xs btn-danger" href="/processRemoveWarPlayer.php?warId=<?=$war->get('id');?>&playerId=<?=$player->get('id');?><?=$clanIdText;?>" data-toggle="popover" data-trigger="hover" data-placement="left" data-content="Click to remove this player from the war.">&times;</a>
											</td>
										<?}?>
									</tr>
								<?}?>
							</tbody>
						</table>
					</div>
				<?}else{?>
					<div class="alert alert-info" role="alert">
						<strong>On no!</strong> There's no players in the war for this clan. <?if($userCanEdit){ print "You can start by adding some above.";}?>
					</div>
				<?}?>
			</div>
		</div>
		<div class="col-md-6">
			<h2 class="hidden-lg" style="cursor: pointer;" onclick="clickRow('clan.php?clanId=<?=$clan2->get("id");?>');">
				<?=displayName($clan2->get('name'));?>
			</h2>
			<div class="col-md-12">
				<?if($clan2CanAddMore && $userCanEdit && $war->isPreparationDay()){?>
					<a type="button" class="btn btn-success" href="/addWarPlayer.php?warId=<?=$war->get('id');?>&addClanId=<?=$clan2->get('id');?><?=$clanIdText;?>">Add Players</a><br><br>
					<?if(count($clan2Players) > 0){?>
						<div class="alert alert-warning" role="alert">
							There's not enough players in the war for this clan yet. <?if($userCanEdit){ print "You can add more by clicking the button above.";}?>
						</div>
					<?}
				}?>
			</div>
			<div class="col-md-12">
				<?if(count($clan2Players) > 0){?>
					<div class="table-responsive">
						<table class="table table-hover">
							<thead>
								<tr>
									<?if($userCanEdit){?>
										<th></th>
									<?}?>
									<th>Player</th>
									<th>First&nbsp;Attack</th>
									<th>Second&nbsp;Attack</th>
									<th>Defence</th>
									<?if($userCanEdit && $war->isPreparationDay()){?>
										<th></th>
									<?}?>
								</tr>
							</thead>
							<tbody>
								<?foreach ($clan2Players as $player) {
									$playerAttacks = getPlayerAttacks($player->get('id'));
									$firstAttack = $playerAttacks[0];
									$secondAttack = $playerAttacks[1];
									$playerDefences = getPlayerAttacks($player->get('id'), 'defence');
									$starsAgainst = -1;
									$rank = $war->getPlayerRank($player->get('id'));
									foreach ($playerDefences as $defence) {
										if($defence['totalStars'] > $starsAgainst){
											$starsAgainst = $defence['totalStars'];
										}
									}?>
									<tr class="playerRow-<?=$player->get('id');?>">
										<?if($userCanEdit){?>
											<td style="line-height: 1;">
												<?$upHidden='';
												$downHidden='';
												if($rank<=1){
													$upHidden = 'hidden';
												}if($rank>=count($war->getPlayers($clan2))){
													$downHidden = 'hidden';
												}?>
												<a id="up-<?=$player->get('id');?>" class="<?=$upHidden;?>" style="color: black; cursor: pointer;" onclick="changeOrder('<?=$player->get('id');?>', '<?=$clanId;?>', 'up');">
													<i class="fa fa-caret-up"></i><br>
												</a>
												<a id="down-<?=$player->get('id');?>" class="<?=$downHidden;?>" style="color: black; cursor: pointer;" onclick="changeOrder('<?=$player->get('id');?>', '<?=$clanId;?>', 'down');">
													<i class="fa fa-caret-down"></i><br>
												</a>
											</td>
										<?}?>
										<td class="rank-<?=$player->get('id');?>" style="cursor: pointer;" onclick="clickRow('player.php?playerId=<?=$player->get("id");?>');"><?=$rank . '.&nbsp;' . displayName($player->get('name'));?></td>
										<td>
											<?if(isset($firstAttack)){
												for($i=$firstAttack['totalStars']-$firstAttack['newStars'];$i>0;$i--){?>
													<i class="fa fa-star" style="color: silver;"></i>
												<?}
												for($i=$firstAttack['newStars'];$i>0;$i--){?>
													<i class="fa fa-star" style="color: gold;"></i>
												<?}
												for($i=$firstAttack['totalStars'];$i<3;$i++){?>
													<i class="fa fa-star-o" style="color: silver;"></i>
												<?}
											}else{
												if(count($clan1Players) > 0 && $userCanEdit && $war->isBattleDay()){?>
													<a type="button" class="btn btn-xs btn-success" href="/addWarAttack.php?warId=<?=$war->get('id');?>&playerId=<?=$player->get('id');?><?=$clanIdText;?>">Add Attack</a>
												<?}
											}?>
										</td>
										<td>
											<?if(isset($secondAttack)){
												for($i=$secondAttack['totalStars']-$secondAttack['newStars'];$i>0;$i--){?>
													<i class="fa fa-star" style="color: silver;"></i>
												<?}
												for($i=$secondAttack['newStars'];$i>0;$i--){?>
													<i class="fa fa-star" style="color: gold;"></i>
												<?}
												for($i=$secondAttack['totalStars'];$i<3;$i++){?>
													<i class="fa fa-star-o" style="color: silver;"></i>
												<?}
											}elseif(isset($firstAttack)){
												if(count($clan1Players) > 0 && $userCanEdit && $war->isBattleDay()){?>
													<a type="button" class="btn btn-xs btn-success" href="/addWarAttack.php?warId=<?=$war->get('id');?>&playerId=<?=$player->get('id');?><?=$clanIdText;?>">Add Attack</a>
												<?}
											}?>
										</td>
										<td>
											<?if($starsAgainst==3){?>
												<i class="fa fa-star" style="color: gold;"></i> <i class="fa fa-star" style="color: gold;"></i> <i class="fa fa-star" style="color: gold;"></i>
											<?}elseif($starsAgainst==2){?>
												<i class="fa fa-star" style="color: gold;"></i> <i class="fa fa-star" style="color: gold;"></i> <i class="fa fa-star-o" style="color: silver;"></i>
											<?}elseif($starsAgainst==1){?>
												<i class="fa fa-star" style="color: gold;"></i> <i class="fa fa-star-o" style="color: silver;"></i> <i class="fa fa-star-o" style="color: silver;"></i>
											<?}elseif($starsAgainst==0){?>
												<i class="fa fa-star-o" style="color: silver;"></i> <i class="fa fa-star-o" style="color: silver;"></i> <i class="fa fa-star-o" style="color: silver;"></i>
											<?}?>
										</td>
										<?if($userCanEdit && $war->isPreparationDay()){?>
											<td>
												<a type="button" class="btn btn-xs btn-danger" href="/processRemoveWarPlayer.php?warId=<?=$war->get('id');?>&playerId=<?=$player->get('id');?><?=$clanIdText;?>" data-toggle="popover" data-trigger="hover" data-placement="left" data-content="Click to remove this player from the war.">&times;</a>
											</td>
										<?}?>
									</tr>
								<?}?>
							</tbody>
						</table>
					</div>
				<?}else{?>
					<div class="alert alert-info" role="alert">
						<strong>On no!</strong> There's no players in the war for this clan. <?if($userCanEdit){ print "You can start by adding some above.";}?>
					</div>
				<?}?>
			</div>
		</div>
	</div>
	<?if(count($warAttacks) > 0){?>
		<div id="warAttacks" class="col-md-12 hidden">
			<div class="table-responsive">
				<table class="table table-hover">
					<thead>
						<tr>
							<th class="text-left">
								<h3 style="cursor: pointer;" onclick="clickRow('clan.php?clanId=<?=$clan1->get("id");?>');">
									<?=displayName($clan1->get('name'));?>
								</h3>
							</th>
							<?if($userCanEdit && $war->isBattleDay()){?>
								<th class="text-center">Actions</th>
							<?}?>
							<th class="text-right">
								<h3 style="cursor: pointer;" onclick="clickRow('clan.php?clanId=<?=$clan2->get("id");?>');">
									<?=displayName($clan2->get('name'));?>
								</h3>
							</th>
						</tr>
					</thead>
					<tbody>
						<?foreach ($warAttacks as $attack) {
							$attacker = $warPlayers[$attack['attackerId']];
							$defender = $warPlayers[$attack['defenderId']];
							$attackerRank = $war->getPlayerRank($attacker->get('id'));
							$defenderRank = $war->getPlayerRank($defender->get('id'));
							$attackerClanId = $attack['attackerClanId'];
							$totalStars = $attack['totalStars'];
							$newStars = $attack['newStars'];?>
							<tr>
								<?if($attackerClanId == $clan1->get('id')){?>
									<td class="text-left"><strong class="rank-<?=$attacker->get('id');?>"><?=$attackerRank . '.&nbsp;' . displayName($attacker->get('name'));?></strong>&nbsp;<i class="fa fa-star"></i><br>
										<?for($i=$totalStars-$newStars;$i>0;$i--){?>
											<i class="fa fa-star" style="color: silver;"></i>
										<?}
										for($i=$newStars;$i>0;$i--){?>
											<i class="fa fa-star" style="color: gold;"></i>
										<?}
										for($i=$totalStars;$i<3;$i++){?>
											<i class="fa fa-star-o" style="color: silver;"></i>
										<?}?>
									</td>
									<?if($userCanEdit && $war->isBattleDay()){?>
										<td class="text-center">
											<a type="button" class="btn btn-sm btn-success" href="/editWarAttack.php?warId=<?=$war->get('id');?>&attackerId=<?=$attacker->get('id');?>&defenderId=<?=$defender->get('id');?><?=$clanIdText;?>">Edit</a>
											<a type="button" class="btn btn-sm btn-danger" href="/processRemoveWarAttack.php?warId=<?=$war->get('id');?>&attackerId=<?=$attacker->get('id');?>&defenderId=<?=$defender->get('id');?><?=$clanIdText;?>" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Click to remove this attack from the war.">&times;</a>
										</td>
									<?}?>
									<td class="text-right"><i class="fa fa-shield"></i>&nbsp;<strong class="rank-<?=$defender->get('id');?>"><?=$defenderRank . '. ' . displayName($defender->get('name'));?></strong><br>
										<?if($totalStars==0){?>
											<i>Defended</i>
										<?}else{?>
											<i>Defeat</i>
										<?}?>
									</td>
								<?}else{?>
									<td class="text-left"><strong class="rank-<?=$defender->get('id');?>"><?=$defenderRank . '. ' . displayName($defender->get('name'));?></strong>&nbsp;<i class="fa fa-shield"></i><br>
										<?if($totalStars==0){?>
											<i>Defended</i>
										<?}else{?>
											<i>Defeat</i>
										<?}?>
									</td>
									<?if($userCanEdit && $war->isBattleDay()){?>
										<td class="text-center">
											<a type="button" class="btn btn-sm btn-success" href="/editWarAttack.php?warId=<?=$war->get('id');?>&attackerId=<?=$attacker->get('id');?>&defenderId=<?=$defender->get('id');?><?=$clanIdText;?>">Edit</a>
											<a type="button" class="btn btn-sm btn-danger" href="/processRemoveWarAttack.php?warId=<?=$war->get('id');?>&attackerId=<?=$attacker->get('id');?>&defenderId=<?=$defender->get('id');?><?=$clanIdText;?>" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="Click to remove this attack from the war.">&times;</a>
										</td>
									<?}?>
									<td class="text-right"><i class="fa fa-star"></i>&nbsp;<strong class="rank-<?=$attacker->get('id');?>"><?=$attackerRank . '.&nbsp;' . displayName($attacker->get('name'));?></strong><br>
										<?for($i=$totalStars-$newStars;$i>0;$i--){?>
											<i class="fa fa-star" style="color: silver;"></i>
										<?}
										for($i=$newStars;$i>0;$i--){?>
											<i class="fa fa-star" style="color: gold;"></i>
										<?}
										for($i=$totalStars;$i<3;$i++){?>
											<i class="fa fa-star-o" style="color: silver;"></i>
										<?}?></td>
									</td>
								<?}?>
							</tr>
						<?}?>
					</tbody>
				</table>
			</div>
		</div>
	<?}if(count($requests) > 0){?>
		<div id="editRequests" class="col-md-12 hidden">
			<div class="col-md-12"><br>
				<div class="alert alert-info" role="alert">
					The following players have requested access to update this war's information. Accepting their request will allow them to add, edit and delete attacks for this war.
				</div>
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>Player Name</th>
								<th>Player Rank</th>
								<th>Email</th>
								<th>Message</th>
								<th class="text-right">Actions</th>
							</tr>
						</thead>
						<tbody>
							<?foreach($requests as $request){?>
								<tr>
									<td>
										<?$requestPlayer = $request->user->get('player');
										if(isset($requestPlayer)){
											print displayName($requestPlayer->get('name'));
										}?>									
									</td>
									<td>
										<?if(isset($requestPlayer)){
											print rankFromCode($requestPlayer->get('rank'));
										}?>
									</td>
									<td><?=$request->user->get('email');?></td>
									<td><?=displayName($request->message);?></td>
									<td class="text-right">
										<a type="button" class="btn btn-xs btn-danger" href="/processEditRequestResponse.php?warId=<?=$war->get('id');?>&userId=<?=$request->user->get('id');?>&response=decline<?=$clanIdText;?>">Decline</a>
										<a type="button" class="btn btn-xs btn-success" href="/processEditRequestResponse.php?warId=<?=$war->get('id');?>&userId=<?=$request->user->get('id');?>&response=accept<?=$clanIdText;?>">Accept</a>
									</td>
								</tr>
							<?}?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?}if(count($allowedUsers) > 0){?>
		<div id="allowedUsers" class="col-md-12 hidden">
			<div class="col-md-12"><br>
				<div class="alert alert-info" role="alert">
					The following players have been granted access to edit this war's information. That means they can add, edit and delete attacks for this war.
				</div>
				<div class="table-responsive">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>Player Name</th>
								<th>Player Rank</th>
								<th>Clan</th>
								<th>Email</th>
								<th class="text-right">Actions</th>
							</tr>
						</thead>
						<tbody>
							<?foreach($allowedUsers as $allowedUser){?>
								<tr>
									<?$allowedUserPlayer = $allowedUser->get('player');
									if(isset($allowedUserPlayer)){?>
										<td style="cursor: pointer;" onclick="clickRow('player.php?playerId=<?=$allowedUserPlayer->get("id");?>');">
											<?=displayName($allowedUserPlayer->get('name'));?>
										</td>
										<td>
											<?=rankFromCode($allowedUserPlayer->get('rank'));?>
										</td>
										<?$allowedUserPlayerClan = $allowedUserPlayer->get('clan');
										if(isset($allowedUserPlayerClan)){?>
											<td style="cursor: pointer;" onclick="clickRow('clan.php?clanId=<?=$allowedUserPlayerClan->get("id");?>');">
												<?=displayName($allowedUserPlayerClan->get('name'));?>
											</td>
										<?}else{?>
											<td></td>
										<?}
									}else{?>
										<td></td>
										<td></td>
										<td></td>
									<?}?>
									<td><?=$allowedUser->get('email');?></td>
									<td class="text-right">
										<a type="button" class="btn btn-xs btn-danger" href="/processRevokeWarAccess.php?warId=<?=$war->get('id');?>&userId=<?=$allowedUser->get('id') . $clanIdText;?>">Revoke Access</a>
									</td>
								</tr>
							<?}?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?}if($canRequestAccess){?>
		<div id="requestAccess" class="col-md-12 hidden">
			<div class="col-md-12" style="margin-top: 10px;">
				<div class="alert alert-info" role="alert">
					<?=displayName($war->get('clan1')->get('name'));?> has restricted access to updating their war information, but you can request access to edit this war below.
				</div>
				<form class="form-horizontal" action="/processRequestAccess.php" method="POST">
					<div class="col-md-12">
						<div class="col-md-6">
							<div class="form-group">
								<label class="col-sm-4 control-lable" for="message">Message:</label>
								<div class="col-sm-8">
									<textarea type="textarea" rows="6" class="form-control" id="message" name="message" placeholder="Hello, could I have access to update this war's attack information?" value="Hello, could I have access to update this war's attack information?"></textarea>
								</div>
							</div>
						</div>
					</div>
					<input hidden id="warId" name="warId" value="<?=$war->get('id');?>">
					<?if(isset($clan)){?>
						<input hidden id="clanId" name="clanId" value="<?=$clan->get('id');?>">
					<?}?>
					<div class="row col-md-6" style="margin-top: 10px;">
						<div class="text-right btn-actions">
							<button type="submit" class="btn btn-success" name="submit" value="submit">Request</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	<?}?>
	<div id="addMessage" class="col-md-12 hidden">
		<div class="col-md-12" style="margin-top: 10px;">
			<div class="alert alert-info" role="alert">
				Add a message that your clan members can read when they view this page.
			</div>
			<div class="col-md-12">
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-sm-4 control-lable">Message:</label>
						<div class="col-sm-8">
							<textarea type="textarea" rows="6" class="form-control" id="addClanMessage" placeholder="Hey Clan! Don't forget to use both your attacks!" value=""></textarea>
						</div>
					</div>
				</div>
			</div>
			<div class="row col-md-6" style="margin-top: 10px;">
				<div class="text-right btn-actions">
					<button type="submit" class="btn btn-success" onclick="saveMessage('addClanMessage');">Save</button>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
function showEditMessageForm(){
	$('#clanMessageDiv').hide();
	$('#editClanMessageDiv').show();
}
function showMessage(){
	$('#clanMessageDiv').show();
	$('#editClanMessageDiv').hide();
}
function saveMessage(id){
	var warId = '<?=$war->get('id');?>';
	var clanId = '<?=$clanId;?>';
	var message = $('#' + id).val();
	$.ajax({
		url: '/processUpdateWarMessage.php',
		method: 'POST',
		data: {
			warId: warId,
			clanId: clanId,
			message: message
		}
	}).done(function(xhr){
		data = jQuery.parseJSON(xhr);
		if(data.error){
			alert(data.error);
		}else{
			var message = data.message;
			$('#clanMessage').html(message);
			$('#newMessage').html(data.textarea);
			var height = (message.match(/\<br\>/g) || []).length+1;
			$('#newMessage').prop('rows', height)
			checkMessageStatus();
		}
	}).fail(function(xhr, textStatus){
		alert('There was an unexpected error. Please refresh the page and try again.');
	});
}
function checkMessageStatus(){
	if($('#clanMessage').html().length == 0){
		$('#clanMessageWrapper').addClass('hidden');
		$('#addMessageTab').removeClass('hidden');
		$('#tabs').removeClass('hidden');
		$('#addClanMessage').val('');
	}else{
		$('#warPlayersTab').click();
		$('#clanMessageWrapper').removeClass('hidden');
		$('#addMessageTab').addClass('hidden');
		if(!<?=json_encode($otherTabs);?>){
			$('#tabs').addClass('hidden');
		}
		showMessage();
	}
}
function changeOrder(playerId, clanId, action){
	var warId = '<?=$war->get('id');?>';
	$.ajax({
		url: '/processUpdateWarRank.php',
		method: 'POST',
		data: {
			warId: warId,
			playerId: playerId,
			clanId: clanId,
			action: action
		}
	}).done(function(xhr){
		data = jQuery.parseJSON(xhr);
		if(data.error){
			alert(data.error);
		}else{
			var player1Id = data.player1.id;
			var player2Id = data.player2.id;
			var row1 = $('.playerRow-' + player1Id);
			var row2 = $('.playerRow-' + player2Id);
			var temp = row1.html();
			row1.html(row2.html());
			row2.html(temp);
			row1Class = row1.attr('class');
			row2Class = row2.attr('class');
			row1.removeClass(row1Class).addClass(row2Class);
			row2.removeClass(row2Class).addClass(row1Class);
			var up = $('#up-' + player1Id);
			up.removeClass('hidden');
			if(data.player1.hideUp){
				up.addClass('hidden');
			}
			var down = $('#down-' + player1Id);
			down.removeClass('hidden');
			if(data.player1.hideDown){
				down.addClass('hidden');
			}
			var up = $('#up-' + player2Id);
			up.removeClass('hidden');
			if(data.player2.hideUp){
				up.addClass('hidden');
			}
			var down = $('#down-' + player2Id);
			down.removeClass('hidden');
			if(data.player2.hideDown){
				down.addClass('hidden');
			}
			$('.rank-' + player1Id).html(data.player1.rank);
			$('.rank-' + player2Id).html(data.player2.rank);
		}
	}).fail(function(xhr, textStatus){
		alert('There was an unexpected error. Please refresh the page and try again.');
	});
}
function clickRow(href){
	window.document.location = href;
}
var tabs = $('[role=presentation]').map(function(){return $(this).attr('name');});
for (var i = tabs.length - 1; i >= 0; i--) {
	$('#' + tabs[i] + 'Tab').on('click', function(){
		hideAllTabs();
		$(this).addClass('active');
		$('#' + $(this).attr('name')).removeClass('hidden');
	});
}
function hideAllTabs(){
	for (var i = tabs.length - 1; i >= 0; i--) {
		$('#' + tabs[i] + 'Tab').removeClass('active');
		$('#' + tabs[i]).addClass('hidden');
	}
}
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();   
});
</script>
<?
require('footer.php');