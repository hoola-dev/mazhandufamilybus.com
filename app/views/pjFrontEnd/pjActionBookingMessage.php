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
                        <h1 class="flex fadeInUp    animated animated animated animated animated animated animated animated animated" style="visibility: visible; animation-name: fadeInUp;">Booking Detail</h1>                       
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

<div style="margin:50px 20px 50px 20px;">
    <?php echo preg_replace('/\n/','<br>',$tpl['message']); ?>
</div>


<div id="footer">
        <div class="container">
            <div class="row">
                <div class="footer-section col-md-1">
                    <a href="#" title="Mazhandu Family Bus Services" rel="home"><img src="<?php echo $tpl['base_url']; ?>app/web/img/mazhandu-logo-footer.png" class="logo-footer flex fadeInUp         animated" alt="Mazhandu Family Bus Services" style="visibility: visible; animation-name: fadeInUp;">
                    </a>
                </div>
                <!--end footer-section-->

                <div class="footer-section col-md-6 col-sm-6 col-xs-12">
                    <p class="copyright flex fadeInUp         animated" style="visibility: visible; animation-name: fadeInUp; margin-top: 0px; margin-left: 12px;">© Copyright <?php echo date('Y'); ?> <strong>Mazhandu Family Bus Services.</strong>&nbsp;All rights reserved.
                        <br>
                        <br>Powered by <b>Zed Booking</b>
                    </p>
                </div>
                <!--end footer-section-->
                <div class="footer-section col-md-1 col-sm1 col-xs-12">
                    <div class="social copyright flex fadeInUp         animated" style="visibility: visible; animation-name: fadeInUp;">
                        <a href="https://www.facebook.com/Mazhandu-Family-Bus-Services-203889616435316" target="_blank"><i class="fa fa-facebook"></i></a>
                        <!-- <a href="#" target="_blank"><i class="fa fa-linkedin"></i></a>
                        <a href="#" target="_blank"><i class="fa fa-instagram"></i></a> -->
                    </div>
                    <!--end social-->
                </div>
                <div class="footer-section col-md-4 col-sm-4 col-xs-12" style="text-align:right;">
                    <script language="JavaScript" type="text/javascript">
                    TrustLogo("https://mazhandufamilybus.com/positivessl_trust_seal_lg_222x54.png", "CL1", "none");
                    </script>
                    <a  href="https://www.positivessl.com/" id="comodoTL">Positive SSL Wildcard</a>
                </div>
            </div>
            <!--end row-->
        </div>
        <!--end container-->
    </div>
    <!--end footer-->



    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js?ver=1.0"></script>
    <script type="text/javascript" src="<?php echo $tpl['base_url']; ?>app/web/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo $tpl['base_url']; ?>app/web/js/jquery-mobile.js?ver=1.0"></script>
    <script type="text/javascript" src="<?php echo $tpl['base_url']; ?>app/web/js/wow.min.js"></script>
    <script type="text/javascript" src="<?php echo $tpl['base_url']; ?>app/web/js/main.js?<?php echo PJ_CSS_JS_VERSION; ?>"></script>
    <script>
        //jQuery transitions init(wow + animated)
        wow = new WOW({
            boxClass: 'flex',
            animateClass: 'animated', // default
            offset: 0, // default
            mobile: true, // default
            live: true // default
        })
        wow.init();
    </script>
    <script type="text/javascript" src="<?php echo $tpl['base_url']; ?>app/web/js/jQuery.style.switcher.js"></script>
    <script type="text/javascript">
        $(function() {
            $('#colour-variations ul').styleSwitcher({
                defaultThemeId: 'flexible-switch',
                hasPreview: false,
                cookie: {
                    expires: 30,
                    isManagingLoad: true
                }
            });
            $('.option-toggle').click(function() {
                $('#colour-variations').toggleClass('sleep');
            });
        });
    </script>
<script language="JavaScript" type="text/javascript">
TrustLogo("https://mazhandufamilybus.com/", "CL1", "none");
</script>
<a  href="https://www.positivessl.com/" id="comodoTL">Positive SSL Wildcard</a>
</body>


