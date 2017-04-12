<?
	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		die("請勿直接使用絕對路徑");

	$ebs_id = $_COOKIE['id'];
	$ebs_pwd = $_COOKIE['pwd'];

	if (!($User = checkpwd($ebs_id, $ebs_pwd, $user_dat)))
		errmsg("帳號或密碼錯誤，請重新登入");

	if (file_exists("$dat_dir/$ebs_id/history"))
		$data = array_reverse(file("$dat_dir/$ebs_id/history"));
	else
		errmsg("記錄檔不存在");

	$data_count = count($data);
	$i = $_GET['index'];
	if ($i < 0 || $i >= $data_count)
		errmsg("有問題的索引值，請再嘗試一次");

	if (!$_GET['job'])
	{
?>
<html>
<head>
	<title>EBS XD</title>
	<link rel="shortcut icon" href="favicon.ico">
</head>
<frameset rows="100%,*" border="0">
	<frame src="?show_battle&index=<?=$i?>&job=view" name="show_battle">
	<frame src="about:blank" name="hidden">
</frameset>
<?
		exit;
	}
	else
	{
		include_once("header.php");
		list($type, $dates, $vs_name, $my_hp1, $my_hp2, $my_hp, $opp_hp1, $opp_hp2, $opp_hp, $my_attack, $my_hit, $my_damage,
			$opp_attack, $opp_hit, $opp_damage, $getexp, $getmoney, $getwexp, $result, $othermsg, $my_weapon, $opp_weapon) = split(";", $data[$i]);

?>
<script type="text/javascript" language="javascript">
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
<div class="box">
	<div style="width:100%; padding:5px 0px 5px 0px; background-color:#aaa; text-align:center">Battle</div>
	<div style="width:100%; margin:8px auto 8px auto">
		<div style="margin-left:10px"> </div>
		<div style="margin:8px 8px 8px 8px; border:1px solid #555; background-color:#222; padding:5px; clear:both">
			<div style="border:1px solid #555; margin-top:5px; background-color:#000; padding:0px; text-align:left">
				<table style="width:100% !important; width:318px" cellspacing="1" cellpadding="3">
					<tr>
						<td width="30%" style="text-align:right"><b>對　　象：</b></td>
						<td width="70%" style="text-align:left; background-color:#222">
							<span style="color:#CCC;" id="ebs_opponent"><?=$vs_name?></span>
						</td>
					</tr>
					<tr>
						<td width="30%" style="text-align:right; height:45px"><b>己方攻擊：</b></td>
						<td width="70%" style="text-align:left; background-color:#222">
							<span style="color:#789; font-size:11px; font-family:tahoma" id="ebs_my">
								<?=$my_weapon?><br>
<? if ($my_hit > 0) { ?>
								<b style='color:#9acd32;'><?=$my_attack?></b> <b style='color:#dc143c;'>Attack</b> 
								<b style='color:#9acd32;'><?=$my_hit?></b> <b style='color:#dc143c;'>Hit</b> 
								<b style='color:#9acd32;'><?=$my_damage?></b> <b style='color:#dc143c;'>Damage</b>
<? } else { ?>
								<font color=#6a5acd>Miss</font>
<? } ?>
							</span>
						</td>
					</tr>
					<tr>
						<td width="30%" style="text-align:right; height:45px"><b>對方攻擊：</b></td>
						<td width="70%" style="text-align:left; background-color:#222">
							<span style="color:#789; font-size:11px; font-family:tahoma;" id="ebs_opp">
								<?=$opp_weapon?><br>
<? if ($opp_hit > 0) { ?>
								<b style='color:#9acd32;'><?=$opp_attack?></b> <b style='color:#dc143c;'>Attack</b> 
								<b style='color:#9acd32;'><?=$opp_hit?></b> <b style='color:#dc143c;'>Hit</b> 
								<b style='color:#9acd32;'><?=$opp_damage?></b> <b style='color:#dc143c;'>Damage</b>
<? } else { ?>
								<font color=#6a5acd>Miss</font>
<? } ?>
							</span>
						</td>
					</tr>
					<tr>
						<td width="30%" style="text-align:right"><b>已方血量：</b></td>
						<td width="70%" style="text-align:left; background-color:#222">
							<div style="background:url(./image/bar_gray.gif); width:150px; height:16px; float:left"><img src="./image/bar_red.gif" width="0" height="16" id="my_hp1"></div>
							<div style="height:16px; float:right;" id="my_hp2"><?=$my_hp1?></div>
						</td>
					</tr>
					<tr>
						<td width="30%" style="text-align:right"><b>對方血量：</b></td>
						<td width="70%" style="text-align:left; background-color:#222">
							<div style="background:url(./image/bar_gray.gif); width:150px; height:16px; float:left"><img src="./image/bar_red.gif" width="0" height="16" id="opp_hp1"></div>
							<div style="height:16px; float:right;" id="opp_hp2"><?=$opp_hp1?></div>
						</td>
					</tr>
					<tr>
						<td width="30%" style="text-align:right"><b>結　　果：</b></td>
						<td width="70%" style="text-align:left; background-color:#222">
							<span style="color:#CCC;" id="ebs_result">
							<?
								switch($result)
								{
									case '1':
									case '4':
										echo "獲勝"; break;
									case '3':
									case '5':
										echo "戰敗"; break;
									default:
										echo "平手";
								}
							?>
							</span>
						</td>
					</tr>
					<tr>
						<td width="30%" style="text-align:right"><b>獲　　得：</b></td>
						<td width="70%" style="text-align:left; background-color:#222">
							<span style="color:#CCC;" id="ebs_get">無
								<b style='color:#dc143c;'><?=$getexp?></b> 經驗 
								<b style='color:#dc143c;'><?=$getmoney?></b> 金錢 
								<b style='color:#dc143c;'><?=$getwexp?></b> 招式經驗
							</span>
						</td>
					</tr>
					<tr>
						<td width="30%" style="text-align:right; height:60px"><b>其　　他：</b></td>
						<td width="70%" style="text-align:left; background-color:#222">
							<span style="color:#CCC; font-size:11px" id="ebs_other">
								<?=$othermsg?>
							</span>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<input type="button" value="上一場" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="window.location.href='?show_battle&index=<?=($i-1)%$data_count?>'">
		<input type="button" value="關閉" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="window.close()">
		<input type="button" value="下一場" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="window.location.href='?show_battle&index=<?=($i+1)%$data_count?>'">
	</div>
</div>
<script type="text/javascript" language="javascript">
	showHP('my_hp1', 'my_hp2', <?=$my_hp1?>, <?=$my_hp2?>, <?=$my_hp?>);
	showHP('opp_hp1', 'opp_hp2', <?=$opp_hp1?>, <?=$opp_hp2?>, <?=$opp_hp?>);
</script>

<?include("footer.php");?>
<?
	}
	function errmsg($msg)
	{
		include_once("header.php");
?>
	<script type="text/javascript" language="javascript">
	alert("<?=$msg?>");
	window.close();
	</script>
<?
		exit;
	}