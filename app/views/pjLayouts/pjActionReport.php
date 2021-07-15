<!doctype html>
<html>
	<head>
		<title>Bus Schedule Script | Report</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<link type="text/css" rel="stylesheet" href="<?php echo PJ_INSTALL_URL . PJ_CSS_PATH; ?>report.css" media="screen, print" />
		<?php
		foreach ($controller->getJs() as $js)
		{
			echo '<script src="'.(isset($js['remote']) && $js['remote'] ? NULL : PJ_INSTALL_URL).$js['path'].htmlspecialchars($js['file']).'"></script>';
		} 
		?>
	</head>
	<body>
		<div id="container">
			<?php require $content_tpl; ?>
		</div>
	</body>
</html>