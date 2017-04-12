<?
	$perpage = 50;

	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		die("請勿直接使用絕對路徑");

	$ebs_id = $_COOKIE['id'];
	$ebs_pwd = $_COOKIE['pwd'];

	if (!($User = checkpwd($ebs_id, $ebs_pwd, $user_dat)))
		msgBox("?","帳號或密碼錯誤，請重新登入");

	if (file_exists("$dat_dir/$ebs_id/login"))
		$data = array_reverse(file("$dat_dir/$ebs_id/login"));
	else
		$data = array();

	include_once("header.php");
	$data_count = count($data);

?>
<style type="text/css">
	.pagebtn {border-width:1px 0px 1px 0px; border-style: solid; border-color:#222; background-color:#555}
	.pagebtn a {float:left; line-height:26px; color:#aaa; text-decoration:none; padding:0px 5px}
	.pagebtn a:hover {background-color:#999}
	.pagebtn b {float:left; line-height:26px; color:#ccc; background-color:#222; padding:0px 5px}
	.pagebtn em {float:left; line-height:26px; background-color:#353535; color:#cc5; font-style:normal; padding:0px 9px}
	.Td0 {
		background-color: #aaa;
		padding:3px;
		cursor:hand;
	}
	.Td1 {
		background-color: #999;
		padding:3px;
		cursor:hand;
	}
	.date {
		float:left;
		margin-left:10px;
		font-size:12px;
		color:#335;
	}
	.no {
		float:left;
		width:25px;
		color:#252;
		text-align:right;
	}
	.ip {
		float:right;
		margin-left:5px;
		color:#533;
	}
</style>
<div class="box">
	<div style="width:100%; padding:5px 0px 5px 0px; background-color:#aaa; text-align:center">Login History</div>
	<div style="margin:5px auto">共有 <font color="red"><b><?=$data_count?></b></font> 筆登入記錄</div>
	<div style="margin:0px; height:390px; Overflow:auto;">
<?
	for ($i=0; $i<$data_count; ++$i)
	{
		$history_dat = split(";", str_replace("\n","",$data[$i]));
		echo "\t<div class='Td".($i%2)."'>\n";
		echo "\t\t<span class='no'>".($i+1)."</span>\n";
		echo "\t\t<span class='date'>".$history_dat[0]."</span>\n";
		echo "\t\t<span class='ip'>".$history_dat[1]."</span>\n";
		echo "\t\t<span style='clear:both'></span>\n";
		echo "\t</div>\n";
	}
	if ($data_count == 0)
		echo "<table style='height:100%;'><tr><td style='font-size:16px; color:#CC0'>無 記 錄</td></tr></table>";
?>
	</div>
	<div style="margin:5px auto">
		<input type="button" value="重整" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.reload()">
		<input type="button" value="紀錄" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.href='?history'">
		<input type="button" value="主頁" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.href='?config'">
	</div>
</div>
<?include("footer.php");?>
