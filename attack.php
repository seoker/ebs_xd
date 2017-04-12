<?
	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		die("請勿直接使用絕對路徑");

	$ebs_id = $_COOKIE['id'];
	$ebs_pwd = $_COOKIE['pwd'];

	if (!($User = checkpwd($ebs_id, $ebs_pwd, $user_dat)))
		msgBox("?","帳號或密碼錯誤，請重新登入");

	/* 檢查表單 */
	$list_file = "$dat_dir/$ebs_id/list".($_POST['attack_list']+1).".dat";
	$errmsg = "";
	if (!file_exists($list_file) || !filesize($list_file))
		$errmsg .= "請勿選取尚未設定之攻擊名單<br>";
	if (!$_POST['attack_mode'])
		$errmsg .= "請選取攻擊指令<br>";
	if (!$_POST['wait_time'])
		$errmsg .= "請選取攻擊間隔<br>";
	if (!is_numeric($_POST['attack_times']))
		$errmsg .= "執行場數請為數字<br>";
	if ($errmsg)
		msgBox("?config", $errmsg);

	$temp_list_file = "$dat_dir/$ebs_id/templist";
	/* Copy 攻擊列表 */
	if (!copy($list_file, $temp_list_file))
		msgBox("發生錯誤<br>無法建立暫存檔");
	$temp_data = split("\n", file_get_contents($temp_list_file));
	foreach($temp_data as $key=>$val)
		$temp_data[$key] .= ";".time();
	file_put_contents($temp_list_file, join("\n", $temp_data));

	/* 開啟 pattern 比對清單 */
	$pattern = split("\n",file_get_contents($pattern_dat));

	/* 如果選取武器不為目前武器，則更換之 */
	if ($_POST['weapon'] != 0)
	{
		$postdata = array(
			"cmd" => "EB_SOUKO",
			"pname" => $ebs_id,
			"pass" => $ebs_pwd
		);
		$retdata = myPost($ebs_url."/ebs.cgi", $_SERVER['HTTP_USER_AGENT'], $User[3], $postdata);
		preg_match("/$pattern[5]/", $retdata, $getRegs);
		$postdata = array(
			"cmd" => "EB_GOUSEI3",
			"Gmode" => "裝備變更",
			"pname" => $ebs_id,
			"pass" => $ebs_pwd,
			"date" => $getRegs[1],
			"check" => $getRegs[2],
			"check8" => $getRegs[3],
			"check9" => $getRegs[4],
			"ireru" => $_POST['weapon']
		);
		myPost($ebs_url."/ebs.cgi", $_SERVER['HTTP_USER_AGENT'], $User[3], $postdata);
	}
	if ($_POST['end_weapon'] == -1 || $_POST['end_weapon'] == $_POST['weapon'])
		$_POST['end_weapon'] = 0;
	else if ($_POST['end_weapon'] == 0)
		$_POST['end_weapon'] = $_POST['weapon'];

	/* 儲存攻擊資訊 */
	$config_data = join(";", array($ebs_id, $ebs_pwd, $User[3], $_POST['hp'], $_POST['ap'], $_POST['wait_time'], $_POST['attack_mode'])) . ";";
	$config_data.= ($_POST['options'][0]) ? "y;" : ";";
	$config_data.= ($_POST['options'][1]) ? $_POST['hp_attack'].";" : ";";
	$config_data.= ($_POST['options'][2]) ? "y;" : ";";
	$config_data.= ($_POST['options'][3]) ? $_POST['equip_period'].",".$_POST['equip'].";" : ";";
	if ($_POST['wait_time'] == 1)
		$config_data.= $_POST['fix_time'] . ";";
	else if ($_POST['wait_time'] == 2)
		$config_data.= $_POST['random_time1'] . "," . $_POST['random_time2'] . ";";
	else
		$config_data.= ";";
	$config_data.= "1;0;";
	/* ID;PWD;Proxy;AP;HP;間隔選項;戰鬥指令;沒死繼續打;攻擊低於多少刪除該對象;不攻擊線上玩家;週期購買配件;間隔時間;武器AP;下一個對手;結束武器; */
	file_put_contents("$dat_dir/$ebs_id/config", $config_data);

	include_once("header.php");
?>
<script type="text/javascript" language="javascript">
	var attack_flag = true;
	setTimeout("window.parent.hiddenframe.location.href='?battle'", 1000);
	function stoporgo(obj)
	{
		if (attack_flag)
			stopGo(obj);
		else
			Go(obj);
	}
	function stopGo(obj)
	{
		window.parent.hiddenframe.location.href='about:blank';
		obj.value = "繼續";
		document.getElementById('ebs_status').innerHTML = "停止中...";
		attack_flag = false;
	}
	function Go(obj)
	{
		setTimeout("window.parent.hiddenframe.location.href='?battle'", 1000);
		obj.value = "停止";
		document.getElementById('ebs_status').innerHTML = "準備開始戰鬥..請稍後 1 秒鐘";
		attack_flag = true;
	}
	function remaining(text1, text2, sec)
	{
		if (attack_flag)
		{
			document.getElementById('ebs_status').innerHTML = text1 + " " + sec + " " + text2;
			if (sec > 0)
				setTimeout("remaining('"+text1+"','"+text2+"',"+(sec-1)+")", 1000);
			else
				document.getElementById('ebs_status').innerHTML = "準備進行戰鬥...";
		}
	}
	function countdownmsg(text1, text2, text3, sec)
	{
		document.getElementById('ebs_status').innerHTML = text1 + " " + sec + " " + text2;
		if (sec > 0)
			setTimeout("countdownmsg('"+text1+"','"+text2+"','"+text3+"',"+(sec-1)+")", 1000);
		else
			document.getElementById('ebs_status').innerHTML = text3;
	}
</script>
<script type="text/javascript" language="javascript">
	var battle_times = 0, w = 0, d = 0, l = 0;
	var total_exp = 0, total_money = 0, total_wexp = 0;
	var counter = <?=($_POST['limit'] == "y")?$_POST['attack_times']:-1?>;
	function battleReturn(opp_name, hp_my1, hp_my2, hp_my, hp_opp1, hp_opp2, hp_opp, attack_my, hit_my, damage_my,
	attack_opp, hit_opp, damage_opp, exp, money, wexp, result, othermsg, my_w, opp_w, nextopp)
	{
		var my_msg = "";
		var opp_msg = "";
		var get_msg = "";
		var result_msg = "";
		battle_times = battle_times + 1;
		my_msg = my_msg + my_w + "<br>";
		if (hit_my > 0)
		{
			my_msg = my_msg + "<b style='color:#9acd32;'>" + attack_my + "</b> <b style='color:#dc143c;'>Attack</b> ";
			my_msg = my_msg + "<b style='color:#9acd32;'>" + hit_my + "</b> <b style='color:#dc143c;'>Hit</b> ";
			my_msg = my_msg + "<b style='color:#9acd32;'>" + damage_my + "</b> <b style='color:#dc143c;'>Damage</b>";
		}
		else
			my_msg = my_msg + "<font color=#6a5acd>Miss</font>";
		opp_msg = opp_msg + opp_w + "<br>";
		if (hit_opp > 0)
		{
			opp_msg = opp_msg + "<b style='color:#9acd32;'>" + attack_opp + "</b> <b style='color:#dc143c;'>Attack</b> ";
			opp_msg = opp_msg + "<b style='color:#9acd32;'>" + hit_opp + "</b> <b style='color:#dc143c;'>Hit</b> ";
			opp_msg = opp_msg + "<b style='color:#9acd32;'>" + damage_opp + "</b> <b style='color:#dc143c;'>Damage</b>";
		}
		else
			opp_msg = opp_msg + "<font color=#6a5acd>Miss</font>";
		if (result == 1)
		{
			result_msg = result_msg + "獲勝";
			w = w + 1;
		}
		else if (result == 3)
		{
			result_msg = result_msg + "戰敗";
			l = l + 1;
		}
		else if (result == 4)
		{
			result_msg = result_msg + "獲勝但對方復活";
			w = w + 1;
		}
		else if (result == 5)
		{
			result_msg = result_msg + "戰敗但復活";
			l = l + 1;
		}
		else
		{
			result_msg = result_msg + "平手";
			d = d + 1;
		}
		total_exp += parseInt(exp);
		total_money += parseInt(money);
		total_wexp += parseInt(wexp);
		get_msg = get_msg + "<b style='color:#dc143c;'>" + exp + "</b> 經驗 ";
		get_msg = get_msg + "<b style='color:#dc143c;'>" + money + "</b> 金錢 ";
		get_msg = get_msg + "<b style='color:#dc143c;'>" + wexp + "</b> 招式經驗 ";
		document.getElementById('ebs_lastone').innerText = document.getElementById('ebs_opponent').innerText;
		document.getElementById('ebs_nextone').innerText = nextopp;
		document.getElementById('ebs_opponent').innerText = opp_name;
		document.getElementById('ebs_my').innerHTML = my_msg;
		document.getElementById('ebs_opp').innerHTML = opp_msg;
		document.getElementById('ebs_result').innerHTML = result_msg;
		document.getElementById('ebs_get').innerHTML = get_msg;
		document.getElementById('ebs_times').innerText = battle_times;
		document.getElementById('ebs_all').innerText = battle_times
		document.getElementById('ebs_w').innerText = w;
		document.getElementById('ebs_d').innerText = d;
		document.getElementById('ebs_l').innerText = l;
		document.getElementById('ebs_other').innerHTML = othermsg;
		showHP('my_hp1', 'my_hp2', hp_my1, hp_my2, hp_my);
		showHP('opp_hp1', 'opp_hp2', hp_opp1, hp_opp2, hp_opp);
		if (counter != -1)
		{
			counter--;
			document.getElementById('remains').innerText = counter;
			if (counter == 0)
			{
				stopGo(document.getElementById('BBB'));
<? if ($_POST['end_weapon'] != 0) { ?>
				countdownmsg("等候","秒更換武器","停止中...", 7);
				setTimeout("makeRequest('?ch_weapon&weapon=<?=$_POST['end_weapon']?>&num="+Math.random()+"')", 7000);
<? } else { ?>
				alert("執行結束");
<? } ?>
			}
		}
	}
	function view()
	{
		alert("累積總共\n" + total_exp + "\t經驗\n" + total_money + "\t金錢\n" + total_wexp + "\t招式經驗");
	}
	function showHP(obj1, obj2, hp_s, hp_e, hp_all)
	{
		var ob1 = document.getElementById(obj1);
		var ob2 = document.getElementById(obj2);
		var r = Math.round(Math.random()*100);
		var img = "./image/bar_";
		if (r > 66)
			img = img + "blue.gif";
		else if (r > 33)
			img = img + "green.gif";
		else
			img = img + "purple.gif";
		ob1.src = img;
		ob1.width = Math.round(hp_s/hp_all*150);
		ob2.innerText = hp_s;
		ob2.style.color = "#ccc";
		setTimeout("decHP('"+obj1+"','"+obj2+"',"+hp_s+","+hp_s+","+hp_e+","+hp_all+")", 1000);
	}
	function decHP(obj1, obj2, hp, hp_s, hp_e, hp_all)
	{
		var ob1 = document.getElementById(obj1);
		var ob2 = document.getElementById(obj2);
		hp = hp + Math.round((hp_e-hp_s)*0.1);
		if (hp < hp_e)
			hp = hp_e;
		if (hp < Math.round(hp_all*0.2))
		{
			ob1.src = "./image/bar_red.gif";
			ob2.style.color = "#c00";
		}
		ob1.width = Math.round(hp/hp_all*150);
		ob2.innerText = hp;
		if (hp != hp_e)
			setTimeout("decHP('"+obj1+"','"+obj2+"',"+hp+","+hp_s+","+hp_e+","+hp_all+")", 20);
	}
</script>
<script type="text/javascript" language="javascript">
	var http_request = false;
	function makeRequest(url)
	{
		http_request = false;
		if (window.XMLHttpRequest) { // Mozilla, Safari,...
			http_request = new XMLHttpRequest();
			if (http_request.overrideMimeType)
				http_request.overrideMimeType('text/xml');
		}
		else if (window.ActiveXObject)
		{ // IE
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

		if (!http_request)
		{
			alert('Giving up :( Cannot create an XMLHTTP instance');
			return false;
		}
		http_request.onreadystatechange = alertContents;
		http_request.open('GET', url, true);
		http_request.send(null);
	}

	function alertContents()
	{
		if (http_request.readyState == 4)
		{
			if (http_request.status == 200)
				alert("執行結束\n"+http_request.responseText);
			else
				alert("傳送要求時發生錯誤");
		}
	}

</script>
<div class="box">
	<div style="width:100%; padding:5px 0px 5px 0px; background-color:#aaa; text-align:center">Battle</div>
	<div style="width:100%; margin:8px auto 8px auto">
		<div style="float:left; margin-left:10px">第 <span id="ebs_times" style="color:red; font-weight:bold">0</span> 次戰鬥結果 </div>
<?
	if ($_POST['limit'] == 'y')
	echo "\t\t<div style=\"float:right; margin-right:10px\">剩餘場數：<span id=\"remains\" style=\"color:red; font-weight:bold\">0</span> 場</div>\n";
?>
		<div style="margin:8px 8px 8px 8px; border:1px solid #555; background-color:#222; padding:5px; clear:both">
			<div style="border:1px solid #555; background-color:#111; color:#444; padding:4px; text-align:center">
				<div style="float:left; text-align:left;">
				上一位：<span style="color:#CCC; margin:2px" id="ebs_lastone">無</span><br>
				下一位：<span style="color:#CCC; margin:2px" id="ebs_nextone">無</span> 
				</div>
				<div style="float:right; text-align:left">
				戰：<span style="color:#CCC" id="ebs_all">0</span> 勝：<span style="color:#CCC" id="ebs_w">0</span><br>
				平：<span style="color:#CCC" id="ebs_d">0</span> 敗：<span style="color:#CCC" id="ebs_l">0</span>
				</div>
				<div style="clear:both"></div>
			</div>
			<div style="border:1px solid #555; margin-top:5px; background-color:#111; color:#444; padding:4px; text-align:left">
				狀態：　<span style="color:#CCC;" id="ebs_status">準備開始戰鬥..請稍後 1 秒鐘</span>
			</div>
			<div style="border:1px solid #555; margin-top:5px; background-color:#000; padding:0px; text-align:left">
				<table style="width:100% !important; width:318px" cellspacing="1" cellpadding="3">
					<tr>
						<td width="30%" style="text-align:right"><b>對　　象：</b></td>
						<td width="70%" style="text-align:left; background-color:#222"><span style="color:#CCC;" id="ebs_opponent">無</span></td>
					</tr>
					<tr>
						<td width="30%" style="text-align:right; height:45px"><b>己方攻擊：</b></td>
						<td width="70%" style="text-align:left; background-color:#222"><span style="color:#789; font-size:11px; font-family:tahoma" id="ebs_my">無<br>無</span></td>
					</tr>
					<tr>
						<td width="30%" style="text-align:right; height:45px"><b>對方攻擊：</b></td>
						<td width="70%" style="text-align:left; background-color:#222"><span style="color:#789; font-size:11px; font-family:tahoma;" id="ebs_opp">無<br>無</span></td>
					</tr>
					<tr>
						<td width="30%" style="text-align:right"><b>已方血量：</b></td>
						<td width="70%" style="text-align:left; background-color:#222">
							<div style="background:url(./image/bar_gray.gif); width:150px; height:16px; float:left"><img src="./image/bar_red.gif" width="0" height="16" id="my_hp1"></div>
							<div style="height:16px; float:right;" id="my_hp2"></div>
						</td>
					</tr>
					<tr>
						<td width="30%" style="text-align:right"><b>對方血量：</b></td>
						<td width="70%" style="text-align:left; background-color:#222">
							<div style="background:url(./image/bar_gray.gif); width:150px; height:16px; float:left"><img src="./image/bar_red.gif" width="0" height="16" id="opp_hp1"></div>
							<div style="height:16px; float:right;" id="opp_hp2"></div>
						</td>
					</tr>
					<tr>
						<td width="30%" style="text-align:right"><b>結　　果：</b></td>
						<td width="70%" style="text-align:left; background-color:#222"><span style="color:#CCC;" id="ebs_result">無</span></td>
					</tr>
					<tr>
						<td width="30%" style="text-align:right"><b>獲　　得：</b></td>
						<td width="70%" style="text-align:left; background-color:#222"><span style="color:#CCC;" id="ebs_get">無</span></td>
					</tr>
					<tr>
						<td width="30%" style="text-align:right; height:60px"><b>其　　他：</b></td>
						<td width="70%" style="text-align:left; background-color:#222"><span style="color:#CCC; font-size:11px" id="ebs_other">無<br>無<br>無</span></td>
					</tr>
				</table>
			</div>
		</div>
		<input type="button" value="停止" class="submit" id="BBB" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="stoporgo(this)">
		<input type="button" value="累積" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="view()">
		<input type="button" value="主頁" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="window.parent.hiddenframe.location.href='about:blank';location.href='?config'">
	</div>
</div>
<script type="text/javascript" language="javascript">
	if ("<?=$_POST['limit']?>" == "y")
		document.getElementById('remains').innerText = <?=$_POST['attack_times']?>;
</script>
<?include("footer.php");?>