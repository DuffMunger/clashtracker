<?
require('init.php');
require('session.php');

$clanId = $_GET['clanId'];
try{
	$clan = new clan($clanId);
	$clanId = $clan->get('id');
}catch(Exception $e){
	$_SESSION['curError'] = 'No clan with id ' . $clanId . ' found.';
	header('Location: /clans.php');
	exit;
}

$edit = isset($_GET['edit']);

$members = $clan->getMembers();
$members = sortPlayersByWarScore($members);

$moreInfo = array();
$moreInfo[] = "A score calculated from the player's offensive and defensive performance in wars.";
$moreInfo[] = "The average number of total stars the player got with their first attack.";
$moreInfo[] = "The average number of stars that counted towards the war that the player got with their first attack.";
$moreInfo[] = "The average number of total stars the player got with their second attack.";
$moreInfo[] = "The average number of stars that counted towards the war that the player got with their second attack.";
$moreInfo[] = "The average number of stars the player lost per war.";
$moreInfo[] = "The average number of times the player was attacked per war.";
$moreInfo[] = "The average number of times the player attacked per war.";
$moreInfo[] = "The average rank from their own the player attacks each war.";
$moreInfo[] = "The average rank from their own the player defends each war.";
$moreInfo[] = "Increasing this will increase the impact first attacks have on a player's score.";
$moreInfo[] = "Increasing this will increase the impact second attacks have on a player's score.";
$moreInfo[] = "Increasing this will increase the impact total stars achieved in both attacks have on a player's score.";
$moreInfo[] = "Increasing this will increase the impact new stars achieved in both attacks have on a player's score.";
$moreInfo[] = "Increasing this will increase the penalty for failing to use attacks.";
$moreInfo[] = "Increasing this will increase the penalty for losing stars on defence.";
$moreInfo[] = "Increasing this will increase the amount more defences has on reducing the penalty for losing stars on defence.";
$moreInfo[] = "Increasing this will increase the bonus for attacking higher level bases in war.";
$moreInfo[] = "Increasing this will increase the amount being attacked by higher level players reduces the penalty for losing stars on defence.";

$userHasAccessToUpdateClan = userHasAccessToUpdateClan($clan);

require('header.php');
?>
<div class="col-md-12">
	<ol class="breadcrumb">
		<li><a href="/home.php">Home</a></li>
		<li><a href="/clans.php">Clans</a></li>
		<li><a href="/clan.php?clanId=<?=$clanId;?>"><?=htmlspecialchars($clan->get('name'));?></a></li>
		<li class="active">War Statistics</li>
	</ol>
	<?require('showMessages.php');?>
	<h1>War Statistics</h1>
	<?if($userHasAccessToUpdateClan){?>
		<div class="row">
			<div id="editButton" <?if($edit){?>hidden<?}?> class="col-md-12">
				<button type="button" class="btn btn-primary" onclick="showEditForm();">Edit Scoring</button>
			</div>
			<div id="editForm" <?if(!$edit){?>hidden<?}?> class="col-md-12">
				<form class="form-horizontal" action="/processEditScoring.php" method="POST">
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-sm-4 control-lable" for="totalStarsWeight">First Attack Weight:&nbsp;<i class="fa fa-info-circle" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[10];?>"></i></label>
							<div class="col-sm-8">
								<input type="number" class="form-control" id="totalStarsWeight" name="firstAttackWeight" value="<?=$clan->get('firstAttackWeight');?>">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4 control-lable" for="secondAttackWeight">Second Attack Weight:&nbsp;<i class="fa fa-info-circle" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[11];?>"></i></label>
							<div class="col-sm-8">
								<input type="number" class="form-control" id="secondAttackWeight" name="secondAttackWeight" value="<?=$clan->get('secondAttackWeight');?>">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4 control-lable" for="totalStarsWeight">Total Stars Weight:&nbsp;<i class="fa fa-info-circle" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[12];?>"></i></label>
							<div class="col-sm-8">
								<input type="number" class="form-control" id="totalStarsWeight" name="totalStarsWeight" value="<?=$clan->get('totalStarsWeight');?>">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4 control-lable" for="newStarsWeight">New Stars Weight:&nbsp;<i class="fa fa-info-circle" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[13];?>"></i></label>
							<div class="col-sm-8">
								<input type="number" class="form-control" id="newStarsWeight" name="newStarsWeight" value="<?=$clan->get('newStarsWeight');?>">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4 control-lable" for="attacksUsedWeight">Attacks Used Weight:&nbsp;<i class="fa fa-info-circle" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[14];?>"></i></label>
							<div class="col-sm-8">
								<input type="number" class="form-control" id="attacksUsedWeight" name="attacksUsedWeight" value="<?=$clan->get('attacksUsedWeight');?>">
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="col-sm-4 control-lable" for="defenceWeight">Defence Weight:&nbsp;<i class="fa fa-info-circle" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[15];?>"></i></label>
							<div class="col-sm-8">
								<input type="number" class="form-control" id="defenceWeight" name="defenceWeight" value="<?=$clan->get('defenceWeight');?>">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4 control-lable" for="numberOfDefencesWeight">Number of Defences Weight:&nbsp;<i class="fa fa-info-circle" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[16];?>"></i></label>
							<div class="col-sm-8">
								<input type="number" class="form-control" id="numberOfDefencesWeight" name="numberOfDefencesWeight" value="<?=$clan->get('numberOfDefencesWeight');?>">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4 control-lable" for="rankAttackedWeight">Rank Attacked Weight:&nbsp;<i class="fa fa-info-circle" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[17];?>"></i></label>
							<div class="col-sm-8">
								<input type="number" class="form-control" id="rankAttackedWeight" name="rankAttackedWeight" value="<?=$clan->get('rankAttackedWeight');?>">
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-4 control-lable" for="rankDefendedWeight">Rank Defended Weight:&nbsp;<i class="fa fa-info-circle" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[18];?>"></i></label>
							<div class="col-sm-8">
								<input type="number" class="form-control" id="rankDefendedWeight" name="rankDefendedWeight" value="<?=$clan->get('rankDefendedWeight');?>">
							</div>
						</div>
					</div>
					<input hidden id="clanId" name="clanId" value="<?=$clan->get('id');?>">
					<div class="row">
						<div class="col-sm-12 text-right btn-actions">
							<button class="btn btn-default" onclick="return hideEditForm();">Cancel</button>
							<button type="submit" class="btn btn-success" name="submit" value="submit">Save</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	<?}
	if(count($members)>0){?>
		<div class="table-responsive">
			<table class="table table-hover">
				<thead>
					<tr>
						<th></th>
						<th>Name</th>
						<th><div data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[0];?>">Score</div></th>
						<th><div data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[1];?>">1<sup>st</sup> Attack Stars</div></th>
						<th><div data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[2];?>">1<sup>st</sup> Attack New Stars</div></th>
						<th><div data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[3];?>">2<sup>nd</sup> Attack Stars</div></th>
						<th><div data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[4];?>">2<sup>nd</sup> Attack New Stars</div></th>
						<th><div data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[5];?>">Stars on Defence</div></th>
						<th><div data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[6];?>">Number of Defences</div></th>
						<th><div data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[7];?>">Attacks Used</div></th>
						<th><div data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[8];?>">Rank Attacked</div></th>
						<th><div data-toggle="popover" data-trigger="hover" data-placement="top" data-content="<?=$moreInfo[9];?>">Rank Defended</div></th>
						<th class="text-right">Player Tag</th>
					</tr>
				</thead>
				<tbody>
					<?foreach ($members as $member) {?>
						<tr style="cursor: pointer;" onclick="clickRow('player.php?playerId=<?=$member->get("id");?>&clanId=<?=$clan->get('id');?>');">
							<td width="20">
								<?$url = $member->get('leagueUrl');
								if(strlen($url)>0){?>
									<img src="<?=$url;?>" height="20" width="20">
								<?}?>
							</td>
							<td><?=htmlspecialchars($member->get('name'));?></td>
							<td><?=number_format($member->getScore(), 2);?></td>
							<td><?=number_format($member->get('firstAttackTotalStars') / $member->get('numberOfWars'), 2);?></td>
							<td><?=number_format($member->get('firstAttackNewStars') / $member->get('numberOfWars'), 2);?></td>
							<td><?=number_format($member->get('secondAttackTotalStars') / $member->get('numberOfWars'), 2);?></td>
							<td><?=number_format($member->get('secondAttackNewStars') / $member->get('numberOfWars'), 2);?></td>
							<td><?=number_format($member->get('starsOnDefence') / $member->get('numberOfWars'), 2);?></td>
							<td><?=number_format($member->get('numberOfDefences') / $member->get('numberOfWars'), 2);?></td>
							<td><?=number_format($member->get('attacksUsed') / $member->get('numberOfWars'), 2);?></td>
							<td>
								<?if($member->get('attacksUsed')!=0){?>
									<?=number_format($member->get('rankAttacked') / $member->get('attacksUsed'), 2);?>
								<?}else{?>
									0.00
								<?}?>
							</td>
							<td>
								<?if($member->get('numberOfDefences')!=0){?>
									<?=number_format($member->get('rankDefended') / $member->get('numberOfDefences'), 2);?>
								<?}else{?>
									0.00
								<?}?>
							</td>
							<td class="text-right"><?=$member->get('tag');?></td>
						</tr>
					<?}?>
				</tbody>
			</table>
		</div>
	<?}else{?>
		<div class="alert alert-info">
			<strong>Oh no!</strong> There's no members currently in this clan that have been in enough wars. Players must be in at least 2 completed wars appear on this page.
		</div>
	<?}?>
</div>
<script type="text/javascript">
function clickRow(href){
	window.document.location = href;
}
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();   
});
function showEditForm(){
	$('#editButton').hide();
	$('#editForm').show();
}
function hideEditForm(){
	$('#editButton').show();
	$('#editForm').hide();
	return false;
}
</script>
<?
require('footer.php');