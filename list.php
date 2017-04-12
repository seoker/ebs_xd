<?
	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		die("請勿直接使用絕對路徑");


	$ebs_id = $_COOKIE['id'];
	$ebs_pwd = $_COOKIE['pwd'];

	if (!checkpwd($ebs_id, $ebs_pwd, $user_dat))
	{
		echo iconv("big5","UTF-8", "帳號或密碼錯誤");
		exit;
	}

	$list_file = "$dat_dir/$ebs_id/list".($_POST['list']+1).".dat";
	if (!file_exists($list_file) || !filesize($list_file))
	{
		echo iconv("big5","UTF-8", "無此列表");
		exit;
	}
	echo iconv("big5","UTF-8", file_get_contents($list_file));
?>