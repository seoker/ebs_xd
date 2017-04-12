<?
	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		die("請勿直接使用絕對路徑");

	$ebs_id = $_COOKIE['id'];
	$ebs_pwd = $_COOKIE['pwd'];

	if (!($User = checkpwd($ebs_id, $ebs_pwd, $user_dat)))
	{
		echo iconv("big5","UTF-8","帳號或密碼不存在");
		exit;
	}

	/* 開啟 pattern 比對清單 */
	$pattern = split("\n", file_get_contents($pattern_dat));

	$data = array(
		"cmd"=>"MAIN_PAGE",
		"pname"=>$ebs_id,
		"pass"=>$ebs_pwd
	);
	if (!($retdata = myPost($ebs_url."/kaigisitu.cgi", NULL, $User[3], $data)))
	{
		echo iconv("big5","UTF-8","無法連接伺服器");
		exit;
	}
	preg_match("/$pattern[41]/", $retdata, $regs);
	preg_match_all("/<option>([^<>]+)/", $regs[1], $test);
	/* 取得所有玩家列表 */
	$opp = array();
	for ($i=0; $i<count($test[1]); ++$i)
		$opp[$test[1][$i]] = true;

	/* 攻擊名單處理 */
	$list_files = array();
	for ($i=0; $i<5; ++$i)
		array_push($list_files, "$dat_dir/$ebs_id/list".($i+1).".dat");

	/* 處理錯誤玩家名字 */
	$notfound = array();
	foreach ($list_files as $list_file)
	{
		if (!file_exists($list_file) || !filesize($list_file))
			continue;
		$opplist = split("\n", file_get_contents($list_file));
		$new_opplist = array();
		foreach ($opplist as $i)
		{
			if ($opp[$i])
				array_push($new_opplist, $i);
			else
				array_push($notfound, $i);
		}
		file_put_contents($list_file, join("\n", $new_opplist));
	}

	echo iconv("big5","UTF-8","名單更新完畢");
	if (count($notfound) > 0)
		echo iconv("big5","UTF-8","\n\n刪除以下帳號：\n" . join("\n", $notfound));
	else
		echo iconv("big5","UTF-8","\n\n沒有異動");
?>
