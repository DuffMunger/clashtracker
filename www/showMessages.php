<? if (isset($_SESSION['curMessage'])) { ?>
	<div class="alert alert-success alert-dismissible fade in">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<p><?=$_SESSION['curMessage']; ?></p>
	</div>
<? } ?>
  
<? if (isset($_SESSION['curError'])) { ?>
	<div class="alert alert-danger alert-dismissible fade in">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		<p><?=$_SESSION['curError']; ?></p>
	</div>
<? } ?>

<? 
	unset($_SESSION['curMessage']);
	unset($_SESSION['curError']);
?>
