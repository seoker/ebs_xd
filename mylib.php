<?
function msgBox($url,$msg)
{
	include_once("header.php");
?>
<table style="width:90%; height:90%; border:1px #333 solid;">
<tr><td style="background-color:#aaa; color:#333"><?=$msg?></td></tr>
</table>
<script language="javascript">
	setTimeout("location.href='<?=$url?>'",2000);
</script>
<?
	exit;
}
function checkpwd($id, $pwd, $user_dat)
{
	if (file_exists($user_dat))
	{
		$user_data = file($user_dat);
		for ($i=0; $i<count($user_data); ++$i)
		{
			$user_array = split(";", $user_data[$i]);
			//$user_id, $user_pwd, $user_nickname, $user_proxy, $user_times, $user_lastlogin
			if ($user_array[0] == $id && $user_array[1] == $pwd)
				return $user_array;
		}
	}
	return false;

}
function myPost($url, $user_agent, $proxy_server, $fields)
{
	if (!$user_agent)
		$user_agent = "MyBrowser v0.0 Windows 7";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_REFERER, $url);
	if (isset($fields))
	{
		foreach ($fields as $key=>$value)
			$fields_string .= $key.'='.$value.'&';
		rtrim($fields_string,'&');
		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	}
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	if ($proxy_server)
		curl_setopt($ch, CURLOPT_PROXY, "$proxy_server");
	if (!($result = curl_exec($ch))) return false;
	curl_close($ch);
	return $result;
}
function array_delete_key($arr, $k)
{
	$newarr = array();
	foreach ($arr as $key=>$value)
		if ($k != $key)
			$newarr[$key] = $value;
	return $newarr;
}
function array_delete($arr, $v)
{
	$newarr = array();
	foreach ($arr as $key=>$value)
		if ($v != $value)
			$newarr[$key] = $value;
	return $newarr;
}
?>