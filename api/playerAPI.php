<?
class PlayerAPI extends API{
	public function getPlayerInformation($tag){
		$extension = 'players/' . urlencode($tag);
		return $this->request($extension);
	}
}