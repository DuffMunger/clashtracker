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
	$clanIdText = '&clanId=' . $clan1->get('id');
}else{
	$clanId = null;
	$clanIdText = '';
	$clan1 = $war->get('clan1');
	$clan2 = $war->get('clan2');
}

if(!userHasAccessToUpdateClan($war->get('clan1'))){
	$_SESSION['curError'] = NO_ACCESS;
	header('Location: /war.php?warId=' . $war->get('id') . $clanIdText);
	exit;
}

if(!$war->isBattleDay()){
	$_SESSION['curError'] = 'Editing war attacks is only available during battle day.';
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
		exit;
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
		exit;
	}
}

$attackerId = $_GET['attackerId'];
if($war->isPlayerInWar($attackerId)){
	$attacker = new player($attackerId);
	$attackerId = $attacker->get('id');
}else{
	$_SESSION['curError'] = 'Attacker not in war.';
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
	}
	exit;
}

$defenderId = $_GET['defenderId'];
if($war->isPlayerInWar($defenderId)){
	$defender = new player($defenderId);
	$defenderId = $defender->get('id');
}else{
	$_SESSION['curError'] = 'Defender not in war.';
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
	}
	exit;
}

$attack = $war->getAttack($attackerId, $defenderId);
if(!isset($attack)){
	$_SESSION['curError'] = htmlspecialchars($attacker->get('name')) . ' never attacked ' . htmlspecialchars($defender->get('name')) . ' in this war.';
	if(isset($clanId)){
		header('Location: /war.php?warId=' . $war->get('id') . '&clanId=' . $clanId);
	}else{
		header('Location: /war.php?warId=' . $war->get('id'));
	}
	exit;
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
		<li class="active">Edit War Attack</li>
	</ol>
	<?require('showMessages.php');?>
	<h1>Edit War Attack</h1><br>
	<div class="">
		<form class="form-horizontal" action="/processEditWarAttack.php" method="POST">
			<input hidden name="warId" value="<?=$war->get('id');?>">
			<input hidden name="attackerId" value="<?=$attacker->get('id');?>">
			<input hidden name="defenderId" value="<?=$defender->get('id');?>">
			<?if(isset($clanId)){?>
				<input hidden name="clanId" value="<?=$clan1->get('id');?>">
			<?}?>
			<div class="col-md-12">
				<h4><?=htmlspecialchars($attacker->get('name')) . ' attacked ' . htmlspecialchars($defender->get('name'));?></h4>
				<div class="col-md-6">
					<div class="form-group">
						<label class="col-sm-4 control-lable" for="stars">Stars:</label>
						<div class="col-sm-8">
							<?for ($i=0; $i <= 3; $i++){?>
								<div class="col-sm-3">
									<input id="<?=$i;?>stars" <?=($attack['totalStars'] == $i) ? 'checked' : '';?> onclick="selectStars(<?=$i;?>);" name="stars" value="<?=$i;?>" class="stars" type="checkbox">
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
function selectStars(stars){
	$(".stars").each(function(){
		$(this).attr('checked', false);
	});
	$('#' + stars + 'stars').prop('checked', true);
}
</script>
<?
require('footer.php');