<?
	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		die("請勿直接使用絕對路徑");

	$proxy_list = file($proxy_dat);
	if (!$_POST['job'])
	{
		$proxy_option = "";
		$proxy_js = "";
		$proxy_sv_js = "";
		$proxy_count = count($proxy_list);
		for ($i=0; $i<$proxy_count; ++$i)
		{
			list($sv_name, $sv_url, $user) = split(";", $proxy_list[$i]);
			$proxy_option .= "<option value='$i'".(($user)?" style='color:red'":"").(($_GET['proxy_sv'] == $i)?" selected":"").">$sv_name\n";
			$proxy_sv_js .= "proxy_sv_list[$i] = \"".$sv_url."\";\n";
			if ($user)
				$proxy_js .= "proxy_list[$i] = \"$user\";\n";
		}
		include_once("header.php");
?>
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
		http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		http_request.setRequestHeader("Content-length", parameters.length);
		http_request.setRequestHeader("Connection", "close");
		http_request.send(parameters);
	}

	function alertContents() {
		if (http_request.readyState == 4) {
			if (http_request.status == 200)
				document.getElementById('proxy_box').innerHTML = document.getElementById('proxy_box').innerHTML + http_request.responseText;
			else
            	alert("傳送要求時發生錯誤");
		}
	}
</script>

<script language="javascript">
	var proxy_list = new Array(<?=$proxy_count?>);
	var proxy_sv_list = new Array(<?=$proxy_count?>);
<?=$proxy_js?>
<?=$proxy_sv_js?>
	function proxyDescription(i) {
		document.getElementById('proxy_box').innerHTML = proxy_sv_list[i] + ((proxy_list[i])?"使用："+proxy_list[i]:"" ) + "<br>連線品質：";
		var poststr;
		poststr = "sv=" + encodeURI( proxy_sv_list[i] );
		makePOSTRequest('?proxy', poststr);
	}
	function checkForm()
	{
		var msg = "";
		var obj = document.MyForm;
		if (obj.id.value == "")
			msg = msg + "請輸入帳號\n";
		if (obj.pwd.value == "")
			msg = msg + "請輸入密碼\n";
		if (obj.nickname.value == "")
			msg = msg + "請輸入暱稱\n";
		if (msg)
			alert(msg);
		else
			obj.submit();
	}
</script>
<div class="box">
<div style="width:100%; padding:5px 0px 5px 0px; background-color:#aaa; text-align:center">Sign up</div>
<div style="text-align:center; margin:5px;">
<form action="?reg" method="post" name="MyForm" onkeydown="if(event.keyCode == 13) this.submit()">
	<input type="hidden" name="job" value="do_reg">
	<table style="margin:5px auto 5px auto;">
	<tr>
		<td style="text-align:right">EBS帳號：</td>
		<td style="text-align:left"><input type="text" name="id" class="inputfield" value="<?=urldecode($_GET['id'])?>"></td>
	</tr>
	<tr>
		<td style="text-align:right">EBS密碼：</td>
		<td style="text-align:left"><input type="password" name="pwd" class="inputfield" value="<?=urldecode($_GET['pwd'])?>"></td>
	</tr>
	<tr>
		<td style="text-align:right">暱稱：</td>
		<td style="text-align:left"><input type="text" name="nickname" class="inputfield" value="<?=urldecode($_GET['nickname'])?>"></td>
	</tr>
	<tr>
		<td style="text-align:right">代理：</td>
		<td style="text-align:left"><select name="proxy_sv" onchange="proxyDescription(this.value)" class="inputfield">
			<?=$proxy_option?>
		</select></td>
	</tr>
	</table>
	<div id="proxy_box" style="margin:5px; background-color:#eee; color:#555; border:1 #555 solid; width:230px; text-align:left; padding:5px">
	</div>
	<input type="button" value="註冊" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="checkForm()">
	<input type="button" value="首頁" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.href='?login'">
</form>
<script language="Javascript">
	document.MyForm.id.focus();
	proxyDescription(0);
</script>
</div>
</div>
<?
	}
	else
	{
		$query_string = "&id=".urlencode($_POST['id'])."&pwd=".urlencode($_POST['pwd'])."&proxy_sv=".$_POST['proxy_sv']."&nickname=".urlencode($_POST['nickname']);
		list($sv_name, $sv_url, $user) = split(";", $proxy_list[$_POST['proxy_sv']]);
		if ($user)
			$proxy_user_list = split(",", $user);
		else
			$proxy_user_list = array();
		array_push($proxy_user_list, $_POST['id']);

		$data = array(
			"cmd"=>"EB_SOUKO",
			"pname"=>$_POST['id'],
			"pass"=>$_POST['pwd']
		);

		/* 檢查資料 */
		if (!$_POST['id'] || !$_POST['pwd'] || !$_POST['nickname'])
			msgBox("?reg".htmlentities($query_string),"請填寫完整資料");
		$retdata = myPost($ebs_url."/ebs.cgi",$_SERVER['HTTP_USER_AGENT'],$sv_url,$data);
		if (!$retdata)
			msgBox("?reg".htmlentities($query_string),"無法連接遊戲伺服器");
		if (preg_match("/密碼錯誤/",$retdata))
			msgBox("?reg".htmlentities($query_string),"帳號或密碼錯誤");

		if (file_exists($user_dat))
		{
			$user_list = split("\n",file_get_contents($user_dat));
			foreach ($user_list as $useruser)
			{
				if (preg_match("/^".$_POST['id']."\;/", $useruser))
					msgBox("?reg".htmlentities($query_string),"此使用者 ".$_POST['id']."已經存在");
			}
		}
		/* 建立用戶資料夾 */
		mkdir($dat_dir."/".$_POST['id']) or msgBox("?", "無法建立用戶資料夾");
		//file_put_contents($dat_dir."/".$_POST['id']."/.htacess","<Limit GET POST PUT DELETE>\nOption ExecCGI\n</Limit>");
		/* 寫回 proxy 列表 */
		$proxy_list[$_POST['proxy_sv']] = $sv_name.";".$sv_url.";".join(",", $proxy_user_list).";\n";
		file_put_contents($proxy_dat, $proxy_list);

		$handle = fopen($user_dat,"a");
		fwrite($handle, $_POST['id'].";".$_POST['pwd'].";".$_POST['nickname'].";".$_POST['proxy'].";0;;\n");
		fclose($handle);
		msgBox("?login","使用者".$_POST['id']."已經建立");
	}
?>
<?include("footer.php");?>