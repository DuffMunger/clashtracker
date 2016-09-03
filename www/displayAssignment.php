<?if($battleDay && isset($prevAttack)){?>
	War Assignment
	<a type="button" class="btn btn-xs btn-success" href="/addWarAttack.php?warId=<?=$war->get('id');?>&playerId=<?=$player->get('id');?><?=$clanIdText;?>">Add Attack</a>
<?}else{?>
	War Assignment
<?}?>