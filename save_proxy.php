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
	if (file_exists($user_dat))
	{
		/* 更新使用者資料 */
		$user_list = file($user_dat);
		for ($i=0; $i<count($user_list); ++$i)
		{
			list($user_id, $user_pwd, $user_nickname, $user_proxy, $user_times, $user_lastlogin) = split(";", $user_list[$i]);
			if ($user_id == $ebs_id)
				$user_list[$i] = $user_id . ";" . $user_pwd . ";" . $user_nickname . ";" . $_POST['sv'] . ";" . $user_times . ";" . $user_lastlogin . ";\n";
		}
		file_put_contents($user_dat, $user_list);
		/* 更新代理伺服器列表 */
		$proxy_list = file($proxy_dat);
		for ($i=0; $i<count($proxy_list); ++$i)
		{
			list($sv_name, $sv_url, $user) = split(";", $proxy_list[$i]);
			if ($user)
				$proxy_user_list = split(",", $user);
			else
				$proxy_user_list = array();
			$proxy_user_list = array_delete($proxy_user_list, $ebs_id);
			if ($sv_url == $_POST['sv'])
				array_push($proxy_user_list, $ebs_id);
			$proxy_list[$i] = $sv_name . ";" . $sv_url . ";" . join(",", $proxy_user_list) . ";\n";
		}
		file_put_contents($proxy_dat, $proxy_list);
	}
	else
	{
		echo iconv("big5","UTF-8","使用者資料檔不存在");
		exit;
	}

	echo iconv("big5","UTF-8","更改代理伺服器完成");
?>
