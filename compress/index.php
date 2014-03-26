<?php
session_start();

error_reporting(E_ALL ^ E_STRICT);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
<script type="application/javascript" src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
</head>
<body>
<?php
if(isset($_POST['folder']) AND count(explode("\\", $_POST['folder'])) < 3)
	{
		echo "TOO CLOSE TO THE ROOT!";
	}
else if(isset($_POST['folder']))
	{
		$stime = time();
		$dir = explode("\\", $_POST['folder']);
		if(strpos($dir[(count($dir)-1)], ".") !== FALSE)
			{
				unset($dir[(count($dir)-1)]);
			}
		
		$target_dir = $dir;
		$target_dir[(count($target_dir)-1)] = $target_dir[(count($target_dir)-1)]."_min";
		
		$target_dir = join("\\", $target_dir).'\\';
		$dir = join("\\", $dir).'\\';
		
		$_SESSION['directories'] = 0;
		$_SESSION['copied'] = 0;
		$_SESSION['min_js'] = 0;
		$_SESSION['min_css'] = 0;
		$_SESSION['min_html'] = 0;
		$orig_f_size = 0;
		$new_f_size = 0;
		echo $dir."<br/>";
		browseDir($dir, $target_dir, 0);
		
		exec("dir /w /s ".$dir, $op, $err);
		foreach($op as $index => $line)
			{
				if(strpos($line, "Total Files Listed:") !== FALSE)
					{
						$op[$index+1] = explode("(s)", $op[$index+1]);
						$orig_f_size = str_replace(",", "", trim(substr($op[$index+1][1], 0, -6)));
						break;
					}
			}
		unset($op, $err);
		exec("dir /w /s ".$target_dir, $op, $err);
		foreach($op as $index => $line)
			{
				if(strpos($line, "Total Files Listed:") !== FALSE)
					{
						$op[$index+1] = explode("(s)", $op[$index+1]);
						$new_f_size =  str_replace(",", "", trim(substr($op[$index+1][1], 0, -6)));
						break;
					}
			}
		
		echo ("
		<br/>
		<br/>
		<table>
			<tr>
				<td>
					ORIGIN DIR
				</td>
				<td>
					".$dir."
				</td>
			</tr>
			<tr>
				<td>
					TARGET DIR
				</td>
				<td>
					".$target_dir."
				</td>
			</tr>
			<tr>
				<td colspan=\"2\">
					<strong>Results</strong>
				</td>
			</tr>
			<tr>
				<td>
					Minified JS files
				</td>
				<td>
					".number_format($_SESSION['min_js'])."
				</td>
			</tr>
			<tr>
				<td>
					Minified CSS files
				</td>
				<td>
					".number_format($_SESSION['min_css'])."
				</td>
			</tr>
			<tr>
				<td>
					Minified HTML files
				</td>
				<td>
					".number_format($_SESSION['min_html'])."
				</td>
			</tr>
			<tr>
				<td>
					Just copied files
				</td>
				<td>
					".number_format($_SESSION['copied'])."
				</td>
			</tr>
			<tr>
				<td>
					Total directories
				</td>
				<td>
					".number_format($_SESSION['directories'])."
				</td>
			</tr>
			<tr>
				<td>
					Total files
				</td>
				<td>
					".number_format($_SESSION['min_js']+$_SESSION['min_css']+$_SESSION['min_html']+$_SESSION['copied'])."
				</td>
			</tr>
			<tr>
				<td colspan=\"2\">
					<strong>Folder sizes</strong>
				</td>
			</tr>
			<tr>
				<td>
					Original size
				</td>
				<td>
					".number_format($orig_f_size)."
				</td>
			</tr>
			<tr>
				<td>
					Minimised size
				</td>
				<td>
					".number_format($new_f_size)."
				</td>
			</tr>
			<tr>
				<td>
					Difference
				</td>
				<td>
					".(number_format((1-$new_f_size/$orig_f_size)*100, 1))."% smaller from original
				</td>
			</tr>
		</table>
		");
		
		$etime = time();
		
		echo "TIME TAKEN: ".($etime-$stime);
	}

function browseDir($d, $c, $inset = 0)
	{
		$spaces = "";
		for($i=0 ; $i<$inset+5 ; $i++)
			{
				if($i%5 == 0 AND $i>5)
					{
						$spaces .= "|";
					}
				$spaces .= "&nbsp;";
			}
		
		if(is_dir($d))
			{
				if($dh = opendir($d))
					{
						while (($file = readdir($dh)) !== false)
							{
								if($file != "." AND  $file != "..")
									{
										$filetype = array_pop(explode(".", $file));
										echo $spaces.'&#8735;';//.$file.'<br/>';
										$f = $d.$file;
										$t = $c.$file;
										
										if(filetype($d.$file) == "dir")
											{
												// create dir
												$_SESSION['directories']++;
												$f = $f.'\\';
												$t = $t.'\\';
												if(!is_dir($t))
													{
														exec("mkdir ".$t);
													}
												echo $file."<br/>";
												browseDir($f, $t, ($inset+5));
											}
										else
											{
												if($filetype == "js")
													{
														//minimise js
														$_SESSION['min_js']++;
														echo $file."<br/>";
														exec("java -jar c:\wamp\www\compress\yuicompressor.jar ".$f." -o ".$t);
													}
												else if($filetype == "css")
													{
														//minimise css
														$_SESSION['min_css']++;
														echo $file."<br/>";
														exec("java -jar c:\wamp\www\compress\yuicompressor.jar ".$f." -o ".$t);
													}
												else if($filetype == "html")
													{
														//minimise html
														$_SESSION['min_html']++;
														echo $file."<br/>";
														exec("java -jar c:\wamp\www\compress\htmlcompressor.jar ".$f." -o ".$t);
													}
												else
													{
														//just copy file
														$_SESSION['copied']++;
														echo $file."<br/>";
														exec("copy ".$f." ".$t);
													}
											}
									}
							}
						closedir($dh);
					}
			}
	}

?>
<form action="" method="post" style="margin-top:50px;">
	<input type="file" value="" onchange="$('#folder').val($(this).val())" /><br/>
	<input type="text" value="" onchange="$('#folder').val($(this).val())" /><br/>
	<input type="hidden" name="folder" value="" id="folder" />
	<input type="submit" value="select" id="submitFolder" />
</form>
</body>
</html>