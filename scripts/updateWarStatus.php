<?
require('../www/init.php');
$wars = War::getWars(1000);
foreach ($wars as $war) {
	if($war->oldIsEditable()){
		$war->set('status', War::PREPARATION);
	}else{
		$war->set('status', War::COMPLETE);
	}
}