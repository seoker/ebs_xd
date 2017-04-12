<?
	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		die("請勿直接使用絕對路徑");
	list($msec_1, $sec_1) = split(" ", microtime());
	$retdata = myPost($ebs_url."/ebs.cgi", $_SERVER['HTTP_USER_AGENT'], $_POST['sv'], NULL);

	if (!$retdata || !preg_match("/Evilfox EBS/", $retdata))
		$response = "無法連線";
	else
	{
		list($msec_2, $sec_2) = split(" ", microtime());
		$response = floor(($sec_2+$msec_2-$sec_1-$msec_1)*1000)."ms";
	}

	echo iconv("big5","UTF-8", $response);
?>
