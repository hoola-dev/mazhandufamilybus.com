<!doctype html>
<html>
	<head>
		<title>Bus Reservation System by ParezaGroup.com</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">
		<?php
		foreach ($controller->getCss() as $css)
		{
			echo '<link type="text/css" rel="stylesheet" href="'.(isset($css['remote']) && $css['remote'] ? NULL : PJ_INSTALL_URL).$css['path'].$css['file'].'" />';
		}
		
		foreach ($controller->getJs() as $js)
		{
			echo '<script src="'.(isset($js['remote']) && $js['remote'] ? NULL : PJ_INSTALL_URL).$js['path'].$js['file'].'"></script>';
		}
		?>
	</head>
	<body>
		<div id="container">
			<!-- <div id="header">
				<div id="logo">
					<a href="https://mazhandufamilybus.com/" target="_blank" rel="nofollow">Mazhandu Family Bus Reservation System</a>
					<span><?php //echo PJ_SCRIPT_VERSION;?></span>
				</div>
			</div> -->
			<div id="middle">
				<div id="login-content">
				<?php require $content_tpl; ?>
				</div>
			</div> <!-- middle -->
		</div> <!-- container -->
		<!-- <div id="footer-wrap">
			<div id="footer">
			   	<p>Copyright &copy; <?php echo date("Y"); ?> <a href="https://www.parezagroup.com" target="_blank">ParezaGroup.com</a></p>
	        </div>
        </div> -->
	</body>
</html>
