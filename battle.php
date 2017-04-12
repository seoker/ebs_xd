<?
	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		echo "<script language='Javascript'>alert('請勿直接使用絕對路徑');</script>";

	$ebs_id = $_COOKIE['id'];
	$ebs_pwd = $_COOKIE['pwd'];

	if (!($User = checkpwd($ebs_id, $ebs_pwd, $user_dat)))
		errMsg("帳號或密碼錯誤，請重新登入");

	/* 開啟 pattern 比對清單 */
	$pattern = split("\n",file_get_contents($pattern_dat));

	/* 讀取攻擊資訊 */
	$config_data = split(";", file_get_contents("$dat_dir/$ebs_id/config"));
	/*
		$config_data[0] = ID;
		$config_data[1] = PWD;
		$config_data[2] = Proxy;
		$config_data[3] = HP;
		$config_data[4] = AP;
		$config_data[5] = 間隔選項;
		$config_data[6] = 戰鬥指令;
		$config_data[7] = 沒死繼續打;
		$config_data[8] = 攻擊低於多少刪除該對象;
		$config_data[9] = 不攻擊線上玩家;
		$config_data[10] = 週期購買配件;
		$config_data[11] = 間隔時間;
		$config_data[12] = 武器ＡＰ;
		$config_data[13] = 下一個對手;
	*/
	/* 讀取攻擊名單 */
	$list_file = "$dat_dir/$ebs_id/templist";
	$opp_list = split("\n", file_get_contents($list_file));

	/* 取得線上參賽者 */
	if ($config_data[9])
	{
		$retdata = myPost($ebs_url."/member.cgi", $_SERVER['HTTP_USER_AGENT'], $User[3], NULL);
		preg_match_all("/$pattern[19]/", $retdata, $getRegs);
		for ($i=0; $i<count($getRegs[1]); ++$i)
			$online[$getRegs[1][$i]] = true;
	}

	/* 選取國家(站管國)以取得時間資訊 */
	$postdata = array(
		"cmd" => "BATTLEPLUS_1",
		"CNTRY" => "站管國",
		"pname" => $ebs_id,
		"pass" => $ebs_pwd
	);
	$retdata = myPost($ebs_url."/ebs.cgi", $_SERVER['HTTP_USER_AGENT'], $User[3], $postdata);

	if (preg_match("/AP不足/", $retdata))
		sendMsg("AP不足，等候","秒", 8);
	else if (preg_match("/體力不足/", $retdata))
		sendMsg("體力不足，等候","秒", 5);
	else if (preg_match("/掛彩中/", $retdata))
		sendMsg("掛彩中，等候","秒", floor($config_data[3]/(20+$config_data[3]*0.005)/2));
	else if (preg_match("/$pattern[9]/", $retdata, $getRegs))
	{
		$postdata = array(
			"cmd" => "CHECKW",
			"pname" => $ebs_id,
			"pass" => $ebs_pwd,
			"check0" => $getRegs[1],
			"check1" => $getRegs[1],
			"ctime" => $getRegs[2]
		);
		myPost($ebs_url."/ebs.cgi", $_SERVER['HTTP_USER_AGENT'], $User[3], $postdata);
		/* 紀錄 */
		recordFile("$dat_dir/$ebs_id/history", array(2, date("Y-m-d H:i:s"), $getRegs[1]));
		sendMsg("驗證完畢，等候","秒進行下一場戰鬥",5);
	}
	/* 取得時間資訊 */
	else if (preg_match("/$pattern[17]/", $retdata, $getRegs))
		$timestamp = $getRegs[1];
	else
	{
		file_put_contents($err_dir."/".$ebs_id.".htm", $retdata);
		/* 紀錄 */
		recordFile("$dat_dir/$ebs_id/history", array(3, date("Y-m-d H:i:s")));
		errMsg("未知情況，停止運作　<a href='".$err_dir."/".$ebs_id.".htm' tatget='_blank'>傳回頁面</a>");
	}

	/* 戰鬥 */
	list($vs_name, $vs_wait) = split(";", $opp_list[$config_data[13]]);
	/* 如果在線上 */
	if ($config_data[9] && $online[$vs_name])
	{
		/* 將其回復時間調整並回存 */
		$opp_list[$config_data[13]] = $vs_name.";".(time()+60000);
		file_put_contents($list_file, join("\n", $opp_list));
		$nextopp = chooseNextOpp($opp_list, $config_data, false);
		updateConfig("$dat_dir/$ebs_id/config", $config_data, array(13=>$nextopp[0]));
		list($next_name, $next_wait) = split(";", $opp_list[$nextopp[0]]);
		echo "<script language=\"Javascript\">window.parent.main.document.getElementById('ebs_nextone').innerText='".$next_name."'</script>";
		sendMsg("<b>".$vs_name."</b>正在線上，等候", "秒", $nextopp[1]);
	}
	$postdata = array(
		"cmd" => "BATTLEPLUS_2",
		"CustomCheck" => "",
		"pname" => $ebs_id,
		"pass" => $ebs_pwd,
		"b_mode" => "",
		"check" => $timestamp+21,
		"vsname" => $vs_name,
		"mode" => $config_data[6]
	);
	$retdata = myPost($ebs_url."/ebs.cgi", $_SERVER['HTTP_USER_AGENT'], $User[3], $postdata);
	if (preg_match("/對方已經掛了/", $retdata))
	{
		/* 計算復活時間並回存 */
		if ($config_data[5] == 4)
		{
			$opp_list[$config_data[13]] = $vs_name.";".(time()+120);
			file_put_contents($list_file, join("\n", $opp_list));
		}
		/* 選取下一個對手 */
		$nextopp = chooseNextOpp($opp_list, $config_data, false);
		updateConfig("$dat_dir/$ebs_id/config", $config_data, array(13=>$nextopp[0]));
		list($next_name, $next_wait) = split(";", $opp_list[$nextopp[0]]);
		echo "<script language=\"Javascript\">window.parent.main.document.getElementById('ebs_nextone').innerText='".$next_name."'</script>";
		sendMsg("對方尚未復活，等候", "秒進行下一場戰鬥", $nextopp[1]);
	}
	else if (preg_match("/AP不足/", $retdata))
		sendMsg("AP不足，等候","秒", 8);
	else if (preg_match("/$pattern[11]/", $retdata, $getRegs))
	{
		/* 取得雙方傷害 */
		$my_hp1 = $getRegs[1];
		$my_hp2 = $getRegs[2];
		$opp_hp1 = $getRegs[3];
		$opp_hp2 = $getRegs[4];
		/* 取得雙方武器資訊 */
		preg_match_all("/$pattern[21]/", $retdata, $getRegs);
		$my_weapon = $getRegs[1][0]."(Level.".$getRegs[2][0].")";
		$opp_weapon = $getRegs[1][1]."(Level.".$getRegs[2][1].")";
		/* 判斷輸贏 */
		if ($opp_hp2 == 0)
			$result = 1;
		else if ($my_hp2 == 0)
			$result = 3;
		else if (preg_match("/$vs_name嚴重受傷/", $retdata))
			$result = 4;
		else if (preg_match("/$ebs_id嚴重受傷/", $retdata))
			$result = 5;
		else
			$result = 2;
		/* 取得武器AP */
		preg_match("/$pattern[25]/", $retdata, $getRegs);
		$use_ap = $getRegs[1];
		/* 取得雙方ＨＰ */
		preg_match_all("/$pattern[27]/", $retdata, $getRegs);
		$my_hp = $getRegs[1][0];
		$opp_hp = $getRegs[1][1];
		/* 取得戰鬥經驗及金錢 */
		preg_match("/$pattern[29]/", $retdata, $getRegs);
		$getexp = $getRegs[1];
		$getmoney = $getRegs[2];
		$getwexp = $getRegs[3];
		/* 取得其他訊息 */
		$othermsg = "";
		if (preg_match("/$ebs_id 的等級上升/", $retdata))
		{
			preg_match_all("/$pattern[31]/", $retdata, $getRegs);
			$othermsg.= "<font color=#f7e957>等級上升。" . join("、", $getRegs[1]) . "提升</font><br>";
		}
		preg_match_all("/$pattern[33]/", $retdata, $getRegs);
		if ($getRegs[1][0])
			$othermsg.= "<font color=#FFFF00>".$getRegs[1][0]."</font>";
		preg_match_all("/$pattern[35]/", $retdata, $getRegs);
		if ($getRegs[1][0])
			$othermsg.= "<font color=#0066FF>".$getRegs[1][0]."</font>";
		if (preg_match_all("/對手([^\n<\/>]+)能力下降/", $retdata, $getRegs))
			$othermsg.= "<font color='#8000ff'>對手".join("、", $getRegs[1])."下降</font><br>";
		if (preg_match("/道具獲得/", $retdata))
			$othermsg.= "<font color=#f7e957>道具獲得。<br></font>";
		/* 取得雙方 attack & hit */
		preg_match_all("/$pattern[37]/", $retdata, $damageRegs);
		$match = preg_match_all("/$pattern[23]/", $retdata, $getRegs);
		if ($match == 0)
		{
			$my_hit = $opp_hit = 0;
			$my_attack = $opp_attack = "?";
			$my_damage = $opp_damage = 0;
		}
		else if ($match == 1)
		{
			if (preg_match("/$pattern[39]/", $retdata) == 1)
			{
				$my_hit = $getRegs[2][0];
				$my_attack = $getRegs[1][0];
				$my_damage = $damageRegs[1][0];
				$opp_hit = 0;
				$opp_attack = "?";
				$opp_damage = 0;
			}
			else
			{
				$my_hit = 0;
				$my_attack = "?";
				$my_damage = 0;
				$opp_hit = $getRegs[2][0];
				$opp_attack = $getRegs[1][0];
				$opp_damage = $damageRegs[1][0];
			}
		}
		else
		{
			$my_hit = $getRegs[2][0];
			$my_attack = $getRegs[1][0];
			$my_damage = $damageRegs[1][0];
			$opp_hit = $getRegs[2][1];
			$opp_attack = $getRegs[1][1];
			$opp_damage = $damageRegs[1][1];
		}

		/* 處理攻擊低於某值或被打掛，刪除對象 */
		if ($config_data[8] && ($result == 3 || ($my_hit > 0 && $my_damage < $config_data[8])))
			$opp_list = array_delete_key($opp_list, $config_data[13]);
		/* 計算復活時間並回存 */
		else if ($config_data[5] == 4 && $result == 1)
			$opp_list[$config_data[13]] = $vs_name.";".(time()+floor($opp_hp/($opp_hp*0.005+20)));
		file_put_contents($list_file, join("\n", $opp_list));
		/* 選取下一場對手並輸出訊息 */
		if ($result != 1 && $config_data[7])
			$config_data[13] -= 1;
		$nextopp = chooseNextOpp($opp_list, $config_data, true);
		updateConfig("$dat_dir/$ebs_id/config", $config_data, array(12=>$use_ap, 13=>$nextopp[0]));
		list($next_name, $next_wait) = split(";", $opp_list[$nextopp[0]]);
		/* 紀錄 */
		recordFile("$dat_dir/$ebs_id/history", array(1, date("Y-m-d H:i:s"), $vs_name, $my_hp1, $my_hp2, $my_hp, $opp_hp1, $opp_hp2, $opp_hp, $my_attack, $my_hit, $my_damage,
		$opp_attack, $opp_hit, $opp_damage, $getexp, $getmoney, $getwexp, $result, $othermsg, $my_weapon, $opp_weapon));
		/* 輸出 */
?>
<script language="Javascript">
window.parent.main.battleReturn("<?=$vs_name?>", "<?=$my_hp1?>", "<?=$my_hp2?>", "<?=$my_hp?>", "<?=$opp_hp1?>", "<?=$opp_hp2?>", "<?=$opp_hp?>",
		"<?=$my_attack?>", "<?=$my_hit?>", "<?=$my_damage?>","<?=$opp_attack?>", "<?=$opp_hit?>", "<?=$opp_damage?>", 
		"<?=$getexp?>", "<?=$getmoney?>", "<?=$getwexp?>", "<?=$result?>", "<?=$othermsg?>", "<?=$my_weapon?>", "<?=$opp_weapon?>", "<?=$next_name?>");
</script>
<?
		if ($result == 3)
			sendMsg("掛彩中，等候", "秒", floor($config_data[3]/($config_data[3]*0.005+20)));
		else
			sendMsg("等候", "秒進行下一場戰鬥", $nextopp[1]);
	}
	/* 查無對手 */
	else
	{
		/* 刪除該對手並回存 */
		$opp_list = array_delete_key($opp_list, $config_data[13]);
		file_put_contents($list_file, join("\n", $opp_list));
		/* 紀錄 */
		recordFile("$dat_dir/$ebs_id/history", array(4, date("Y-m-d H:i:s"), $vs_name));
		/* 選取下一個對手 */
		$nextopp = chooseNextOpp($opp_list, $config_data, false);
		updateConfig("$dat_dir/$ebs_id/config", $config_data, array(13=>$nextopp[0]));
		list($next_name, $next_wait) = split(";", $opp_list[$nextopp[0]]);
		echo "<script language=\"Javascript\">window.parent.main.document.getElementById('ebs_nextone').innerText='".$next_name."'</script>";
		sendMsg("無此對手<b>".$vs_name."</b>，刪除之，等候","秒", 2);
	}

/* 副程式 */
	/* 紀錄 */
	function recordFile($record_file, $data)
	{
		$recordData = file($record_file) or array();
		while (count($recordData) >= 1000)
			array_shift($recordData);
		array_push($recordData, join(";", $data)."\n");
		file_put_contents($record_file, $recordData);
	}
	/* 選取下一場對手 */
	function chooseNextOpp($opplist, $config, $flag)
	{
		$count_list = count($opplist);
		switch ($config[5])
		{
			case '1':
				$n = ($config[13]+1) % $count_list;
				$waitTime = ($flag) ? $config[11] : 2;
				break;
			case '2':
				$n = ($config[13]+1) % $count_list;
				list($r1, $r2) = split(",", $config[11]);
				$waitTime = ($flag) ? rand($r1, $r2) : 2;
				break;
			case '3':
				$n = ($config[13]+1) % $count_list;
				$waitTime = ($flag) ? floor(($config[12]/($config[12]*0.002+25))/2) : 2;
				break;
			case '4':
				$m = 0;
				$n = -1;
				for ($i=0; $i<$count_list; ++$i)
				{
					list($vs_name, $vs_wait) = split(";", $opplist[$i]);
					if (!$m || $vs_wait < $m)
					{
						$m = $vs_wait;
						$n = $i;
					}
				}
				$waitTime = $vs_wait - time() + 1;
				if ($waitTime < 2) $waitTime = 2;
				break;
			default:
				$waitTime = 2;
				$n = ($config[13]+1) % $count_list;
		}
		return array($n, $waitTime);
	}
	/* 更新 config */
	function updateConfig($con_file, $con_dat, $update)
	{
		foreach ($update as $k=>$v)
			$con_dat[$k] = $v;
		file_put_contents($con_file, join(";", $con_dat));
	}
	/* 傳送需要等待時間的 message */
	function sendMsg($text1, $text2, $sec)
	{
?>
<script language="Javascript">
	window.parent.main.remaining("<?=$text1?>","<?=$text2?>",<?=$sec?>);
	setTimeout("location.href='?battle&time='+Math.random();", <?=$sec*1000?>);
</script>
<?
		exit;
	}
	/* 傳送停止程式的 message */
	function errMsg($msg)
	{
?>
<script language="Javascript">
	window.parent.main.document.getElementById('ebs_status').innerHTML = "<?=$msg?>";
</script>
<?
		exit;
	}
?>