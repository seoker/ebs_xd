<?
	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		die("請勿直接使用絕對路徑");

	$ebs_id = $_COOKIE['id'];
	$ebs_pwd = $_COOKIE['pwd'];

	if (!($User = checkpwd($ebs_id, $ebs_pwd, $user_dat)))
		msgBox("?","帳號或密碼錯誤，請重新登入");

	/* 代理伺服器處理 */
	$proxy_list = file($proxy_dat);
	$proxy_option = "";
	$proxy_js = "";
	$proxy_sv_js = "";
	$proxy_count = count($proxy_list);
	for ($i=0; $i<$proxy_count; ++$i)
	{
		list($sv_name, $sv_url, $user) = split(";", $proxy_list[$i]);
		$proxy_option .= "<option value='$i'".(($user)?" style='color:red'":"").(($User[3] == $sv_url)?" selected":"").">$sv_name\n";
		$proxy_sv_js .= "\tproxy_sv_list[$i] = \"".$sv_url."\";\n";
		if ($user)
			$proxy_js .= "\tproxy_list[$i] = \"$user\";\n";
	}


	/* 攻擊名單處理 */
	$list_file = array();
	for ($i=0; $i<5; ++$i)
		array_push($list_file, "$dat_dir/$ebs_id/list".($i+1).".dat");

	$attack_list = "";
	$opponent = array();
	for ($i=0; $i<count($list_file); ++$i)
	{
		$opponent[$i] = array();
		if (file_exists($list_file[$i]) && filesize($list_file[$i]))
		{
			$attack_list .= "<option value='$i'>名單" . ($i+1);
			$opponent[$i] = split("\n", file_get_contents($list_file[$i]));
		}
		else
		{
			$attack_list .= "<option style='color:#444' value='$i'>名單".($i+1)." (未設定)";
			$opponent[$i] = Null;
		}
	}

	include_once("header.php");
?>
<script type="text/javascript" language="javascript">
	var http_request = false;
	function makePOSTRequest(url, parameters, act)
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
		http_request.onreadystatechange = act;
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

	function alertUpdate() {
		if (http_request.readyState == 4) {
			if (http_request.status == 200)
			{
				alert(http_request.responseText);
				document.location.reload();
			}
			else
            	alert("傳送要求時發生錯誤");
		}
	}

	function alertProxy() {
		if (http_request.readyState == 4) {
			if (http_request.status == 200)
				document.getElementById('proxy_box').innerHTML = document.getElementById('proxy_box').innerHTML + http_request.responseText;
			else
            	alert("傳送要求時發生錯誤");
		}
	}

	function alertProxySave() {
		if (http_request.readyState == 4) {
			if (http_request.status == 200)
				alert(http_request.responseText);
			else
            	alert("傳送要求時發生錯誤");
		}
	}
	
</script>
<script language="javascript">
	var proxy_sv = "<?=$User[3]?>";
	var proxy_list = new Array(<?=$proxy_count?>);
	var proxy_sv_list = new Array(<?=$proxy_count?>);
<?=$proxy_js?>
<?=$proxy_sv_js?>
	function proxyDescription(i) {
		if (i == "other")
		{
			var test = prompt("請輸入代理伺服器位置\n如：proxy.cs.nctu.edu.tw:3128", proxy_sv);
			if (test) proxy_sv = test;
		}
		else
			proxy_sv = proxy_sv_list[i];
		document.getElementById('proxy_box').innerHTML = proxy_sv + ((proxy_list[i])?"使用："+proxy_list[i]:"" ) + "<br>連線品質：";
		proxyCheck();
	}
	function proxyCheck()
	{
		var poststr;
		poststr = "sv=" + encodeURI( proxy_sv );
		makePOSTRequest('?proxy', poststr, alertProxy);
	}
	function proxySave() {
		var poststr;
		poststr = "sv=" + encodeURI( proxy_sv );
		makePOSTRequest('?save_proxy', poststr, alertProxySave);
	}

</script>
<script language="Javascript">
	var opponent = new Array(5);
<?
	for ($i=0; $i<5; ++$i)
		echo "\topponent[$i] = new Array(".(($opponent[$i])?"\"".join("\",\"", $opponent[$i])."\"":"").");\n";
?>
	function uselist(obj)
	{
		var w = 120, h = 300;
	    var l = (screen.width - w)/2;
	    var t = (screen.height - h)/2;
		var poststr;
		var set = document.MyForm.attack_list.value;
		poststr = "opponent=" + opponent[set].join(";");
		if (navigator.appName == "Microsoft Internet Explorer")
		{
			var myobj = FFF;
			window.showModalDialog("?player&"+poststr+"&num="+Math.random(),myobj,"center:yes;resizable:no;scroll:on;status:no;dialogWidth:"+w+"px;dialogHeight:"+h+"px;dialogLeft:"+l+"px;dialogTop:"+t+"px");
		}
		else
			window.open("?player&"+poststr,'newWin',"modal=yes,status=no,toolbar=no,menubar=no,resizable=no,scrollbars=yes,width="+w+",height="+h+",left="+l+",top="+t);
		if (document.MyForm.my_opp_list.value)
		{
			renewList(obj, document.MyForm.my_opp_list.value);
			document.MyForm.my_opp_list.value = "";
		}
	}
	function addItem(obj, valObj)
	{
		jsAddItemToSelect(obj, valObj.value, valObj.value);
		var set = document.MyForm.attack_list.value;
		opponent[set].push(valObj.value);
		valObj.value = "";
	}
	function renewList(obj, val)
	{
		var set = document.MyForm.attack_list.value;
		var list = val.split(";");
		opponent[set] = new Array();
		for (var i=0; i<list.length; ++i)
			opponent[set].push(list[i]);
		loadItem(obj, set);
	}
	function sortItem(obj)
	{
		var set = document.MyForm.attack_list.value;
		opponent[set].sort();
		loadItem(obj, set);
	}
	function reverseItem(obj)
	{
		var set = document.MyForm.attack_list.value;
		opponent[set].reverse();
		loadItem(obj, set);
	}
	function randOrderItem(obj)
	{
		var set = document.MyForm.attack_list.value;
		opponent[set].sort(function(){return Math.random()>0.5?-1:1;});
		loadItem(obj, set);
	}
	function loadItem(obj, set)
	{
		obj.options.length = 0;
		for(var i=0; i<opponent[set].length; ++i)
			jsAddItemToSelect(obj, opponent[set][i], opponent[set][i]);
	}
	function delItem(obj)
	{
		jsRemoveSelectedItemFromSelect(obj);
		var set = document.MyForm.attack_list.value;
		opponent[set] = jsAllItems(obj);
	}
	function cleanItem(obj)
	{
		obj.options.length = 0;
		var set = document.MyForm.attack_list.value;
		opponent[set] = new Array();
	}
	function moveUp(obj)
	{
		jsMoveUp(obj);
		var set = document.MyForm.attack_list.value;
		opponent[set] = jsAllItems(obj);
	}
	function moveDown(obj)
	{
		jsMoveDown(obj);
		var set = document.MyForm.attack_list.value;
		opponent[set] = jsAllItems(obj);
	}
	function saveList() {
		var poststr;
		poststr = "opponent[0]=" + encodeURI( opponent[0].join(";") );
		for (var i=1; i<5; ++i)
			poststr = poststr + "&opponent[" + i +"]=" + encodeURI( opponent[i].join(";") );
		makePOSTRequest('?save_list', poststr, alertContents);
	}
	function updateList() {
		makePOSTRequest('?update_list', "", alertUpdate);
	}
</script>
<script language="Javascript" src="select.js"></script>
<div class="box">
<div style="width:100%; padding:5px 0px 5px 0px; background-color:#aaa; text-align:center">Opponent Setup</div>
<div style="text-align:center; margin:5px;">
<form method="post" id="FFF" name="MyForm" onkeydown="if(event.keyCode == 13) { addItem(document.MyForm.lists, document.MyForm.pname); return false; }">
<input type="hidden" name="my_opp_list" value="">
	<table style="margin:5px auto 5px auto;">
	<tr>
		<td style="text-align:right">名單：</td>
		<td style="text-align:left"><select class="selectfield" name="attack_list" onchange="loadItem(document.MyForm.lists, this.value)"><?=$attack_list?></select></td>
	</tr>
	<tr>
		<td style="text-align:right"><font style="margin-bottom:4px; display:block">新增對象：</td>
		<td style="text-align:left">
			<input type="text" name="pname" class="inputfield" style="width:120px; float:left" onmouseover="this.focus()">
			<div style="float:right; width:100px; margin-left:4px">
			<input type="button" value="新增" style="height:22px;padding:2px" class="submit" onmouseover="this.className='submitH'"
			onmouseout="this.className='submit'" onclick="addItem(document.MyForm.lists, document.MyForm.pname);">
			</div>
		</td>
	</tr>
	<tr>
		<td style="text-align:right"></td>
		<td style="text-align:left">
			<select size="6" name="lists" class="selectfield" style="width:120px; float:left" multiple></select>
			<div style="float:right; width:100px; margin-left:4px">
			<input type="button" value="刪除" style="height:22px; padding:2px;" class="submit" onmouseover="this.className='submitH'"
			onmouseout="this.className='submit'" onclick="delItem(document.MyForm.lists)" /> 
			<input type="button" value="列表" style="height:22px; padding:2px;" class="submit" onmouseover="this.className='submitH'"
			onmouseout="this.className='submit'" onclick="uselist(document.MyForm.lists)" /><br>
			<input type="button" value="清空" style="height:22px; padding:2px; margin-top:5px;" class="submit" onmouseover="this.className='submitH'"
			onmouseout="this.className='submit'" onclick="if (confirm('確定清空?')) cleanItem(document.MyForm.lists)" /> 
			<input type="button" value="排序" style="height:22px; padding:2px;" class="submit" onmouseover="this.className='submitH'"
			onmouseout="this.className='submit'" onclick="sortItem(document.MyForm.lists)" /><br>
			<input type="button" value="上移" style="height:22px; padding:2px; margin-top:5px;" class="submit" onmouseover="this.className='submitH'"
			onmouseout="this.className='submit'" onclick="moveUp(document.MyForm.lists)" />
			<input type="button" value="反序" style="height:22px; padding:2px;" class="submit" onmouseover="this.className='submitH'"
			onmouseout="this.className='submit'" onclick="reverseItem(document.MyForm.lists)" /><br>
			<input type="button" value="下移" style="height:22px; padding:2px; margin-top:5px;" class="submit" onmouseover="this.className='submitH'"
			onmouseout="this.className='submit'" onclick="moveDown(document.MyForm.lists)" />
			<input type="button" value="亂序" style="height:22px; padding:2px;" class="submit" onmouseover="this.className='submitH'"
			onmouseout="this.className='submit'" onclick="randOrderItem(document.MyForm.lists)" />
			</div>
		</td>
	</tr>
	</table>
	<input type="button" value="存檔" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="saveList()">
	<input type="button" value="預設" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.href='?setup'">
	<input type="button" value="更新" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="updateList()">
	<input type="button" value="主頁" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.href='?config'">
</form>

</div>
<div style="width:100%; padding:5px 0px 5px 0px; background-color:#aaa; text-align:center">Proxy Setup</div>
<form method="post" name="ProxyForm">
	<table style="width:100%;">
	<tr>
	<td style="text-align:right">
		代理伺服器：
		<select name="proxy_sv" onchange="proxyDescription(this.value)" class="inputfield">
			<?=$proxy_option?>
			<option value="other">其他
		</select>
		<div id="proxy_box" style="margin:5px auto 5px auto; background-color:#eee; color:#555; border:1 #555 solid; width:230px; text-align:left; padding:5px;">
		</div>
	</td>
	<td style="text-align:center; width:80px">
		<input type="button" value="設定" class="submit" style="height:30px; width:50px" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="proxySave()">
	</td>
	</tr>
	</table>
</form>
</div>
<script language="Javascript">
	loadItem(document.MyForm.lists, 0);
	proxyDescription(document.ProxyForm.proxy_sv.value);
</script>

<?include("footer.php");?>