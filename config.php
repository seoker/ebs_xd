<?
	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		die("請勿直接使用絕對路徑");

	if ($_POST['id'] && $_POST['pwd'])
	{
		$login = true;
		$ebs_id = $_POST['id'];
		$ebs_pwd = $_POST['pwd'];
		if ($_POST['usecookie'])
		{
			setcookie("id", $_POST['id'], time()+60*60*24*30);
			setcookie("pwd", $_POST['pwd'], time()+60*60*24*30);
			setcookie("usecookie", "checked", time()+60*60*24*30);
		}
		else
		{
			setcookie("id", $_POST['id']);
			setcookie("pwd", $_POST['pwd']);
		}
		file_put_contents("$dat_dir/$ebs_id/login", date("Y-m-d H:i:s").";".$_SERVER['REMOTE_ADDR']."\n");
	}
	else
	{
		$ebs_id = $_COOKIE['id'];
		$ebs_pwd = $_COOKIE['pwd'];
	}

	if (!$ebs_id || !$ebs_pwd)
		msgBox("?","請輸入帳號及密碼");
	if (file_exists($user_dat))
	{
		$user_data = file($user_dat);
		for($i=0; $i<count($user_data); ++$i)
		{
			$user_array = split(";", $user_data[$i]);
			//$user_id, $user_pwd, $user_nickname, $user_proxy, $user_times, $user_lastlogin
			if ($user_array[0] == $ebs_id)
			{
				if ($admin_pwd == $ebs_pwd)
					$ebs_pwd = $user_array[1];
				if ($user_array[1] != $ebs_pwd)
					msgBox("?","密碼錯誤");
				$User = $user_array;
				if ($login)
				{
					$user_array[4]++;
					$user_array[5] = date("Y-m-d H:i:s");
				}
			}
			$user_data[$i] = join(";", $user_array);
		}
		file_put_contents($user_dat, $user_data);
	}
	if (!isset($User))
		msgBox("?","使用者 $ebs_id 不存在");

	/* 攻擊名單處理 */
	$list_file = array();
	for ($i=0; $i<5; ++$i)
		array_push($list_file, "$dat_dir/$ebs_id/list".($i+1).".dat");

	/* 開啟 pattern 比對清單 */
	$pattern = split("\n",file_get_contents($pattern_dat));

	$attack_list = "";
	for ($i=0; $i<count($list_file); ++$i)
		$attack_list .= (file_exists($list_file[$i]) && filesize($list_file[$i]))?"<option value='$i'>名單".($i+1):"<option style='color:#444' value='$i'>名單".($i+1)." (未設定)";

	/* 取得武器資訊 */
	$postdata = array(
		"cmd"=>"EB_SOUKO",
		"pname"=>$ebs_id,
		"pass"=>$ebs_pwd
	);
	$alertmsg = "";
	$weapon_list = array();
	if (!($retdata = myPost($ebs_url."/ebs.cgi", $_SERVER['HTTP_USER_AGENT'], $User[3], $postdata)))
		msgBox("?","無法連結遊戲伺服器");
	if (preg_match("/熟練度不足/", $retdata))
		$alertmsg.= "熟練度不足，無法進入合成倉庫\\n故無法讀取武器列表\\n但仍可使用目前武器攻擊";
	else
	{
		preg_match("/$pattern[1]/", $retdata, $getRegs);
		array_push($weapon_list, array($getRegs[1], $getRegs[2], $getRegs[3], 0));
		preg_match_all("/$pattern[3]/", $retdata, $getRegs);
		for ($i=0; $i<count($getRegs[0]); ++$i)
			array_push($weapon_list, array($getRegs[2][$i], $getRegs[3][$i], $getRegs[4][$i], $getRegs[1][$i]));
	}

	/* 取得 HP、AP */
	$postdata = array(
		"cmd"=>"CUSTOMING",
		"pname"=>$ebs_id,
		"pass"=>$ebs_pwd
	);
	if (!($retdata = myPost($ebs_url."/ebs.cgi", $_SERVER['HTTP_USER_AGENT'], $User[3], $postdata)))
		msgBox("?","無法連結遊戲伺服器");
	preg_match("/$pattern[13]/", $retdata, $getRegs);
	$hp = $getRegs[1];
	preg_match("/$pattern[15]/", $retdata, $getRegs);
	$ap = $getRegs[1];

	include_once("header.php");
?>
<style type="text/css">
	.setbox {
		position:absolute;
		visibility:hidden;
		left:50%;
		top:50%;
		width:220px;
		height:46px;
		margin-left:-110px;
		margin-top:-23px;
		background-color:#444;
		border:3px #999 solid;
		color:#999;
		text-align:center;
	}
</style>
<script language="JavaScript" type="text/JavaScript">
	function showOption(val)
	{
		document.getElementById('set_1').style.visibility = (val == 1)?'visible':'hidden';
		document.getElementById('set_2').style.visibility = (val == 2)?'visible':'hidden';
		if (val == 1)
			document.fix.sec.value = document.MyForm.fix_time.value;
		else if (val == 2)
		{
			document.fix.sec1.value = document.MyForm.random_time1.value;
			document.fix.sec2.value = document.MyForm.random_time2.value;
		}
	}
	function setTime_1(val)
	{
		document.MyForm.fix_time.value = val;
		document.getElementById('set_1').style.visibility = 'hidden';
	}
	function setTime_2(val1,val2)
	{
		document.MyForm.random_time1.value = val1;
		document.MyForm.random_time2.value = val2;
		document.getElementById('set_2').style.visibility = 'hidden';
	}
</script>
<script type="text/javascript" language="javascript">
	var http_request = false;
	function makePOSTRequest(url, parameters)
	{
		http_request = false;
		if (window.XMLHttpRequest) { // Mozilla, Safari,...
			http_request = new XMLHttpRequest();
			if (http_request.overrideMimeType) {
			// set type accordingly to anticipated content type
			//http_request.overrideMimeType('text/xml');
				http_request.overrideMimeType('text/html');
			}
		}
		else if (window.ActiveXObject) { // IE
			try {
				http_request = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch (e) {
				try {
					http_request = new ActiveXObject("Microsoft.XMLHTTP");
				}
				catch (e) {}
			}
		}
		if (!http_request) {
			alert('Cannot create XMLHTTP instance');
			return false;
		}
		http_request.onreadystatechange = alertContents;
		http_request.open('POST', url, true);
		http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=big5");
		http_request.setRequestHeader("Content-length", parameters.length);
		http_request.setRequestHeader("Connection", "close");
		http_request.send(parameters);
	}

	function alertContents() {
		if (http_request.readyState == 4) {
			if (http_request.status == 200)
				alert(http_request.responseText);
			else
            	alert("傳送要求時發生錯誤");
		}
	}
	function listRequest(val)
	{
		var poststr;
		poststr = "list=" + encodeURI( val );
		makePOSTRequest('?list', poststr);
	}
<?
	if ($alertmsg) echo "alert(\"$alertmsg\");";
?>
</script>
<div class="box">
<div style="width:100%; padding:5px 0px 5px 0px; background-color:#aaa; text-align:center">Hi! <font color="#3333CC"><?=$User[2]?></font></div>
<div style="width:100%; padding:5px 0px 5px 0px; border-bottom:1px #aaa solid; background-color:#222; text-align:left">
<span style="margin-left:5px"><b>登入次數：</b><font color="#7fbfbf"><?=$User[4]?></font></span><br>
<span style="margin-left:5px"><b>上次登入：</b><font color="#7fbfbf"><?=$User[5]?></font></span>
</div>
<div style="text-align:center; margin-bottom:5px;">
<form action="?attack" method="post" name="MyForm" onkeydown="if(event.keyCode == 13) this.submit()">
	<input type="hidden" name="hp" value="<?=$hp?>">
	<input type="hidden" name="ap" value="<?=$ap?>">
	<table style="margin:5px auto 5px auto; width:100%">
	<tr>
		<td style="text-align:right">EBS帳號：</td>
		<td style="text-align:left"><font color="#AA5555"><?=$ebs_id?></font></td>
	</tr>
	<tr>
		<td style="text-align:right">代理：</td>
		<td style="text-align:left"><font color="#AA5555"><?=(($User[3])?((strlen($User[3]) > 27)?substr($User[3],0,25)."...":$User[3]):"無代理")?></font></td>
	</tr>
	<tr>
		<td style="text-align:right">執行場數：</td>
		<td style="text-align:left; color:#888">
			<input type="radio" name="limit" value="y" checked> <input type="text" name="attack_times" value="50" class="inputfield2" style="width:40px; text-align:right"> 場　
			<input type="radio" name="limit" value="n"> 不限制
		</td>
	</tr>
	<tr>
		<td style="text-align:right">攻擊名單：</td>
		<td style="text-align:left">
			<select class="selectfield" name="attack_list"><?=$attack_list?></select>
			[<a href="#" onclick="listRequest(MyForm.attack_list.value)">查看列表</a>]
		</td>
	</tr>
	<tr>
		<td style="text-align:right">攻擊間隔：</td>
		<td style="text-align:left">
			<select class="selectfield" style="width:180px" name="wait_time" onchange="showOption(this.value)">
			<option value="0">--請選擇--
			<option value="1">固定秒數
			<option value="2">隨機秒數
			<option value="3">ＡＰ一半回復時間
			<option value="4">攻擊名單最快復活時間
			</select>
		</td>
		<input type="hidden" name="fix_time" value="20">
		<input type="hidden" name="random_time1" value="10">
		<input type="hidden" name="random_time2" value="20">
	</tr>
	<tr>
		<td style="text-align:right">使用武器：</td>
		<td style="text-align:left">
			<select class="selectfield" style="font-size:12px; font-family:Tahoma; width:240px" name="weapon">
<?
			if (count($weapon_list) > 0)
			{
				foreach($weapon_list as $weapon)
				{
					echo "\t\t\t<option value='".$weapon[3]."'".(($weapon[3]==0)?" style='color:#FFFF99'":"").">" . ((strlen($weapon[0]) > 20) ? substr($weapon[0],0,18)."..." : $weapon[0]);
					echo " [Lv.".$weapon[1]."/exp.".$weapon[2]."]\n";
				}
			}
			else
				echo "<option>無法讀取，使用預設武器\n";
?>
			</select>
		</td>
	</tr>
	<tr>
		<td style="text-align:right" onmouseover="style.cursor='help'" onclick="alert('限有限制執行場數使用')">結束武器：</td>
		<td style="text-align:left">
			<select class="selectfield" style="font-size:12px; font-family:Tahoma; width:240px" name="end_weapon">
<?
				echo "\t\t\t<option value='-1'>不更換，以使用的武器結束\n";
			if (count($weapon_list) > 0)
				foreach ($weapon_list as $weapon)
				{
					echo "\t\t\t<option value='".$weapon[3]."'".(($weapon[3]==0)?" style='color:#FFFF99'":"").">" . ((strlen($weapon[0]) > 20) ? substr($weapon[0],0,18)."..." : $weapon[0]);
					echo " [Lv.".$weapon[1]."/exp.".$weapon[2]."]\n";
				}
?>
			</select>
		</td>
	</tr>
	<tr>
		<td style="text-align:right">攻擊指令：</td>
		<td style="text-align:left">
			<select class="selectfield" style="width:180px" name="attack_mode">
			<option  value="0">--請選擇--
			<option  value="1">LV.001 通常攻擊
			<option  value="2">LV.005 攻擊重視
			<option  value="3">LV.005 命中重視
			<option  value="4">LV.005 敏捷重視
			<option  value="5">LV.005 防禦重視
			<option  value="6">LV.030 闇勁
			<option  value="7">LV.030 光勁
			<option  value="8">LV.045 快速術
			<option  value="9">LV.060 攻擊力增幅
			<option value="10">LV.070 女神ソ眼差ウ
			<option value="11">LV.085 集中
			<option value="12">LV.110 突擊
			<option value="13">LV.130 四天聖精奉還
			<option value="14">LV.150 界王拳
			<option value="15">LV.170 絕對命中
			<option value="16">LV.180 捨身
			<option value="17">LV.200 ホモキゑゐベ
			<option value="18">LV.220 氣鋼鬥衣
			<option value="19">LV.250 萊康布洛斯獸化
			<option value="20">LV.270 超級賽亞人
			<option value="21">LV.290 ノクホよグ
			</select>
		</td>
	</tr>
	<tr>
		<td style="text-align:right">其他選項：</td>
		<td style="text-align:left; color:#888">
			<input type="checkbox" name="options[0]" value="y" checked> 對象沒死繼續打<br>
			<input type="checkbox" name="options[1]" value="y" checked> 攻擊低於 <input type="text" name="hp_attack" class="inputfield2" style="width:40px;" value="1000"> 或被打死取消該對象<br>
			<input type="checkbox" name="options[2]" value="y" checked> 不攻擊線上玩家<br>
			<input type="checkbox" name="options[3]" value="y" onclick="alert('此功能尚未支援')"> 每 <input type="text" name="equip_period" value="50" class="inputfield2" style="width:30px; text-align:right"> 場購買
			<select name="equip" class="selectfield" style="font-size:12px; font-family:Tahoma; width:120px">
				<option value="b">疾風之靴
				<option value="c">光之盾
				<option value="d">火之盾
				<option value="e">地之盾
				<option value="f">闇之盾
				<option value="g">水之盾
				<option value="h">風之盾
				<option value="i">иラヤэュ．э⑦ヲ
				<option value="j">七刃御魂劍
				<option value="o">會心丹
				<option value="p">藍波球
				<option value="q">ьヲЮ①ヱ
				<option value="r">ＡＭ戰鬥衣
				<option value="s">鬥魂мюЬ
				<option value="t">大和мюЬ
				<option value="u">S2機關
				<option value="v">天啟之劍
				<option value="w">聖服
				<option value="y">咒ゆソ指輪
				<option value="z">笑臉男
			</select>
			<br>
		</td>
	</tr>
	</table>
	<input type="button" value="開始" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="MyForm.submit()">
	<input type="button" value="設定" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.href='?setup'">
	<input type="button" value="紀錄" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.href='?history'">
	<input type="button" value="重整" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.href='?config'">
	<input type="button" value="登出" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.href='?login'">
</form>
</div>
</div>
<form name="fix">
<table class="setbox" id="set_1">
<tr>
	<td style="background-color:#444;">
	固定 <input type="input" name="sec" class="inputfield2" style="width:30px; text-align:right"> 秒攻擊間隔
	</td>
	<td style="background-color:#444;">
	<input type="button" value="確定" onclick="setTime_1(document.fix.sec.value)" class="submit">
	</td>
</tr>
</table>
<table class="setbox" id="set_2">
<tr>
	<td style="background-color:#444;">
	隨機 <input type="input" name="sec1" class="inputfield2" style="width:20px; text-align:right"> ~ <input type="input" name="sec2" class="inputfield2" style="width:20px; text-align:right"> 秒之間
	</td>
	<td style="background-color:#444;">
	<input type="button" value="確定" onclick="setTime_2(document.fix.sec1.value,document.fix.sec2.value)" class="submit">
	</td>
</tr>
</table>
</form>
<?include("footer.php");?>