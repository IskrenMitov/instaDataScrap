<?php
/*
Developer - Atul Singh
Github - https://github.com/iamatulsingh
Telegram - https://t.me/developeratul

@license     Code and contributions have 'MIT License'
             More details: LICENSE

* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software with proper credit to original author.

*/
class InstaData{
	
	public function getData($username){
		$options  = array('http' => array('user_agent' => 'Mozilla/5.0 
							(Windows NT 6.1; WOW64) 
							AppleWebKit/537.36 (KHTML, like Gecko) 
							Chrome/47.0.2526.111 
							Safari/537.36 
							OPR/34.0.2036.50'
						)
					);
					
		$context  = stream_context_create($options);
		error_reporting(~E_WARNING);
		if(($instaLink = file_get_contents('https://www.instagram.com/' . $username, false, $context)) == false){
			echo "Error: Link not found, user-id is invalid";
			exit;
		}
		
		return $instaLink;
	}
	
	public function fetchUserDetails($username){
		
		$instaLink = $this->getData($username);
		$instaIDPattern = '/window._sharedData = (.*)/';
		if (!preg_match($instaIDPattern, $instaLink, $matches)) {
			exit;
		}
		$trim_data = substr($matches[1], 0, -10);
		$json_output = json_decode($trim_data,true);
		$json_output = $json_output['entry_data']['ProfilePage']['0']['graphql']['user'];
		return $json_output;
	}
	
	public function fetchAccountDetails($username){
		
		$details = "";
		$instaLink = $this->getData($username);
		$detailsPattern = '/meta content=(.\d+)(.*)/';
		if (preg_match($detailsPattern, $instaLink, $res)) {
			if (strpos($res, '118') !== false) {
				$details = $res[1]. "" .$res[2];
			}
		}
		$input_line = substr($details, 1, -23);
		$userDetails = preg_split("/, /", $input_line);
		
		return $userDetails;
	}
	
	public function getTimeLine($username){
		$instaLink = $this->getData($username);
		$instaIDPattern = '/window._sharedData = (.*)/';
		if (!preg_match($instaIDPattern, $instaLink, $matches)) {
			exit;
		}
		$trim_data = substr($matches[1], 0, -10);
		$json_output = json_decode($trim_data,true);
		$json_output = $json_output['entry_data']['ProfilePage']['0']['graphql']['user']['edge_owner_to_timeline_media']['edges'];
		$count = count($json_output);
		$timeLine = Array();
		for($i=0;$i<$count;$i++){
			$post_txt = $json_output[$i]['node']['edge_media_to_caption']['edges']['0']['node']['text'];
			$post_img = $json_output[$i]['node']['display_url'];
			$post_likes = $json_output[$i]['node']['edge_liked_by']['count'];
			$post_comments = $json_output[$i]['node']['edge_media_to_comment']['count'];
			$post_time = $json_output[$i]['node']['taken_at_timestamp'];
			$date = new DateTime("@$post_time");
			$timeLine[$i]['post_img'] = $post_img;
			$timeLine[$i]['post_txt'] = $post_txt;
			$timeLine[$i]['post_time'] = $date->format('Y-m-d H:i:s');
			$timeLine[$i]['post_likes'] = $post_likes;
			$timeLine[$i]['post_comments'] = $post_comments;
		}
		return Array('data'=>$timeLine,'count'=>$count);
	}
	
	public function getUserDetails($username){
		
		$json_output = $this->fetchUserDetails($username);
		$userData = array();
		$userData['img'] = $json_output['profile_pic_url_hd'];
		$userData['full_name'] = $json_output['full_name'];
		$userData['username'] = $json_output['username'];
		$userData['is_verified'] = "false";
		if($json_output['is_verified'])
			$userData['is_verified'] = "true";
		else
			$userData['is_verified'] = "false";
		$userData['id'] = $json_output['id'];
		$userData['instaUrl'] = "https://instagram.com/".$json_output['username'];
		$json_userData = json_encode($userData);
		
		return $json_userData;
	}
	
	public function getAccountDetails($username){
		
		$userDetails = $this->fetchAccountDetails($username);
		$accountData = array();
		$accountData['followers'] = $userDetails[0];
		$accountData['follow'] = $userDetails[1];
		$temp = preg_split("/ -/", $userDetails[2]);
		$accountData['posts'] = $temp[0];
		$json_accountData = json_encode($accountData);
		
		return $json_accountData;
	}
}
