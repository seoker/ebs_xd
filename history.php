<?
	$perpage = 50;

	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		die("請勿直接使用絕對路徑");

	$ebs_id = $_COOKIE['id'];
	$ebs_pwd = $_COOKIE['pwd'];

	if (!($User = checkpwd($ebs_id, $ebs_pwd, $user_dat)))
		msgBox("?","帳號或密碼錯誤，請重新登入");

	if (file_exists("$dat_dir/$ebs_id/history"))
		$data = array_reverse(file("$dat_dir/$ebs_id/history"));
	else
		$data = array();


if ($_GET['job'] == "view")
{

}
else if ($_GET['job'] == "delete")
{
	file_put_contents("$dat_dir/$ebs_id/history", "");
	echo iconv("big5","UTF-8","歷史紀錄已清空");
}
else
{
	include_once("header.php");
	$page = $_GET['page'];
	$data_count = count($data);
	$pages = ceil($data_count/$perpage);
	if ($page > $pages) $page = $pages;
	else if ($page < 1) $page = 1;
	$min_page = $page;
	$max_page = $page + 9;
	if ($min_page > 2)
	{
		$min_page -= 2;
		$max_page -= 2;
	}
	else if ($min_page > 1)
	{
		$min_page--;
		$max_page--;
	}
	while ($max_page > $pages)
	{
		$max_page--;
		$min_page--;
	}
	if ($min_page < 1) $min_page = 1;

	$from = ($page-1)*$perpage;
	$to = $page*$perpage-1;
	if ($data_count < $to) $to = $data_count;

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
		font-family:tahoma;
		font-size:11px;
		color:#335;
		width:110px;
	}
	.auth {
		font-weight:bold;
		color:#800;
	}
	.noman {
		margin-left:5px;
		color:#AA5;
	}
	.vsopp {
		width:90px;
		margin-left:5px;
		color:#333;
	}
	.err {
		font-weight:bold;
		margin-left:5px;
		color:#800;
	}
	.win {
		color:#00A;
	}
	.draw {
		color:#CCC;
	}
	.lose {
		color:#0A0;
	}
	.no {
		width:25px;
		color:#252;
		text-align:right;
	}
</style>
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
			{
				alert(http_request.responseText);
				location.reload();
			}
			else
				alert("傳送要求時發生錯誤");
		}
	}


	function show(i)
	{
		var w = 380, h = 450;
	    var l = (screen.width - w)/2;
	    var t = (screen.height - h)/2;

		if (navigator.appName == "Microsoft Internet Explorer")
			window.showModalDialog("?show_battle&index="+i+"&num="+Math.random(),self,"center:yes;resizable:no;scroll:on;status:no;dialogWidth:"+w+"px;dialogHeight:"+h+"px;dialogLeft:"+l+"px;dialogTop:"+t+"px");
		else
			window.open("?show_battle&index="+i,'newWin',"modal=yes,status=no,toolbar=no,menubar=no,resizable=no,scrollbars=yes,width="+w+",height="+h+",left="+l+",top="+t);

	}
</script>
<div class="box">
	<div style="width:100%; padding:5px 0px 5px 0px; background-color:#aaa; text-align:center">History</div>
	<div class="pagebtn">
		<em><?=$pages?></em>
<?
	if ($page != 1)
		echo "<a href='#' onclick=\"location.href='?history&page=1'\">|<</a>";
	if ($page > 10)
		echo "<a href='#' onclick=\"location.href='?history&".($page-10)."'\">&lsaquo;&lsaquo;</a>";
	if ($page > 1)
		echo "<a href='#' onclick=\"location.href='?history&".($page-1)."'\">&lsaquo;</a>";
	for ($i=$min_page; $i<=$max_page; ++$i)
		if ($i == $page)
			echo "<b>$i</b>";
		else
			echo "<a href='#' onclick=\"location.href='?history&page=$i'\">$i</a>";
	if ($page < $pages)
		echo "<a href='#' onclick=\"location.href='?history&page=".($page+1)."'\">&rsaquo;</a>";
	if ($page+10 < $pages)
		echo "<a href='#' onclick=\"location.href='?history&page=".($page+10)."'\">&rsaquo;&rsaquo;</a>";
	if ($page != $pages)
		echo "<a href='#' onclick=\"location.href='?history&page=$pages'\">>|</a>";
?>
		<div style="clear:both"></div>
	</div>
	<div style="margin:0px; height:390px; Overflow:auto; text-align:left">
<?
	for ($i=$from; $i<$to; ++$i)
	{
		$history_dat = split(";", $data[$i]);
		echo "\t<div class='Td".($i%2)."'".(($history_dat[0] == 1)?" onclick='show($i)'":"").">\n";
		echo "\t\t<span class='no'>".($i+1)."</span>\n";
		echo "\t\t<span class='date'>".$history_dat[1]."</span>\n";
		switch ($history_dat[0])
		{
			case '1':
				switch ($history_dat[18])
				{
					case '1':
					case '4':
						echo "\t\t<span class='win'>獲勝</span>\n";
						break;
					case '2':
						echo "\t\t<span class='draw'>平手</span>\n";
						break;
					case '3':
					case '5':
						echo "\t\t<span class='lose'>戰敗</span>\n";
						break;
					default:
						echo "\t\t<span class='err'>未知</span>\n";
				}
				echo "\t\t<span class='vsopp'>".$history_dat[2]."</span>\n";

				break;
			case '2':
				echo "\t\t<span class='auth'>認證通過 [".$history_dat[2]."]</span>\n";
				break;
			case '3':
				echo "\t\t<span class='err'>未知情況錯誤</span>\n";
				break;
			case '4':
				echo "\t\t<span class='noman'>無此對手 <b>".$history_dat[2]."</b></span>\n";
				break;
			default:
				echo "未知訊息\n";
		}
		echo "\t</div>\n";
	}
	if ($data_count == 0)
		echo "<table style='height:100%;'><tr><td style='font-size:16px; color:#CC0'>無 記 錄</td></tr></table>";
?>
	</div>
	<div style="margin:5px auto">
		<input type="button" value="重整" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.reload()">
		<input type="button" value="清空" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="if (confirm('確定要清空？')) makeRequest('?history&job=delete');">
		<input type="button" value="登入紀錄" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.href='?login_history'">
		<input type="button" value="主頁" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.href='?config'">
	</div>
</div>
<?
	include("footer.php");
}
?>
