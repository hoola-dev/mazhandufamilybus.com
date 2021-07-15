<?php
if (isset($tpl['status']))
{
	$status = __('status', true);
	switch ($tpl['status'])
	{
		case 2:
			pjUtil::printNotice(NULL, $status[2]);
			break;
	}
} else {
    if ($tpl['is_reset']) {
        if ($tpl['is_sent']) {
            pjUtil::printNotice('Success', 'Your current credit password is sent to your phone and email.');    
        } else {
            pjUtil::printNotice('Error', 'Cannot sent credit password. Try again.');   
        }
    }
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdmin&amp;action=pjActionCreditPassword">Credit Password</a></li>
		</ul>
	</div>
	
    <div style="margin:20px 0px; 20px 0px;">
        <div style="margin:0px 0px 10px 0px;color: #515050;font-size: 18px;">
            Get Credit Password
        </div>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdmin&amp;action=pjActionGetCreditPassword" method="post" id="frmResetPassword" class="form pj-form">
            <input type="hidden" name="reset_password" value="1" />
            <input type="submit" value="Get Credit Password" class="pj-button"/>
            <div style="margin-top:20px;">
                When clicking on the 'Get Credit Password' button we will send the current credit password to your phone and email.
            </div>
        </form>
    </div>    
	<?php
}
?>