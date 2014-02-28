/*
 
global $ibrowser;
$ibrowser=array();
	function infra_browser($agent=false){
		global $ibrowser;
		if(!$agent)$agent=$_SERVER['HTTP_USER_AGENT'];
		$agent=strtolower($agent);
		if(isset($ibrowser[$agent]))return $ibrowser[$agent];
		
		if (preg_match('/msie (\d)/', $agent,$matches)) {
			$name = 'ie ie'.$matches[1];
		}elseif (preg_match('/opera/', $agent)) {
			$name = 'opera';
			if(preg_match('/opera\/9/', $agent)) {
				$name.=' opera9';
			}else if(preg_match('/opera (\d)/', $agent,$matches)){
				$name.=' opera'.$mathces[1];
			}
			if(preg_match('/opera\smini/', $agent)) {
				$name.=' opera_mini';
			}
		}elseif (preg_match('/gecko\//', $agent)){
			$name='gecko';
			if (preg_match('/firefox/', $agent)){
				$name .= ' ff';
				if (preg_match('/firefox\/2/', $agent)){
					$name .= ' ff2';
				}elseif (preg_match('/firefox\/3/', $agent)){
					$name .= ' ff3';
				}
			}
		}elseif (preg_match('/webkit/', $agent)) {
			$name = 'webkit';
			if (preg_match('/chrome/', $agent)) {
				$name .= ' chrome';
			}else{
				$name .= ' safari';
			}
		}elseif (preg_match('/konqueror/', $agent)) {
			$name='konqueror';
		}elseif (preg_match('/flock/', $agent)) {
			$name='flock';
		}else{
			$name='stranger';
		}
		if (!preg_match('/ie/', $name)){
			$name.=' noie';
		}
		if (preg_match('/linux|x11/', $agent)) {
			$name.=' linux';
		}elseif (preg_match('/macintosh|mac os x/', $agent)) {
			 $name.=' mac';
		}elseif (preg_match('/windows|win32/', $agent)) {
			 $name.=' win';
		}
		if(preg_match('/stranger/',$name)){
			$name='';
		}
		$ibrowser[$agent]=$name;
		return $name;
	}
*/
infra.browser=function(agent){//view или agent
	var stor=infra.browser;
	if(typeof(agent=='object'))agent=agent.getAGENT();
	if(!agent)agent='';
	agent=agent.toLowerCase();
	if(stor[agent])return stor[agent];

	var m=agent.match(/msie (\d)/);
	if(m) name = 'ie ie'.m[1];
	else name = 'noie';
	return name;
}
