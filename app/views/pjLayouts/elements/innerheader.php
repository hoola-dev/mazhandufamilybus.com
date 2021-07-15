<head>
    <meta charset="UTF-8">
    <title>Mazhandu Family Bus Services</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=9, IE=edge">
    <meta name="robots" content="noindex,follow">

    <link href="https://fonts.googleapis.com/css?family=Poppins:400,300,500,600,700&amp;subset=latin,devanagari,latin-ext" rel="stylesheet" type="text/css">
    <link rel="stylesheet" id="fontawesome-css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css?ver=4.4.1" type="text/css" media="all">
	<?php
		foreach ($controller->getCss() as $css)
		{
			echo '<link type="text/css" rel="stylesheet" href="'.(isset($css['remote']) && $css['remote'] ? NULL : PJ_INSTALL_URL).$css['path'].htmlspecialchars($css['file']).'" />';
		}
		foreach ($controller->getJs() as $js)
		{
			echo '<script src="'.(isset($js['remote']) && $js['remote'] ? NULL : PJ_INSTALL_URL).$js['path'].htmlspecialchars($js['file']).'"></script>';
		}
		?>

<script>
var base_url = '<?php echo $tpl['base_url']; ?>';
var is_home = 1;
</script>
<script type="text/javascript"> //<![CDATA[
var tlJsHost = ((window.location.protocol == "https:") ? "https://secure.trust-provider.com/" : "http://www.trustlogo.com/");
document.write(unescape("%3Cscript src='" + tlJsHost + "trustlogo/javascript/trustlogo.js' type='text/javascript'%3E%3C/script%3E"));
//]]>
</script>
</head>
 <?php 
      if($_REQUEST['action']=='pjActionAgent') {
          
          $mTitle ="Agents";
          $sT1="Our";
          $sT2="Agents";
      }
      if($_REQUEST['action']=='pjActionTicket') {
          
          $mTitle ="Retrieve Ticket";
          $sT1="My";
          $sT2="Tickets";
      }
      if($_REQUEST['action']=='pjActionBooking') {
          
          $mTitle ="Booking";
          $sT1="Search";
          $sT2="Tickets";
      }
      if($_REQUEST['action']=='pjActionAbout') {
          
          $mTitle ="About";
          $sT1="About";
          $sT2="Us";
      }
      if($_REQUEST['action']=='pjActionContact') {
          
          $mTitle ="Contact";
          $sT1="Contact";
          $sT2="Us";
      }
      if($_REQUEST['action']=='pjActionFAQ') {
          
          $mTitle ="FAQ";
          $sT1="FAQ";
          $sT2="";
      }
      if($_REQUEST['action']=='pjActionPrivacy') {
          
          $mTitle ="Privacy Policy";
          $sT1="Privacy";
          $sT2="Policy";
      }
      if($_REQUEST['action']=='pjActionTerms') {
          
          $mTitle ="Terms & Conditions";
          $sT1="Terms & ";
          $sT2="Conditions";
      }
      if($_REQUEST['action']=='pjActionDisclaimer') {
          
          $mTitle ="Disclaimer";
          $sT1="Disclaimer";
          $sT2="";
      }
      if($_REQUEST['action']=='pjActionList') {
          
          $mTitle ="Agents";
          $sT1="Our";
          $sT2="Agents";
      }
      
      ?>
<body>
<div class="hero-image">
    <div id="home">
        <div class="hero-overlay">
            <header id="site-header">
                <div class="row">
                    <nav id="top-nav" class="navbar navbar-default navbar-fixed-top navbar-scroll-changed fixed-header">

                        <div class="container">

                            <div class="col-md-2 col-sm-1 col-xs-12">
                                <div class="branding">
                                    <a href="<?php echo $tpl['base_url']; ?>" title="Home is where the heart is" rel="home"><img id="logo" src="<?php echo $tpl['base_url']; ?>app/web/img/mazhandu-logo-active.png" alt="Home is where the heart is">
                                    </a>
                                </div>
                                <!--end branding-->

                                <div class="navbar-header">
                                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                                        <span class="sr-only">Toggle navigation</span>
                                        <span class="icon-bar"></span>
                                        <span class="icon-bar"></span>
                                        <span class="icon-bar"></span>
                                    </button>
                                </div>
                                <!--end navbar-header-->
                            </div>
                            <!--end col-md-2 col-sm-2 col-xs-12-->

                            <div class="col-md-10 col-sm-11 col-xs-12">
                                <div class="navbar-collapse collapse" id="bs-example-navbar-collapse-1" aria-expanded="true" style="height: 1px; padding-top: 10px;" role="navigation">

                                    <div class="menu-primary-menu-container">
                                        <ul id="menu-main-top-navigation" class="menu nav navbar-nav navbar-right">
                                            <li class="activenav"><a href="<?php echo PJ_INSTALL_URL; ?>">Home</a>
                                            </li>                                            
                                        </ul>
                                    </div>
                                    <!--end menu-->
                                </div>
                                <!--end navbar-collapse-->

                            </div>
                            <!--end col-md-10 col-sm-10 col-xs-12-->

                        </div>
                        <!--end container-->
                    </nav>

                </div>
                <!--end row-->
                <div class="main-message">
                    <div class="container">
                        <h1 class="flex fadeInUp    animated animated animated animated animated animated animated animated animated" style="visibility: visible; animation-name: fadeInUp;">Mazhandu Family Bus Services</h1>
                        <h1 class="flex fadeInUp    animated animated animated animated animated animated animated animated animated" style="visibility: visible; animation-name: fadeInUp;"><span><?php echo $sT1; ?></span> <?php echo $sT2; ?></h1>                       
                    </div>
                    <!--end container-->
                </div>
                <!--end main-message-->
            </header>
        </div>
        <!--end hero-overlay-->
    </div>
    <!--end acasa-->
</div>
<!--end hero-image-->
