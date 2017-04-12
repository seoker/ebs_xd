<?
	if (basename($_SERVER['SCRIPT_NAME']) != $filename)
		die("請勿直接使用絕對路徑");
	include_once("header.php");
?>

<div class="box">
<div style="width:100%; padding:5px 0px 5px 0px; background-color:#aaa; text-align:center">Login</div>
<div style="text-align:center; margin:5px;">
<form action="?config" method="post" name="MyForm" onkeydown="if(event.keyCode == 13) this.submit()">
	<table style="margin:5px auto 5px auto;">
	<tr>
		<td style="text-align:right"><font style="margin-bottom:4px; display:block">EBS帳號：</td>
		<td style="text-align:left"><input type="text" name="id" class="inputfield" onmouseover="this.focus()" value="<?=$_COOKIE['id']?>"></td>
	</tr>
	<tr>
		<td style="text-align:right"><font style="margin-bottom:4px; display:block">EBS密碼：</td>
		<td style="text-align:left"><input type="password" name="pwd" class="inputfield" onmouseover="this.focus()" value="<?=$_COOKIE['pwd']?>"></td>
	</tr>
	<tr>
		<td style="text-align:right"></td>
		<td style="text-align:left"><input type="checkbox" name="usecookie" value="true" <?=$_COOKIE['usecookie']?>> 記錄密碼</td>
	</tr>
	</table>
	<input type="button" value="登入" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="MyForm.submit()">
	<input type="button" value="註冊" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.href='?reg'">
	<input type="button" value="站管" class="submit" onmouseover="this.className='submitH'" onmouseout="this.className='submit'" onclick="location.href='?admin'">
</form>
<script language="Javascript">
	document.MyForm.id.focus();
</script>
</div>
</div>
<?include("footer.php");?>