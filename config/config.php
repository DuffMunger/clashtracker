<?
// Time values in seconds
define('MINUTE', 60);
define('HOUR', 3600);
define('DAY', 86400);
define('WEEK', 604800);
define('MONTH', 2629743);
define('YEAR', 31556926);

define('NO_ACCESS', 'You do not have access to that page.');

$dir = str_replace('/config', '', __DIR__);
define('APPROOT', $dir);

date_default_timezone_set('Europe/Amsterdam');
session_start();

$classes = scandir(APPROOT . '/classes');
foreach ($classes as $key => $class) {
	if(strpos($class, '.php') !== false){
		include(APPROOT . '/classes/' . $class);
		$classes[$key] = substr($class, 0, -4);
	}else{
		unset($classes[$key]);
	}
}
include(APPROOT . '/exceptions/clashTrackerException.php');
$classes = array_values($classes);
$exceptions = scandir(APPROOT . '/exceptions');
foreach ($exceptions as $key => $exception) {
	if(strpos($exception, '.php') !== false && strpos($exception, 'clashTracker') === false){
		include(APPROOT . '/exceptions/' . $exception);
		$exceptions[$key] = substr($exception, 0, -4);
	}else{
		unset($exceptions[$key]);
	}
}
$exceptions = array_values($exceptions);
$apiClasses = scandir(APPROOT . '/api');
foreach ($apiClasses as $key => $apiClass) {
	if(strpos($apiClass, '.php') !== false){
		include(APPROOT . '/api/' . $apiClass);
		$apiClasses[$key] = substr($apiClass, 0, -4);
	}else{
		unset($apiClasses[$key]);
	}
}
$apiClasses = array_values($apiClasses);

define('HEROKU', isset($_ENV['HEROKU']));

define('PRODUCTION', isset($_ENV['PRODUCTION']));
define('STAGING', isset($_ENV['STAGING']));
define('DEVELOPMENT', !PRODUCTION && !STAGING);
define('RHITNL', true);
if(RHITNL){
	//	Configuration for the MySQL Local Server
	define('DBHOST', 'localhost');
	define('DBNAME', 'dbname');
	define('DBUSER', 'dbuser');
	define('DBPASS', 'dbpass');
}else{
	$url = parse_url($_ENV['CLEARDB_DATABASE_URL']);
	define('DBHOST', $url['localhost']);
	define('DBNAME', 'dbname');
	define('DBUSER', 'dbuser');
	define('DBPASS', 'dbpass');
}

// Create connection
$db = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);

//smtp email settings
$mailFrom = "sender";
$smtpServer = "localhost";
$smtpUser = "user";
$smtpPassword = "pass";
$smtpPort = "25"; //set as you need. 25 is default
$smtpSSL = false; //port 25 should be false. port 465 should be true

//default sender addresses
$senderContactMe = 'john@doe.com'; //set email address for contactform mail
$senderSignup = 'john@doe.com'; //set email address for signup mail
$senderPassword = 'john@doe.com'; //set email address for password forgot mail

//