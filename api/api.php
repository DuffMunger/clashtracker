<?php
class API{
	private $headers;

	public function __construct(){
		$this->ip = trim(shell_exec("dig +short myip.opendns.com @resolver1.opendns.com"));
	}

	protected function request($extension){
		$url = 'https://api.clashofclans.com/v1/' . $extension;
		$curl = curl_init();
		$creds = null;
		if(!DEVELOPMENT){
			$env = $this->determineProxy();
			$creds = parse_url($_ENV[$env]);
			$proxyUrl = $creds['host'].":".$creds['port'];
			$proxyAuth = $creds['user'].":".$creds['pass'];
			curl_setopt($curl, CURLOPT_PROXY, $proxyUrl);
			curl_setopt($curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
			curl_setopt($curl, CURLOPT_PROXYUSERPWD, $proxyAuth);
		}
		$apiKey = new apiKey($this->ip);
		$this->headers = array('Accept: application/json', 'authorization: Bearer ' . $apiKey->get('apiKey'));
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		error_log('API Call: ' . $url);
		$rawResult = curl_exec($curl);
		$result = json_decode($rawResult);
		curl_close($curl);
		if($result->reason){
			throw new APIException($result->reason, $result->message);
		}
		if(isset($result)){
			return $result;
		}else{
			throw new APIException('Nothing was returned from Clash of Clans API.', 'No Result');
		}
	}

	private function determineProxy(){
		global $db;
		$procedure = buildProcedure('p_proxy_request_get');
		if(($db->multi_query($procedure)) === TRUE){
			$results = $db->store_result();
			while ($db->more_results()){
				$db->next_result();
			}
			$currentMonth = date('F');
			if ($results->num_rows) {
				while ($proxyObj = $results->fetch_object()) {
					$month = $proxyObj->month;
					if($month != $currentMonth){
						$count = 0;
					}else{
						$count = $proxyObj->count;
					}
					$limit = $proxyObj->monthly_limit;
					if($limit - $count > 0){
						$env = $proxyObj->env;
						$this->updateProxyCount($env, $count+1);
						$this->ip = $proxyObj->ip;
						return $env;
					}
				}
			}
			throw new APIException('noRequestsLeft', 'The request limits for all available proxies have been reached.');
		}else{
			throw new SQLQueryException('The database encountered an error. ' . $db->error);
		}
	}

	public function updateProxyCount($env, $count){
		global $db;
		$procedure = buildProcedure('p_proxy_request_count_update', $env, $count, date('F'));
		if(($db->multi_query($procedure)) === TRUE){
			while ($db->more_results()){
				$db->next_result();
			}
		}else{
			throw new SQLQueryException('The database encountered an error. ' . $db->error);
		}
	}

	public static function getProxyInformation(){
		global $db;
		$procedure = buildProcedure('p_proxy_request_get');
		if(($db->multi_query($procedure)) === TRUE){
			$results = $db->store_result();
			while ($db->more_results()){
				$db->next_result();
			}
			$currentMonth = date('F');
			$proxies = array();
			if ($results->num_rows) {
				while ($proxyObj = $results->fetch_object()) {
					$proxy = new stdClass();
					$proxy->env = $proxyObj->env;
					$proxy->month = $proxyObj->month;
					$proxy->limit = $proxyObj->monthly_limit;
					$proxy->ip = $proxyObj->ip;
					if($proxy->month != $currentMonth){
						$proxy->month = $currentMonth;
						$proxy->count = 0;
						$api = new API();
						$api->updateProxyCount($proxy->env, $proxy->count);
					}else{
						$proxy->count = $proxyObj->count;
					}
					$proxies[] = $proxy;
				}
			}
			return $proxies;
		}else{
			throw new SQLQueryException('The database encountered an error. ' . $db->error);
		}
	}

	public static function addENV($env, $limit, $ip){
		global $db;
		$procedure = buildProcedure('p_proxy_env_add', $env, $limit, $ip, date('F'));
		if(($db->multi_query($procedure)) === TRUE){
			while ($db->more_results()){
				$db->next_result();
			}
		}else{
			throw new SQLQueryException('The database encountered an error. ' . $db->error);
		}
	}
}