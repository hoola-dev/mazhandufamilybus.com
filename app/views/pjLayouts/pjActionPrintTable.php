<!doctype html>
<html>
	<head>
		<title>Bus Schedule Script | Print</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<link type="text/css" rel="stylesheet" href="<?php echo PJ_INSTALL_URL . PJ_CSS_PATH; ?>print_table.css" media="screen, print" />
	</head>
	<body>
		<div id="container">
			<?php require $content_tpl; ?>
		</div>
		<script type="text/javascript">
		if (window.print) {
			window.print();
		}
		</script>
	</body>
</html>