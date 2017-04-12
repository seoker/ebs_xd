<?
	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		die("請勿直接使用絕對路徑");

	$ebs_id = $_COOKIE['id'];
	$ebs_pwd = $_COOKIE['pwd'];

	if (!($User = checkpwd($ebs_id, $ebs_pwd, $user_dat)))
		errmsg("big5","UTF-8","帳號或密碼不存在");

	/* 讀取攻擊資訊 */
	$config_data = split(";", file_get_contents("$dat_dir/$ebs_id/config"));

	/* 開啟 pattern 比對清單 */
	$pattern = split("\n", file_get_contents($pattern_dat));

	/* 如果選取武器不為目前武器，則更換之 */
	if ($_GET['weapon'] != 0)
	{
		$postdata = array(
			"cmd" => "EB_SOUKO",
			"pname" => $ebs_id,
			"pass" => $ebs_pwd
		);
		if (!($retdata = myPost($ebs_url."/ebs.cgi", $_SERVER['HTTP_USER_AGENT'], $User[3], $postdata)))
			errmsg("無法連接伺服器");

		if (!preg_match("/$pattern[5]/", $retdata, $getRegs))
			errmsg("發生問題");

		$postdata = array(
			"cmd" => "EB_GOUSEI3",
			"Gmode" => "裝備變更",
			"pname" => $ebs_id,
			"pass" => $ebs_pwd,
			"date" => $getRegs[1],
			"check" => $getRegs[2],
			"check8" => $getRegs[3],
			"check9" => $getRegs[4],
			"ireru" => $_GET['weapon']
		);

		myPost($ebs_url."/ebs.cgi", $_SERVER['HTTP_USER_AGENT'], $User[3], $postdata);
		echo iconv("big5","UTF-8","已更換武器");
	}
	else
		echo iconv("big5","UTF-8","不需更換武器");

	function errmsg($msg)
	{
		echo iconv("big5","UTF-8", $msg);
		exit;
	}
?>