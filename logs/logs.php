<?php

  define(logDir,'logs/');

  #config end
	date_default_timezone_set("PRC");
	$post = file_get_contents("php://input");
	// echo $GLOBALS['HTTP_RAW_POST_DATA'];
	$ua = $_SERVER["HTTP_USER_AGENT"];
  $client_ip = $_SERVER["REMOTE_ADDR"];
	$method = $_SERVER["REQUEST_METHOD"];
	$referer = isset($_SERVER["HTTP_REFERER"])? $_SERVER["HTTP_REFERER"]:" " ;
	$date = date("g:i a");
	$querystring = $_SERVER["QUERY_STRING"];
	// $cookie = $_SERVER['HTTP_COOKIE'];
	$url = $_SERVER['REQUEST_URI'];
	$str= "[IP:$client_ip]------[Method: $method ----Date: $date ----QUERY: $querystring -----Url:$url----POST:$post ----UA:$ua]\n";

	$str2 = $url.' '.$post."\n";
	$log_name = logDir.$client_ip.'.txt';
	$log_name2 = logDir."log.txt";
	// print $log_name;
	file_put_contents($log_name,$str,FILE_APPEND);
	file_put_contents($log_name2,$str2,FILE_APPEND);
	//if(strstr($ua, 'python')){
		//die(md5(rand()));
	//}
?>
