<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' />
		<meta name="description" content="">
		<meta name="author" content="">
                
		<title>Clash Tracker</title>
                
                <link rel="icon" href="images/clash.png">
                
		<!-- Bootstrap Core CSS -->
		<link href="css/bootstrap.css" rel="stylesheet">
		<link href="css/font-awesome.css" rel="stylesheet" type="text/css">
                
                
		<link href="css/custom-font-icons.css" rel="stylesheet" type="text/css">
		<link href="css/bootstrap-theme.css" rel="stylesheet" type="text/css">
		<link href="css/sticky-footer.css" rel="stylesheet" type="text/css">		
                <link href="css/bootstrap-slider.css" rel="stylesheet" type="text/css">
		<link href="css/blog.css" rel="stylesheet" type="text/css">
		<link href="css/dashboard.css" rel="stylesheet">
		<link href="css/clashtracker.css" rel="stylesheet" type="text/css">
                
                <script src="js/jquery-1.11.3.min.js" type="text/javascript"></script>
                
                <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
                <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
                <!--[if lt IE 9]>
                <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
                <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
                <![endif]-->

	</head>
	<body role="document">
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="/home.php">
						<img alt="Clash&nbsp;Tracker" src="images/clash.png" height="20" width="20">
					</a>
					<a class="navbar-brand" href="/home.php">Clash&nbsp;Tracker</a>
				</div>
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav navbar-right">
                                                <li class="dropdown">
                                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-question-circle" aria-hidden="true"></i>Help<span class="caret"></span></a>
                                                        <ul class="dropdown-menu">
                                                                <li><a href="help.php?article=overview">Overview</a></li>
                                                                <li><a href="help.php?article=wars">Wars</a></li>
                                                                <li><a href="help.php?article=loot">Loot</a></li>
                                                        </ul>
                                                </li>
                                        </ul>
                                        <form class="navbar-form navbar-right" action="searchResults.php" method="GET">
                                                <input type="text" name="query" class="form-control" placeholder="Search...">
                                        </form>
                                        <ul class="nav navbar-nav navbar-right">
						<?if(isset($loggedInUser)){
                                                        if(isset($loggedInUserPlayer)){?>
                                                                <li class="dropdown">
                                                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="glyphicon glyphicon-user"></i><?=$loggedInUserPlayer->get('name');?><span class="caret"></span></a>
                                                                        <ul class="dropdown-menu">
                                                                                <?if(isset($loggedInUserPlayer)){?>
                                                                                        <li><a href="/player.php?playerId=<?=$loggedInUserPlayer->get('id');?>"><i class="fa fa-user" aria-hidden="true"></i>My Player</a></li>
                                                                                <?}
                                                                                if(isset($loggedInUserClan)){?>
                                                                                        <li><a href="/clan.php?clanId=<?=$loggedInUserClan->get('id');?>"><i class="fa fa-shield" aria-hidden="true"></i>My Clan</a></li>
                                                                                <?}else{
                                                                                        if(isset($loggedInUserPlayer)){
                                                                                                $loggedInUserPlayerClan = $loggedInUserPlayer->getClan();
                                                                                                if(isset($loggedInUserPlayerClan)){?>
                                                                                                        <li><a href="/clan.php?clanId=<?=$loggedInUserPlayerClan->get('id');?>"><i class="fa fa-shield" aria-hidden="true"></i>My Clan</a></li>
                                                                                                <?}
                                                                                        }?>
                                                                                <li><a href="/accountSettings.php"><i class="fa fa-cogs" aria-hidden="true"></i>Settings</a></li>
                                                                                <?}?>
                                                                        </ul>
                                                                </li>
                                                                                
                                                        
							<?}
                                                }?>
                                                <li><a href="/players.php"><i class="fa fa-users" aria-hidden="true"></i>Players</a></li>
                                                <li><a href="/clans.php"><i class="fa fa-shield" aria-hidden="true"></i>Clans</a></li>
                                                <?if(isset($loggedInUserPlayer)){?>
                                                        <li><a href="/processLogout.php"><i class="fa fa-sign-out" aria-hidden="true"></i>Log Out</a></li>
                                                                <?if($loggedInUser->isAdmin()){?>
                                                                        <li><a href="/dev.php"><i class="fa fa-wrench" aria-hidden="true"></i>Dev</a></li>
                                                        <?}
                                                }else{?>
                                                        <li><a href="/login.php"><i class="fa fa-sign-in" aria-hidden="true"></i>Log In</a></li>
                                                <?}?>
					</ul>
				</div>
			</div>
		</nav>
		<div class="container-fluid">