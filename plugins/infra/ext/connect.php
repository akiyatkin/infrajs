<?php
function &infra_db($debug=false){
	return infra_once('infra_db',function&($debug){
		$config=infra_config();
		if(!$debug)$debug=$config['debug'];
		$config=@$config['mysql'];
		$ans=array();
		if(!$config)return $ans;
		try {
			@$db=new PDO('mysql:host='.$config['host'].';dbname='.$config['database'], $config['user'], $config['password']);
			$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			if($debug){
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}else{
				$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
			} 
				/*array(
				PDO::ATTR_PERSISTENT => true,
				PDO::ATTR_ERRMODE => true,
				PDO::ATTR_ERRMODE=>PDO::ERRMODE_WARNING 
			)*/
			$db->exec("SET CHARACTER SET utf8");
		} catch (PDOException $e) {
			//if($debug)throw $e;
			$db=false;
			/*if(!$debug){
			    print "Error!: " . infra_toutf($e->getMessage()) . "<br/>";
			    die();
			}*/
		}
		return $db;
	},array($debug));
}

?>
