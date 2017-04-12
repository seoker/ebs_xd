<?
	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		die("請勿直接使用絕對路徑");
	if (!$_POST['job'])
	{
		include_once("header.php");
?>
<div class="box">
	<div style="width:100%; padding:5px 0px 5px 0px; background-color:#aaa; text-align:center">Administrator Login</div>
	<div style="margin:10px; text-align:left">
		<form action="?admin" method="post" name="MyForm" onkeydown="if(event.keyCode == 13) this.submit()">
		<input type="hidden" name="job" value="login">
		<div style="clear:both">Enter Password</div>
		<input type="password" name="admpwd" class="inputfield" style="float:left" />
		<input type="button" value="登入" style="height:22px; padding:2px; float:right" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="MyForm.submit()">
		</form>
	</div>
	<div style="clear:both; margin-bottom:10px"></div>
</div>
<?
	}
	else if ($_POST['job'] == "login")
	{
		if (file_exists($user_dat))
			$user_list = split("\n",file_get_contents($user_dat));

		include_once("header.php");

		if ($_POST['admpwd'] != $admin_pwd)
			msgBox("?login", "管理員密碼錯誤");
?>
<div class="box">
	<div style="width:100%; padding:5px 0px 5px 0px; background-color:#aaa; text-align:center">Administrator Login</div>
	<div style="margin:10px; text-align:left">
		<form action="?admin" method="post" name="MyForm" onkeydown="if(event.keyCode == 13) this.submit()">
		<input type="hidden" name="job" value="del">
		<input type="hidden" name="admpwd" value="<?=$_POST['admpwd']?>">
		Choose Player to delete<br>
<?
		if ($user_list)
		{
			foreach ($user_list as $value)
			{
				if (!$value) continue;
				list($user_id, $user_pwd, $user_nickname, $user_proxy, $user_times, $user_lastlogin) = split(";", $value);
				echo "<input type=\"checkbox\" name=\"player[]\" value=\"$user_id\" /><span style='width:150px'> $user_id </span>";
				echo "<font style='font-size:11px; color:#444'>($user_lastlogin)</font><br>";
			}
		}
?>
		<div style="text-align:center; margin-top:10px">
			<input type="button" value="刪除" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="MyForm.submit()">
			<input type="button" value="主頁" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.href='?login'">
		</div>
		</form>
	</div>
</div>
<?
	}
	else if ($_POST['job'] == "del")
	{
		$choose = array();
		$new_user_list = array();
		foreach ($_POST['player'] as $val)
			$choose[$val] = true;
		/* 從使用者名單刪除 */
		if (file_exists($user_dat))
		{
			$user_list = file($user_dat);
			foreach ($user_list as $value)
			{
				list($user_id, $user_pwd, $user_nickname, $user_proxy, $user_times, $user_lastlogin) = split(";", $value);
				if (!$choose[$user_id])
					array_push($new_user_list, $value);
			}
			file_put_contents($user_dat, $new_user_list);
		}
		/* 刪除代理伺服器名單中的名字 */
		$proxy_list = file($proxy_dat);
		for ($i=0; $i<count($proxy_list); ++$i)
		{
			list($sv_name, $sv_url, $user) = split(";", $proxy_list[$i]);
			$proxy_user_list = split(",", $user);
			foreach ($_POST['player'] as $val)
				$proxy_user_list = array_delete($proxy_user_list, $val);
			$proxy_list[$i] = $sv_name . ";" . $sv_url . ";" . join(",", $proxy_user_list) . ";\n";
		}
		file_put_contents($proxy_dat, $proxy_list);
		/* 刪除玩家資料夾 */
		foreach ($_POST['player'] as $id)
		{
			if (!remove_dir("$dat_dir/$id"))
				msgBox("?admin", "無法刪除 $id 資料夾");
		}
		msgBox("?admin", "已成功刪除以下帳號<br>".join("、", $_POST['player']));
	}
	function remove_dir($dir)
	{
		$handle = opendir($dir);
		while (false !== ($item = readdir($handle)))
		{
			if ($item != '.' && $item != '..')
			{
				if (is_dir($dir.'/'.$item))
				{
					if (!remove_dir($dir.'/'.$item))
						return false;
				}
				else
				{
					if (!unlink($dir.'/'.$item))
						return false;
				}
			}
		}
		closedir($handle);
		if (rmdir($dir))
			return true;
		else
			return false;
	}
?>