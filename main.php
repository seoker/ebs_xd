<?
	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		die("請勿直接使用絕對路徑");
?>
<html>
<head>
	<title>EBS XD</title>
	<link rel="shortcut icon" href="favicon.ico">
	<script language="Javascript">
		var obj = window.parent.document.getElementById('programname');
		if (obj)
			obj.innerText = "EBS XD Ver<?=$ver?>";
	</script>
</head>
<frameset rows="100%,*" border="0">
	<frame src="?login" name="main">
	<frame src="about:blank" name="hiddenframe">
</frameset>