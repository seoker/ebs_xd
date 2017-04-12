<?
	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		errmsg("請勿直接使用絕對路徑");

	$ebs_id = $_COOKIE['id'];
	$ebs_pwd = $_COOKIE['pwd'];

	if (!($User = checkpwd($ebs_id, $ebs_pwd, $user_dat)))
		errmsg("帳號或密碼錯誤，請重新登入");

	$postdata = array(
		"cmd"=>"MAIN_PAGE",
		"pname"=>$ebs_id,
		"pass"=>$ebs_pwd
	);

	$opponent = split(";", $_GET['opponent']);
	$choose = array();
	foreach ($opponent as $val)
		$choose[$val] = true;

	/* 取得所有玩家列表 */
	if (!($retdata = myPost($ebs_url."/kaigisitu.cgi", NULL, $User[3], $postdata)))
		errmsg("無法連接伺服器");
	preg_match("/<select name=aiteA .*>\n\n<option value=\"\">未選擇\n((<option>[^<>]+)+)\n<\/select>/", $retdata, $regs);
	preg_match_all("/<option>([^<>]+)/", $regs[1], $test);
	$opp = array();
	for ($i=0; $i<count($test[1]); ++$i)
		array_push($opp, $test[1][$i]);
	include_once("header.php");
?>
<script language="Javascript">
	function sendChoice()
	{
		var objs = document.getElementsByName("choose");
		var myObj = window.dialogArguments;
		var list = new Array();
		if (!myObj)
			myObj = window.opener.document.FFF;
		for (var i=0; i<objs.length; i++)
			if (objs[i].checked)
				list.push(objs[i].value);
		myObj.my_opp_list.value = list.join(";");
		window.close();
	}

</script>
<div class="box" style="width:90%">
	<div style="width:100%; padding:5px 0px 5px 0px; background-color:#aaa; text-align:center">Choose</div>
	<div style="margin:5px; height:200px; text-align:left; Overflow:auto;">
<?
	foreach ($opp as $val)
		echo "\t<input type='checkbox' name='choose' value='$val'".(($choose[$val])?" checked":"")."> $val<br>\n";
?>
	</div>
	<input type="button" value="選取" onclick="sendChoice()" class="submit" style="margin-bottom:10px" onmouseover="this.className='submitH'" onmouseout="this.className='submit'">
</div>
</body>
</html>
<?
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