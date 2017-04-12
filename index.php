<?
	$ver 		= "0.85beta";
	$dat_dir	= "./dat";
	$err_dir	= "./error";
	$ebs_url	= "http://evilfox001.hp.infoseek.co.jp/cgi-bin/ebs120plus";
	$proxy_dat	= "proxy.dat";
	$user_dat	= "./dat/list.dat";
	$filename	= "index.php";
	$pattern_dat= "./pattern.txt";
	$admin_pwd	= "{admin-password}";

	include_once("mylib.php");
	$query_string = split("&", $_SERVER['QUERY_STRING']);
	$act = $query_string[0];

	switch($act)
	{
		case "login":
			include_once("login.php");
			break;
		case "reg":
			include_once("reg.php");
			break;
		case "config":
			include_once("config.php");
			break;
		case "attack":
			include_once("attack.php");
			break;
		case "battle":
			include_once("battle.php");
			break;
		case "setup":
			include_once("setup.php");
			break;
		case "save_list":
			include_once("save_list.php");
			break;
		case "update_list":
			include_once("update_list.php");
			break;
		case "save_proxy":
			include_once("save_proxy.php");
			break;
		case "proxy":
			include_once("proxy.php");
			break;
		case "list":
			include_once("list.php");
			break;
		case "player":
			include_once("player.php");
			break;
		case "history":
			include_once("history.php");
			break;
		case "show_battle":
			include_once("show_battle.php");
			break;
		case "login_history":
			include_once("login_history.php");
			break;
		case "ch_weapon":
			include_once("ch_weapon.php");
			break;
		case "admin":
			include_once("admin.php");
			break;
		default:
			include_once("main.php");
	}
?>
