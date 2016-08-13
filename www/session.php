<?
if(isset($_SESSION['user_id'])){
	$userId = $_SESSION['user_id'];
	try{
		$loggedInUser = new User($userId);
		$loggedInUserPlayer = $loggedInUser->get("player");
		$loggedInUserClan = $loggedInUser->get("clan");
	}catch(Exception $e){
		$loggedInUser = null;
		$loggedInUserPlayer = null;
		$loggedInUserClan = null;
		$_SESSION['curError'] = 'No user with id ' . $userId . ' found.';
		$_SESSION['user_id'] = null;
		header('Location: /login.php');
		exit;
	}
}