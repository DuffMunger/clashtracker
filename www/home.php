<?
require('init.php');
require('session.php');
require('header.php');

$blogPosts = BlogPost::getBlogPosts();
$months = array();
foreach ($blogPosts as $blogPost) {
	$months[] = date('F Y', strtotime($blogPost->get('dateCreated')));
}
$months = array_unique($months);
if(isset($blogPosts[0])){
	$blogPost = $blogPosts[0];
}
$months = array_slice($months, 0, 12);

require('showMessages.php');
?>
<div class="col-md-8">
	<div class="blog-header">
		<div class="hidden-xs">
			<h1 class="blog-title">Welcome to Clash&nbsp;Tracker</h1>
		</div>
		<div class="hidden-sm hidden-md hidden-lg">
			<h1 class="blog-title">Welcome to Clash Tracker</h1>
		</div>
		<p class="lead blog-description">Clan & Player Statistics Tracker for Clash of Clans</p>
	</div>
  	<div class="row">
		<div class="col-sm-12 blog-main">
	  		<div class="blog-post">
				<h2 class="blog-post-title">What is Clash&nbsp;Tracker?</h2>
				<p>Clash&nbsp;Tracker is a site designed for tracking statistics for clans and their members. </p>
				<hr>
				<h3>Clan Statistics</h3>
				<p>Ever want to know who your clan's best attackers are? Or more importantly, who your worst attackers are? Clash&nbsp;Tracker provides the tools to track every players' performance in wars so that Leaders and Co-Leaders know how good their players are.</p>
				<p>By inputting data about your clan's wars, Clash&nbsp;Tracker can keep a detailed, longterm history of your wars. Giving you easy access to statistics about your members' performance.</p>
				<p></p>
				<hr>
				<h3>Player Statistics</h3>
				<p>Including war performance statistics from your clan, Clash&nbsp;Tracker can also keep track your players' raiding by inputting players' Gold Grab, Elixir Escapade and Heroic Heist achievements. These statistics can be recorded on a player level or as an entire clan. (Coming Soon: Automatic Trophy, Donations, Received and Level tracking)</p>
			</div>
			<hr>
			<div class="blog-post">
				<h2 class="blog-post-title">How To Get Started</h2>
				<p>If this is the first time you or any of your clanmates are using Clash&nbsp;Tracker, the very first step is to add your Clan to the website. Go to the Clans page, click Add Clan and enter your Clan Tag. The Clash&nbsp;Tracker system will automagically retrieve general information about your clan from Clash of Clans, including War Wins, Clan Points, Member Information, etc. and keep this information up to date.</p>
				<p>Although it is not necessary to do so, it is recommended to create an account and link your account to your clan and to your player. This can be done in your account settings. Doing so allows you to restrict access to who can input information for your clan and player and reduce incorrect information.</p>
				<p>Once this has been done, you're good to go! You can record loot achievements and add wars to start tracking what you want for your clan and your players. If you need help with anything else, <a href="/help.php">click here.</a></p>
			</div>
			<?if(isset($blogPost)){?>
				<div class="blog-post">
					<h2 class="blog-post-title">Most Recent Update: <?=$blogPost->get('name');?></h2>
					<p class="blog-post-meta"><?=date('F j, Y', strtotime($blogPost->get('dateCreated')));?></p>
					<p><?=$blogPost->get('content');?></p>
				</div>
			<?}?>
		</div>
	</div>
</div>
<br><br><br>
<div class="col-sm-3 col-sm-offset-1 blog-sidebar">
	<div class="sidebar-module sidebar-module-inset">
		<h4>Help Support Us!</h4>
		<p>We don't like asking you for money, so this is the only place you'll see this, but there are costs to keep this site running, so if you like what we're doing don't hesitate to donate a few dollars to keep it going!</p>
		<form class="text-center" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
		</div>
		<div class="sidebar-module sidebar-module-inset">
		<h4>Why create an account?</h4>
		<p>Creating an account lets you link your account with your player and your clan so that you can control access to who has the ability to update their information.</p>
		<?if(!isset($loggedInUser)){?>
			<div class="text-center">
				<a href="/signup.php"><button type="button" class="btn btn-success btn-sm">Sign up</button></a>
				<a href="/login.php"><button type="button" class="btn btn-success btn-sm">Log in</button></a>
			</div>
		<?}?>
		</div>
		<?if(count($months)>0){?>
		<div class="sidebar-module">
			<h4>Past Updates</h4>
			<ol class="list-unstyled">
				<?foreach ($months as $month) {?>
					<li><a href="/blog.php?month=<?=$month;?>"><?=$month;?></a></li>
				<?}?>
			</ol>
		</div>
	<?}?>
	<div class="sidebar-module">
		<h4>Contact Us!</h4>
		<ol class="list-unstyled">
			<li><a href="https://twitter.com/clashsolo"><i class="fa fa-twitter" style="color: #3C90E8;"></i>&nbsp;Twitter</a></li>
		</ol>
	</div>
	<div class="sidebar-module">
		<h4>Found a Bug?</h4>
		<ol class="list-unstyled">
			<li><a href="https://github.com/alexinman/clashtracker/issues"><i class="fa fa-github" style="color: black;"></i>&nbsp;GitHub Issues List</a></li>
		</ol>
	</div>
</div>
<?
require('footer.php');
