
    <div id="footer">
        <div class="container">
            <div class="row">
                <div class="footer-section col-md-1">
                    <a href="#" title="Mazhandu Family Bus Services" rel="home"><img src="<?php echo $tpl['base_url']; ?>app/web/img/mazhandu-logo-footer.png" class="logo-footer flex fadeInUp         animated" alt="Mazhandu Family Bus Services" style="visibility: visible; animation-name: fadeInUp;">
                    </a>
                </div>
                <!--end footer-section-->

                <div class="footer-section col-md-6 col-sm-6 col-xs-12">
                    <a href="<?php echo $tpl['base_url']; ?>index.php?controller=pjPages&action=pjActionTerms">Terms & Conditions</a>
                    <a style="margin-left:100px;" href="<?php echo $tpl['base_url']; ?>index.php?controller=pjPages&action=pjActionPrivacy">Privacy Policy</a>
                </div>
                
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