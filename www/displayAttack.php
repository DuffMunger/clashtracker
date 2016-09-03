<?
for($i=$attack['totalStars']-$attack['newStars'];$i>0;$i--){?>
	<i class="fa fa-star" style="color: silver;"></i>
<?}
for($i=$attack['newStars'];$i>0;$i--){?>
	<i class="fa fa-star" style="color: gold;"></i>
<?}
for($i=$attack['totalStars'];$i<3;$i++){?>
	<i class="fa fa-star-o" style="color: silver;"></i>
<?}