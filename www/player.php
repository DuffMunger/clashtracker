<?
require('init.php');
require('session.php');

$playerId = $_GET['playerId'];
$force = isset($_GET['force']);
try{
	$player = new Player($playerId);
	$playerId = $player->get('id');
	refreshPlayerInfo($player, $force);
}catch(NoResultFoundException $e){
	$player = new Player();
	$player->create('TEMPORARY NAME', $playerId);
	if(refreshPlayerInfo($player, true) === false){
		$_SESSION['curError'] = 'Player Tag was not found in Clash of Clans.';
		header('Location: /players.php');
		exit;
	}
}catch(Exception $e){
	$_SESSION['curError'] = $e->getMessage();
	header('Location: /players.php');
	exit;
}

$clanId = $_GET['clanId'];
try{
	$clan = new clan($clanId);
	$clanId = $clan->get('id');
}catch(Exception $e){
	$clan = null;
}

$attacks = $player->getAttacks();
$defences = $player->getDefences();
$wars = $player->getWars();
if(count($wars)>0 && $wars[0]->isEditable()){
	$lastWarId = $wars[0]->get('id');
	$ignoreLast=1;
}
$attackStats = array(3=>0, 2=>0, 1=>0, 0=>0);
$countAttacks = 0;
$defenceStats = array(3=>0, 2=>0, 1=>0, 0=>0);
$countDefences = 0;
$totalAttackStars = 0;
$totalDefenceStars = 0;
$warsAvailable = false;
foreach ($attacks as $attack) {
	if($attack['warId'] != $lastWarId){
		$attackStats[$attack['totalStars']]++;
		$totalAttackStars += $attack['totalStars'];
		$countAttacks++;
		$warsAvailable = true;
	}
}
foreach ($defences as $defence) {
	if($defence['warId'] != $lastWarId){
		$defenceStats[$defence['totalStars']]++;
		$totalDefenceStars += $defence['totalStars'];
		$countDefences++;
		$warsAvailable = true;
	}
}
$attackStats['DNA'] = (count($wars)-$ignoreLast)*2 - $countAttacks;
$averageAttackStars = ($totalAttackStars==0) ? 0 : $totalAttackStars/$countAttacks;
$averageDefenceStars = ($totalDefenceStars==0) ? 0 : $totalDefenceStars/$countDefences;

$gold = $player->getGold();
$elixir = $player->getElixir();
$oil = $player->getDarkElixir();

$goldAvailable = count($gold)>1;
$elixirAvailable = count($elixir)>1;
$oilAvailable = count($oil)>1;
$lootAvailable = ($goldAvailable||$elixirAvailable||$oilAvailable);

$goldPastYear = $player->getGold(yearAgo());
$elixirPastYear = $player->getElixir(yearAgo());
$oilPastYear = $player->getDarkElixir(yearAgo());
$goldAvailablePastYear = count($goldPastYear)>1;
$elixirAvailablePastYear = count($elixirPastYear)>1;
$oilAvailablePastYear = count($oilPastYear)>1;
$lootAvailablePastYear = (($goldAvailablePastYear||$elixirAvailablePastYear||$oilAvailablePastYear) && (count($gold)!=count($goldPastYear)||count($elixir)!=count($elixirPastYear)||count($oil)!=count($oilPastYear)));

$goldPastMonth = $player->getGold(monthAgo());
$elixirPastMonth = $player->getElixir(monthAgo());
$oilPastMonth = $player->getDarkElixir(monthAgo());
$goldAvailablePastMonth = count($goldPastMonth)>1;
$elixirAvailablePastMonth = count($elixirPastMonth)>1;
$oilAvailablePastMonth = count($oilPastMonth)>1;
$lootAvailablePastMonth = ($goldAvailablePastMonth||$elixirAvailablePastMonth||$oilAvailablePastMonth);

$goldPastWeek = $player->getGold(weekAgo());
$elixirPastWeek = $player->getElixir(weekAgo());
$oilPastWeek = $player->getDarkElixir(weekAgo());
$goldAvailablePastWeek = count($goldPastWeek)>1;
$elixirAvailablePastWeek = count($elixirPastWeek)>1;
$oilAvailablePastWeek = count($oilPastWeek)>1;
$lootAvailablePastWeek = ($goldAvailablePastWeek||$elixirAvailablePastWeek||$oilAvailablePastWeek);

$bestGold = $player->getBestReportResult('GO');
$bestElixir = $player->getBestReportResult('EL');
$bestDarkElixir = $player->getBestReportResult('DE');

$playerClan = $player->getClan();
$playerClans = $player->getClans();

$largestValue = max(0, $gold[0]['statAmount'], $elixir[0]['statAmount'], $oil[0]['statAmount']);
require('header.php');
?>
<div class="col-md-12">
	<ol class="breadcrumb">
		<li><a href="/home.php">Home</a></li>
		<?if(isset($clan)){?>
			<li><a href="/clans.php">Clans</a></li>
			<li><a href="/clan.php?clanId=<?=$clan->get('id');?>"><?=htmlspecialchars($clan->get('name'));?></a></li>
		<?}else{?>
			<li><a href="/players.php">Players</a></li>
		<?}?>
		<li class="active"><?=htmlspecialchars($player->get('name'));?></li>
	</ol>
	<?require('showMessages.php');?>
	<div class="row col-md-12">
		<?$url = $player->get('leagueUrl');
		$xs=0;$md=0;
		if(strlen($url)>0){
			$xs=3;$md=1;?>
			<div class="col-xs-3 col-md-1">
				<img src="<?=$url;?>">
			</div>
		<?}?>
		<div class="col-xs-<?=12-$xs;?> col-md-<?=8-$md;?>">
			<h1 style="margin-bottom: 0px;">
				<?=htmlspecialchars($player->get('name'));?>
			</h1>
			<div class="col-xs-12 col-md-12">
				<h6 style="margin-top: 0px;">
				<?if(isset($clan)){?>
					<?=rankFromCode($player->get('rank', $clan->get('id')));?>
				<?}else{?>
					<?=rankFromCode($player->get('rank'));?>
				<?}?>
				</h6>
			</div>
		</div>
	</div>
	<?if($warsAvailable){?>
		<div class="row col-md-12">
			<h3 style="cursor: pointer;" onclick="clickRow('wars.php?playerId=<?=$player->get("id");?>');">
				<i class="fa fa-star" style="color: gold;"></i>
				&nbsp;Wars
			</h3>
			<div class="col-md-12">
				<div class="col-md-12">
					<?$numPies=0;
					if($countAttacks>0) $numPies++;
					if($countDefences>0) $numPies++;
					if($countAttacks>0){?>
						<div class="col-xs-12 col-sm-<?=12/$numPies;?> col-md-<?=6/$numPies;?> text-center" style="margin-bottom: 10px;">
							<h4>Attacks</h4><br>
							<canvas id="attackPie"></canvas>
						</div>
					<?}if($countDefences>0){?>
						<div class="col-xs-12 col-sm-<?=12/$numPies;?> col-md-<?=6/$numPies;?> text-center" style="margin-bottom: 10px;">
							<h4>Defences</h4><br>
							<canvas id="defencePie"></canvas>
						</div>
					<?}?>
					<div class="col-md-1"></div>
					<div class="jumbotron col-xs-12 col-md-5">
						<label class="col-xs-8 col-sm-7">Average <i class="fa fa-star" style="color: gold;"></i> per&nbsp;attack</label>
						<div class="col-xs-4 col-sm-5 text-right">
							<p><?=round($averageAttackStars, 2);?></p>
						</div>
						<label class="col-xs-8 col-sm-7">Average <i class="fa fa-star" style="color: gold;"></i> per&nbsp;defence</label>
						<div class="col-xs-4 col-sm-5 text-right">
							<p><?=round($averageDefenceStars, 2);?></p>
						</div>
					</div>
				</div>
			</div>
		</div><br>
	<?}
	if($lootAvailable){?>
		<div class="row col-md-12">
			<h3><i class="fa fa-coins" style="color: gold;"></i>&nbsp;Loot</h3>
			<div class="col-md-12">
				<?if($lootAvailable){?>
					<div class="col-md-12">
						<ul class="nav nav-pills" role="tablist">
							<?if($lootAvailable){?>
								<li id="allTimeTab" onclick="showLootGraph('allTime');" role="presentation" class="active">
									<a style="cursor: pointer;">All Time</a>
								</li>
							<?}
							if($lootAvailablePastYear){?>
								<li id="pastYearTab" onclick="showLootGraph('pastYear');" role="presentation">
									<a style="cursor: pointer;">Past Year</a>
								</li>
							<?}
							if($lootAvailablePastMonth){?>
								<li id="pastMonthTab" onclick="showLootGraph('pastMonth');" role="presentation">
									<a style="cursor: pointer;">Past Month</a>
								</li>
							<?}
							if($lootAvailablePastWeek){?>
								<li id="pastWeekTab" onclick="showLootGraph('pastWeek');" role="presentation">
									<a style="cursor: pointer;">Past Week</a>
								</li>
							<?}?>
						</ul>
					</div><br><br><br>
					<div class="col-md-12">
						<div id="lootLineChartDiv" class="col-md-6">
							<canvas id="lootLineChart" height="100px"></canvas>
						</div>
						<div class="col-md-1"></div>
						<div class="jumbotron col-xs-12 col-md-5">
							<div id="allTimeAverage">
								<?if($goldAvailable){?>
									<label class="col-xs-12 col-sm-6">Average&nbsp;<i class="fa fa-2x fa-coins" style="color: gold;"></i></label>
									<div class="col-xs-12 col-sm-6 text-right">
										<p><?=number_format($player->getAverageGold(), 0, '.', ',') . '/week';?></p>
									</div>
								<?}
								if($elixirAvailable){?>
									<label class="col-xs-12 col-sm-6">Average&nbsp;&nbsp;<i class="fa fa-2x fa-tint" style="color: #FF09F4;"></i></label>
									<div class="col-xs-12 col-sm-6 text-right">
										<p><?=number_format($player->getAverageElixir(), 0, '.', ',') . '/week';?></p>
									</div>
								<?}
								if($oilAvailable){?>
									<label class="col-xs-12 col-sm-6">Average&nbsp;&nbsp;<i class="fa fa-2x fa-tint"></i></label>
									<div class="col-xs-12 col-sm-6 text-right">
										<p><?=number_format($player->getAverageDarkElixir(), 0, '.', ',') . '/week';?></p>
									</div>
								<?}?>
							</div>
							<?if($lootAvailablePastYear){?>
								<div id="pastYearAverage" hidden>
									<?if($goldAvailable){?>
										<label class="col-xs-12 col-sm-6">Average&nbsp;<i class="fa fa-2x fa-coins" style="color: gold;"></i></label>
										<div class="col-xs-12 col-sm-6 text-right">
											<p><?=number_format($player->getAverageGold(yearAgo()), 0, '.', ',') . '/week';?></p>
										</div>
									<?}
									if($elixirAvailable){?>
										<label class="col-xs-12 col-sm-6">Average&nbsp;&nbsp;<i class="fa fa-2x fa-tint" style="color: #FF09F4;"></i></label>
										<div class="col-xs-12 col-sm-6 text-right">
											<p><?=number_format($player->getAverageElixir(yearAgo()), 0, '.', ',') . '/week';?></p>
										</div>
									<?}
									if($oilAvailable){?>
										<label class="col-xs-12 col-sm-6">Average&nbsp;&nbsp;<i class="fa fa-2x fa-tint"></i></label>
										<div class="col-xs-12 col-sm-6 text-right">
											<p><?=number_format($player->getAverageDarkElixir(yearAgo()), 0, '.', ',') . '/week';?></p>
										</div>
									<?}?>
								</div>
							<?}
							if($lootAvailablePastMonth){?>
								<div id="pastMonthAverage" hidden>
									<?if($goldAvailable){?>
										<label class="col-xs-12 col-sm-6">Average&nbsp;<i class="fa fa-2x fa-coins" style="color: gold;"></i></label>
										<div class="col-xs-12 col-sm-6 text-right">
											<p><?=number_format($player->getAverageGold(monthAgo()), 0, '.', ',') . '/week';?></p>
										</div>
									<?}
									if($elixirAvailable){?>
										<label class="col-xs-12 col-sm-6">Average&nbsp;&nbsp;<i class="fa fa-2x fa-tint" style="color: #FF09F4;"></i></label>
										<div class="col-xs-12 col-sm-6 text-right">
											<p><?=number_format($player->getAverageElixir(monthAgo()), 0, '.', ',') . '/week';?></p>
										</div>
									<?}
									if($oilAvailable){?>
										<label class="col-xs-12 col-sm-6">Average&nbsp;&nbsp;<i class="fa fa-2x fa-tint"></i></label>
										<div class="col-xs-12 col-sm-6 text-right">
											<p><?=number_format($player->getAverageDarkElixir(monthAgo()), 0, '.', ',') . '/week';?></p>
										</div>
									<?}?>
								</div>
							<?}
							if($lootAvailablePastWeek){?>
								<div id="pastWeekAverage" hidden>
									<?if($goldAvailable){?>
										<label class="col-xs-12 col-sm-6">Average&nbsp;<i class="fa fa-2x fa-coins" style="color: gold;"></i></label>
										<div class="col-xs-12 col-sm-6 text-right">
											<p><?=number_format($player->getAverageGold(weekAgo()), 0, '.', ',') . '/week';?></p>
										</div>
									<?}
									if($elixirAvailable){?>
										<label class="col-xs-12 col-sm-6">Average&nbsp;&nbsp;<i class="fa fa-2x fa-tint" style="color: #FF09F4;"></i></label>
										<div class="col-xs-12 col-sm-6 text-right">
											<p><?=number_format($player->getAverageElixir(weekAgo()), 0, '.', ',') . '/week';?></p>
										</div>
									<?}
									if($oilAvailable){?>
										<label class="col-xs-12 col-sm-6">Average&nbsp;&nbsp;<i class="fa fa-2x fa-tint"></i></label>
										<div class="col-xs-12 col-sm-6 text-right">
											<p><?=number_format($player->getAverageDarkElixir(weekAgo()), 0, '.', ',') . '/week';?></p>
										</div>
									<?}?>
								</div>
							<?}?>
						</div>
					</div>
					<?if(isset($bestGold) || isset($bestElixir) || isset($bestDarkElixir)){
						$count = 1;
						if(isset($bestGold)){$count++;}
						if(isset($bestElixir)){$count++;}
						if(isset($bestDarkElixir)){$count++;}
						$colSize = 12 / $count;?>
						<div class="col-md-12">
							<div class="col-md-<?=$colSize;?>">
								<h5>Best Results in Clan Loot Reports:</h5>
							</div>
							<?if(isset($bestGold)){?>
								<div class="col-md-<?=$colSize;?>">
									<h6>Gold&nbsp;<i class="fa fa-coins" style="color: gold;"></i>&nbsp;<?=number_format($bestGold, 0, '.', ',');?>/week</h6>
								</div>
							<?}?>
							<?if(isset($bestElixir)){?>
								<div class="col-md-<?=$colSize;?>">
									<h6>Elixir&nbsp;<i class="fa fa-tint" style="color: #FF09F4;"></i>&nbsp;<?=number_format($bestElixir, 0, '.', ',');?>/week</h6>
								</div>
							<?}?>
							<?if(isset($bestDarkElixir)){?>
								<div class="col-md-<?=$colSize;?>">
									<h6>Dark Elixir&nbsp;<i class="fa fa-tint"></i>&nbsp;<?=number_format($bestDarkElixir, 0, '.', ',');?>/week</h6>
								</div>
							<?}?>
						</div>
					<?}
				}?>
			</div><br>
		</div><br>
	<?}if(count($playerClans)>0){?>
		<div class="row col-md-12">
			<?if(count($playerClans)>1){?>
				<h3><i class="fa fa-shield"></i>&nbsp;Clans</h3>
			<?}else{?>
				<h3><i class="fa fa-shield"></i>&nbsp;Clan</h3>
			<?}
			if(isset($playerClan)){?>
				<div class="jumbotron col-xs-12 col-sm-12 col-md-6">
					<label class="col-xs-4 col-md-6">Current Clan</label>
					<div class="col-xs-8 col-md-6 text-right" style="cursor: pointer;" onclick="clickRow('clan.php?clanId=<?=$playerClan->get("id");?>');">
						<p>
							<?$url = $playerClan->get('badgeUrl');
							if(strlen($url)>0){?>
								<img src="<?=$url;?>" height="20" width="20">
							<?}?>
							<?=htmlspecialchars($playerClan->get('name'));?>
						</p>
					</div>
					<label class="col-xs-4 col-md-6">Current Rank</label>
					<div class="col-xs-8 col-md-6 text-right">
						<p><?=rankFromCode($player->get('rank'));?></p>
					</div>
					<label class="col-xs-4 col-sm-4 col-md-6">Date Joined</label>
					<div class="col-xs-8 col-sm-8 col-md-6 text-right">
						<p><?=displayName(date('M. j, Y', strtotime($playerClan->playerJoined($player->get('id')))));?></p>
					</div>
				</div>
			<?}
			if((isset($playerClan)&&count($playerClans)>1)||(!isset($playerClan)&&count($playerClans)>0)){?>
				<div class="col-xs-12 col-sm-12 col-md-6">
					<?if((isset($playerClan)&&count($playerClans)>2) || (!isset($playerClan)&&count($playerClans)>1)){?>
						<h4>Previous Clans</h4>
					<?}else{?>
						<h4>Previous Clan</h4>
					<?}?>
					<div class="table-responsive">
						<table class="table table-hover">
							<thead>
								<tr>
									<th></th>
									<th>Name</th>
									<th>Clan&nbsp;Points</th>
									<th>Wars&nbsp;Won</th>
									<th class="text-right">Clan Tag</th>
								</tr>
							</thead>
							<tbody>
								<?foreach ($playerClans as $clan) {
									if((isset($playerClan) && $clan->get('id') != $playerClan->get('id')) || !isset($playerClan)){?>
										<tr style="cursor: pointer;" onclick="clickRow('clan.php?clanId=<?=$clan->get("id");?>');">
											<td width="20">
												<?$url = $clan->get('badgeUrl');
												if(strlen($url)>0){?>
													<img src="<?=$url;?>" height="20" width="20">
												<?}?>
											</td>
											<td><?=htmlspecialchars($clan->get('name'));?></td>
											<td><?=$clan->get('clanPoints');?></td>
											<td><?=$clan->get('warWins');?></td>
											<td class="text-right"><?=$clan->get('tag');?></td>
										</tr>
									<?}
								}?>
							</tbody>
						</table>
					</div>
				</div>
			<?}?>
		</div>
	<?}?>
</div>
<script type="text/javascript">
function clickRow(href){
	window.document.location = href;
}
var options;
$(document).ready(function() {
	var largestValue = <?=$largestValue;?>;
	var label;
	if(largestValue > 1000000000){
		label = "<%=Math.round(value/1000000)/1000 + 'B'%>";
	}else if(largestValue > 100000000){
		label = "<%=Math.round(value/100000)/10 + 'M'%>";
	}else if(largestValue > 10000000){
		label = "<%=Math.round(value/10000)/100 + 'M'%>";
	}else if(largestValue > 10000){
		label = "<%=Math.round(value/100)/10 + 'K'%>";
	}else{
		label = "<%=value%>";
	}
	options = {
		responsive : true,
		scaleType : "date",
		scaleDateTimeFormat : "mmm d, yyyy",
		scaleLabel : label,
		emptyDataMessage: "Oh no! We don't have enough records for this player's loot to display any stats. You can start by adding some above."
	};
	var warsAvailable = <?=json_encode($warsAvailable);?>;
	var attacksAvailable = <?=json_encode($countAttacks>0);?>;
	if(attacksAvailable){

		var ctx = $("#attackPie").get(0).getContext("2d");
		var data = [
			{
				value: <?=$attackStats[3];?>,
				color:"#419641",
				highlight: "#5CB85C",
				label: "3 Star Attacks"
			},
			{
				value: <?=$attackStats[2];?>,
				color: "#F8CC00",
				highlight: "#F8EB00",
				label: "2 Star Attacks"
			},
			{
				value: <?=$attackStats[1];?>,
				color: "#E15B00",
				highlight: "#E16F00",
				label: "1 Star Attacks"
			},
			{
				value: <?=$attackStats[0];?>,
				color: "#D0470D",
				highlight: "#E0470D",
				label: "0 Star Attacks"
			},
			{
				value: <?=$attackStats['DNA'];?>,
				color: "#C6000D",
				highlight: "#D6000D",
				label: "Did not attack"
			}
		]
		var attackPieChart = new Chart(ctx).Pie(data,{});
	}
	var defenceAvailable = <?=json_encode($countDefences>0);?>;
	if(defenceAvailable){
		var ctx = $("#defencePie").get(0).getContext("2d");
		var data = [
			{
				value: <?=$defenceStats[3];?>,
				color:"#D0470D",
				highlight: "#E0470D",
				label: "3 Star Defence"
			},
			{
				value: <?=$defenceStats[2];?>,
				color: "#E15B00",
				highlight: "#E16F00",
				label: "2 Star Defence"
			},
			{
				value: <?=$defenceStats[1];?>,
				color: "#F8CC00",
				highlight: "#F8EB00",
				label: "1 Star Defence"
			},
			{
				value: <?=$defenceStats[0];?>,
				color: "#419641",
				highlight: "#5CB85C",
				label: "Defended"
			}
		]
		var defencePieChart = new Chart(ctx).Pie(data,{});
	}
	showAllTimeGraph();
});
var lootChart;
function showAllTimeGraph(){
	var lootAvailable = <?=json_encode($lootAvailable);?>;
	if(lootAvailable){
		var ctx = $("#lootLineChart").get(0).getContext("2d");
		var data = [
			<?if($goldAvailable){?>
				{
					label: 'Gold',
					strokeColor: 'gold',
					pointColor: 'gold',
					pointStrokeColor: '#fff',
					data: [
						<?foreach ($gold as $loot) {
							echo "{ x: " . (strtotime($loot['dateRecorded'])*1000) . ", y: " . $loot['statAmount'] . " },\n\t\t\t\t\t";
						}?>
					]
				},
			<?}if($elixirAvailable){?>
				{
					label: 'Elixir',
					strokeColor: '#FF09F4',
					pointColor: '#FF09F4',
					pointStrokeColor: '#fff',
					data: [
						<?foreach ($elixir as $loot) {
							echo "{ x: " . (strtotime($loot['dateRecorded'])*1000) . ", y: " . $loot['statAmount'] . " },\n\t\t\t\t\t";
						}?>
					]
				},
			<?}if($oilAvailable){?>
				{
					label: 'Dark Elixir (×100)',
					strokeColor: 'black',
					pointColor: 'black',
					pointStrokeColor: '#fff',
					data: [
						<?foreach ($oil as $loot) {
							echo "{ x: " . (strtotime($loot['dateRecorded'])*1000) . ", y: " . ($loot['statAmount']*100) . " },\n\t\t\t\t\t";
						}?>
					]
				}
			<?}?>
		];
		lootChart = new Chart(ctx).Scatter(data, options);
	}
}
function showPastYearGraph(){
	var lootAvailablePastYear = <?=json_encode($lootAvailablePastYear);?>;
	if(lootAvailablePastYear){
		var ctx = $("#lootLineChart").get(0).getContext("2d");
		var data = [
			<?if($goldAvailablePastYear){?>
				{
					label: 'Gold',
					strokeColor: 'gold',
					pointColor: 'gold',
					pointStrokeColor: '#fff',
					data: [
						<?foreach ($goldPastYear as $loot) {
							echo "{ x: " . (strtotime($loot['dateRecorded'])*1000) . ", y: " . $loot['statAmount'] . " },\n\t\t\t\t\t";
						}?>
					]
				},
			<?}if($elixirAvailablePastYear){?>
				{
					label: 'Elixir',
					strokeColor: '#FF09F4',
					pointColor: '#FF09F4',
					pointStrokeColor: '#fff',
					data: [
						<?foreach ($elixirPastYear as $loot) {
							echo "{ x: " . (strtotime($loot['dateRecorded'])*1000) . ", y: " . $loot['statAmount'] . " },\n\t\t\t\t\t";
						}?>
					]
				},
			<?}if($oilAvailablePastYear){?>
				{
					label: 'Dark Elixir (×100)',
					strokeColor: 'black',
					pointColor: 'black',
					pointStrokeColor: '#fff',
					data: [
						<?foreach ($oilPastYear as $loot) {
							echo "{ x: " . (strtotime($loot['dateRecorded'])*1000) . ", y: " . ($loot['statAmount']*100) . " },\n\t\t\t\t\t";
						}?>
					]
				}
			<?}?>
		];
		lootChart = new Chart(ctx).Scatter(data, options);
	}
}
function showPastMonthGraph(){
	var lootAvailablePastMonth = <?=json_encode($lootAvailablePastMonth);?>;
	if(lootAvailablePastMonth){
		var ctx = $("#lootLineChart").get(0).getContext("2d");
		var data = [
			<?if($goldAvailablePastMonth){?>
				{
					label: 'Gold',
					strokeColor: 'gold',
					pointColor: 'gold',
					pointStrokeColor: '#fff',
					data: [
						<?foreach ($goldPastMonth as $loot) {
							echo "{ x: " . (strtotime($loot['dateRecorded'])*1000) . ", y: " . $loot['statAmount'] . " },\n\t\t\t\t\t";
						}?>
					]
				},
			<?}if($elixirAvailablePastMonth){?>
				{
					label: 'Elixir',
					strokeColor: '#FF09F4',
					pointColor: '#FF09F4',
					pointStrokeColor: '#fff',
					data: [
						<?foreach ($elixirPastMonth as $loot) {
							echo "{ x: " . (strtotime($loot['dateRecorded'])*1000) . ", y: " . $loot['statAmount'] . " },\n\t\t\t\t\t";
						}?>
					]
				},
			<?}if($oilAvailablePastMonth){?>
				{
					label: 'Dark Elixir (×100)',
					strokeColor: 'black',
					pointColor: 'black',
					pointStrokeColor: '#fff',
					data: [
						<?foreach ($oilPastMonth as $loot) {
							echo "{ x: " . (strtotime($loot['dateRecorded'])*1000) . ", y: " . ($loot['statAmount']*100) . " },\n\t\t\t\t\t";
						}?>
					]
				}
			<?}?>
		];
		lootChart = new Chart(ctx).Scatter(data, options);
	}
}
function showPastWeekGraph(){
	var lootAvailablePastWeek = <?=json_encode($lootAvailablePastWeek);?>;
	if(lootAvailablePastWeek){
		var ctx = $("#lootLineChart").get(0).getContext("2d");
		var data = [
			<?if($goldAvailablePastWeek){?>
				{
					label: 'Gold',
					strokeColor: 'gold',
					pointColor: 'gold',
					pointStrokeColor: '#fff',
					data: [
						<?foreach ($goldPastWeek as $loot) {
							echo "{ x: " . (strtotime($loot['dateRecorded'])*1000) . ", y: " . $loot['statAmount'] . " },\n\t\t\t\t\t";
						}?>
					]
				},
			<?}if($elixirAvailablePastWeek){?>
				{
					label: 'Elixir',
					strokeColor: '#FF09F4',
					pointColor: '#FF09F4',
					pointStrokeColor: '#fff',
					data: [
						<?foreach ($elixirPastWeek as $loot) {
							echo "{ x: " . (strtotime($loot['dateRecorded'])*1000) . ", y: " . $loot['statAmount'] . " },\n\t\t\t\t\t";
						}?>
					]
				},
			<?}if($oilAvailablePastWeek){?>
				{
					label: 'Dark Elixir (×100)',
					strokeColor: 'black',
					pointColor: 'black',
					pointStrokeColor: '#fff',
					data: [
						<?foreach ($oilPastWeek as $loot) {
							echo "{ x: " . (strtotime($loot['dateRecorded'])*1000) . ", y: " . ($loot['statAmount']*100) . " },\n\t\t\t\t\t";
						}?>
					]
				}
			<?}?>
		];
		lootChart = new Chart(ctx).Scatter(data, options);
	}
}
function showLootGraph(type){
	$('#allTimeAverage').hide();
	$('#pastYearAverage').hide();
	$('#pastMonthAverage').hide();
	$('#pastWeekAverage').hide();
	$('#allTimeTab').removeClass('active');
	$('#pastYearTab').removeClass('active');
	$('#pastMonthTab').removeClass('active');
	$('#pastWeekTab').removeClass('active');
	$('#' + type + 'Tab').addClass('active');
	var ctx = $("#lootLineChart").get(0).getContext("2d");
	lootChart.destroy();
	if(type == 'allTime'){
		showAllTimeGraph();
		$('#allTimeAverage').show();
	}else if(type == 'pastYear'){
		showPastYearGraph();
		$('#pastYearAverage').show();
	}else if(type == 'pastMonth'){
		showPastMonthGraph();
		$('#pastMonthAverage').show();
	}else if(type == 'pastWeek'){
		showPastWeekGraph();
		$('#pastWeekAverage').show();
	}
}
</script>
<?
require('footer.php');