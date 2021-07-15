<!DOCTYPE html>
<html dir="<?php echo isset($_GET['dir']) ? $_GET['dir'] : ''; ?>">
	<head>
		<title>Bus Reservation System | Preview</title>
		<meta charset="utf-8">
		<meta name="fragment" content="!">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	    <link href="core/framework/libs/pj/css/pj.bootstrap.min.css" type="text/css" rel="stylesheet" />
  		<link href="index.php?controller=pjFrontEnd&action=pjActionLoadCss<?php echo isset($_GET['theme']) ? '&theme=' . $_GET['theme'] : null; ?>" type="text/css" rel="stylesheet" />
	<head>
	<body>
		<div style="max-width: 1024px;">
			<script type="text/javascript" src="index.php?controller=pjFrontEnd&action=pjActionLoad<?php echo isset($_GET['locale']) ? '&locale=' . $_GET['locale'] : NULL;?><?php echo isset($_GET['hide']) ? '&hide=' . $_GET['hide'] : NULL;?>&theme=<?php echo $_GET['theme'];?>"></script>
		</div>
	</body>
</html>