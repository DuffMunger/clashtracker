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
	$clan = $war->get('clan1');
	if($clan->get('id') == $clanId){
		$clanEnemy = $war->get('clan2');
	}else{
		$clanEnemy = $clan;
		$clan = $war->get('clan2');
	}
	$clanId = $clan->get('id');
}else{
	$clanId = null;
}

if(!$war->isPreparationDay()){
	$_SESSION['curError'] = 'Adding war players is only available during the war preparation period.';
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

$addClanId = $_GET['addClanId'];
if($war->isClanInWar($addClanId)){
	$clan1 = $war->get('clan1');
	$clan2 = $war->get('clan2');
	if($clan1->get('id') == $addClanId){
		$addClan = $clan1;
	}else{
		$addClan = $clan2;
	}
}else{
	$_SESSION['curError'] = 'Clan not in selected war.';
	header('Location: /wars.php');
	exit;
}

$warPlayers = $war->getPlayers($addClan);
$limit = $war->get('size') - count($warPlayers);
$allMembers = $addClan->getMembers();
$members = array();
foreach ($allMembers as $member) {
	if(!$war->isPlayerInWar($member->get('id'))){
		$members[] = $member;
	}
}

for ($i=1; $i < count($members); $i++) {
	$j=$i;
	$member1Val = $members[$j]->get('warRank');
	$member2Val = $members[$j-1]->get('warRank');
	while($j>0 && $member1Val < $member2Val){
		$temp = $members[$j];
		$members[$j] = $members[$j-1];
		$members[$j-1] = $temp;
		$j--;
		if($j>0){
			$member1Val = $members[$j]->get('warRank');
			$member2Val = $members[$j-1]->get('warRank');
		}
	}
}

for ($i=1; $i < count($members); $i++) {
	$j=$i;
	$member1Val = $members[$j]->warsSinceLastParticipated();
	$member2Val = $members[$j-1]->warsSinceLastParticipated();
	while($j>0 && $member1Val < $member2Val){
		$temp = $members[$j];
		$members[$j] = $members[$j-1];
		$members[$j-1] = $temp;
		$j--;
		if($j>0){
			$member1Val = $members[$j]->warsSinceLastParticipated();
			$member2Val = $members[$j-1]->warsSinceLastParticipated();
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
			<li><a href="/clan.php?clanId=<?=$clan->get('id');?>"><?=htmlspecialchars($clan->get('name'));?></a></li>
			<li><a href="/wars.php?clanId=<?=$clan->get('id');?>">Wars</a></li>
			<li><a href="/war.php?warId=<?=$war->get('id');?>&clanId=<?=$clan->get('id');?>"><?=htmlspecialchars($clan->get('name'));?> vs. <?=htmlspecialchars($clanEnemy->get('name'));?></a></li>
		<?}else{?>
			<li><a href="/wars.php">Wars</a></li>
			<li><a href="/war.php?warId=<?=$war->get('id');?>"><?=htmlspecialchars($clan1->get('name'));?> vs. <?=htmlspecialchars($clan2->get('name'));?></a></li>
		<?}?>
		<li class="active">Add Players to War</li>
	</ol>
	<?require('showMessages.php');?>
	<h1>Add Players to War</h1><br>
	<div class="">
		<form class="form-horizontal" action="/processAddWarPlayer.php" method="POST">
			<input hidden name="warId" value="<?=$war->get('id');?>">
			<input hidden name="addClanId" value="<?=$addClan->get('id');?>">
			<?if(isset($clanId)){?>
				<input hidden name="clanId" value="<?=$clan->get('id');?>">
			<?}?>
			<div class="col-md-12">
				<?if(count($members) > 0){?>
					<div class="col-md-6">
						<h4>Select Existing Members:</h4>
						<table class="table table-hover">
							<thead>
								<tr style="cursor: pointer;">
									<th onclick="selectAll();">
										<div class="checkbox">
											<div class="col-md-12">
												<?if(count($members) > $war->get('size')){
													$all = 'First ' . $war->get('size');
												}else{
													$all = ' All';
												}?>
												<input id="selectall" type="checkbox">Select <?=$all;?>
											</div>
										</div>
									</th>
								</tr>
							</thead>
							<tbody>
								<?foreach ($members as $member) {?>
									<tr style="cursor: pointer;">
										<td onclick="selectMember(<?=$member->get('id');?>);">
											<div class="checkbox">
												<div class="col-md-12">
													<input id="<?=$member->get('id');?>" type="checkbox" name="members[]" value="<?=$member->get('id');?>"><?=htmlspecialchars($member->get('name'));?>
												</div>
											</div>
										</td>
									</tr>
								<?}?>
							</tbody>
						</table>
					</div>
				<?}?>
				<div class="col-md-6">
					<h4>Add Other Player:</h4><br>
					<div class="form-group">
						<label class="col-sm-4 control-lable" for="name">Player Name:</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="name" name="name" placeholder="Angry Neeson 52" value="<?=$_SESSION['name'];?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-lable" for="playerTag">Player Tag:</label>
						<div class="col-sm-8">
							<input type="text" class="form-control" id="playerTag" name="playerTag" placeholder="#JKFH83J" value="<?=$_SESSION['playerTag'];?>">
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-12 text-right btn-actions">
					<br>
					<button type="submit" class="btn btn-default" name="cancel" value="cancel">Cancel</button>
					<button type="submit" class="btn btn-success" name="submit" value="submit">Submit</button>
				</div>
			</div>
			<br>
		</form>
	</div>
</div>
<script type="text/javascript">
function selectMember(id){
	var checkbox = $('#' + id);
	if(!checkbox.is(':checked') && !checkbox.is(':disabled')){
		checkbox.prop('checked', true);
	}else{
		checkbox.prop('checked', false);
	}
	var limit = "<?=$limit;?>";
	var selectall = $('#selectall');
	if(selectall.is(':checked')) limit++;
	if ($('input[type=checkbox]:checked').length >= limit) {
		$("input:checkbox:not(:checked)").each(function(){
			$(this).attr('disabled', true);
			$('#name').attr('disabled', true);
			$('#playerTag').attr('disabled', true);
		});
	}else{
		$("input:checkbox:not(:checked)").each(function(){
			$(this).attr('disabled', false);
			$('#name').attr('disabled', false);
			$('#playerTag').attr('disabled', false);
		});
	}
}
function selectAll(){
	var checkboxes = $("input:checkbox");
	var selectall = $('#selectall');
	var select;
	if(!selectall.is(':checked') && !selectall.is(':disabled')){
		select = true;
		$('#name').attr('disabled', true);
		$('#playerTag').attr('disabled', true);
	}else{
		select = false;
		$('#name').attr('disabled', false);
		$('#playerTag').attr('disabled', false);
	}
	var size = <?=$war->get('size');?>;
	for (var i = 0; i <= checkboxes.length - 1; i++) {
		checkbox = $('#' + checkboxes[i].id);
		checkbox.prop('checked', false);
		if(i<=size && select){
			selectMember(checkboxes[i].id);
		}else if(!select){
			checkbox.attr('disabled', false);
		}
	}
}
$('input[type="checkbox"]').on('click', function(event) {
	var id = event.target.id;
	if(id == "selectall"){
		selectAll();
	}else{
		selectMember(id);
	}
});
</script>
<?
require('footer.php');