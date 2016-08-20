<?
require('init.php');
require('session.php');

$sort = $_GET['sort'];
$sort = isset($sort) ? $sort : 'trophies_desc';

$players = array();
try{
	$players = Player::getPlayers($sort);
}catch(Exception $e){
	$_SESSION['curError'] = $e->getMessage();
}

$sorts = array(
	'name' => 'name',
	'level' => 'level_desc',
	'trophies' => 'trophies_desc');
if(strpos($sort, '_desc') !== FALSE){
	$sorts[str_replace('_desc', '', $sort)] = str_replace('_desc', '', $sort);
}else{
	$sorts[str_replace('_desc', '', $sort)] = str_replace('_desc', '', $sort) . '_desc';
}

require('header.php');
?>
<div class="col-md-12">
	<ol class="breadcrumb">
		<li><a href="/home.php">Home</a></li>
		<li class="active">Players</li>
	</ol>
	<?require('showMessages.php');?>
	<h1>Players</h1><br>
	<a type="button" class="btn btn-success" href="/addPlayer.php">Add Player</a><br><br>
	<?if(count($players)>0){?>
		<div class="table-responsive">
			<table class="table table-hover">
				<thead>
					<tr>
						<th></th>
						<th style="cursor: pointer;" onclick="clickRow('players.php?sort=<?=$sorts['name'];?>');"><i class="fa fa-sort"></i>&nbsp;Name</th>
						<th style="cursor: pointer;" onclick="clickRow('players.php?sort=<?=$sorts['level'];?>');"><i class="fa fa-sort"></i>&nbsp;Level</th>
						<th style="cursor: pointer;" onclick="clickRow('players.php?sort=<?=$sorts['trophies'];?>');"><i class="fa fa-sort"></i>&nbsp;Trophies</th>
						<th>Clan Name</th>
						<th>Clan Rank</th>
						<th class="text-right">Player Tag</th>
					</tr>
				</thead>
				<tbody>
					<?foreach ($players as $player) {?>
						<tr style="cursor: pointer;">
							<td width="20">
								<?$url = $player->get('leagueUrl');
								if(strlen($url)>0){?>
									<img src="<?=$url;?>" height="20" width="20">
								<?}?>
							</td>
							<td onclick="clickRow('player.php?playerId=<?=$player->get("id");?>');"><?=displayName($player->get('name'));?></td>
							<td onclick="clickRow('player.php?playerId=<?=$player->get("id");?>');"><i class="fa fa-certificate" style="color: #43BBE9;"></i>&nbsp;<?=$player->get('level');?></td>
							<td onclick="clickRow('player.php?playerId=<?=$player->get("id");?>');"><i class="fa fa-trophy" style="color: gold;"></i>&nbsp;<?=$player->get('trophies');?></td>
							<?$clan = $player->getClan();
							if(isset($clan)){?>
								<td onclick="clickRow('clan.php?clanId=<?=$clan->get("id");?>');">
									<?$url = $clan->get('badgeUrl');
									if(strlen($url)>0){?>
										<img src="<?=$url;?>" height="20" width="20">
									<?}?>
									<?=displayName($clan->get('name'));?>
								</td>
							<?}else{?>
								<td></td>
							<?}?>
							<td onclick="clickRow('player.php?playerId=<?=$player->get("id");?>');"><?=rankFromCode($player->get('rank'));?></td>
							<td class="text-right" onclick="clickRow('player.php?playerId=<?=$player->get("id");?>');"><?=$player->get('tag');?></td>
						</tr>
					<?}?>
				</tbody>
			</table>
		</div>
	<?}else{?>
		<div class="alert alert-info" role="alert">
			<strong>Oh no!</strong> There's no players in our records. You can start by adding one above.
		</div>
	<?}?>
</div>
<script type="text/javascript">
function clickRow(href){
	window.document.location = href;
}
</script>
<?
require('footer.php');