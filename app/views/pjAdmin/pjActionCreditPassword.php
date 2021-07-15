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
	if (isset($_GET['err']))
	{
		$titles = __('error_titles', true);
        $bodies = __('error_bodies', true);
        if ($_GET['err'] == 'ACP01') {
            pjUtil::printNotice('Error', 'Please enter your new Credit Password');
        } else if ($_GET['err'] == 'ACP02') {
            pjUtil::printNotice('Error', 'Current Credit Password is incorrect');
        } else if ($_GET['err'] == 'ACP03') {
            pjUtil::printNotice('Success', 'Credit Password updated successfully');
        }
    }
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdmin&amp;action=pjActionCreditPassword">Credit Password</a></li>
		</ul>
	</div>	

    <div style="margin:20px 0px 10px 0px;color: #515050;font-size: 18px;">
        Change Credit Password
    </div>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdmin&amp;action=pjActionCreditPassword" method="post" id="frmUpdateProfile" class="form pj-form">
		<input type="hidden" name="password_update" value="1" />
        <?php if ($tpl['arr']['credit_password']) { ?>
		<p>
			<label class="title">Current Credit Password:</label>
			<span class="pj-form-field-custom pj-form-field-custom-before">
				<span class="pj-form-field-before"><abbr class="pj-form-field-icon-password"></abbr></span>
				<input type="password" name="current_password" id="current_password" class="pj-form-field w200" value=""  autocomplete="off" />
			</span>
            <div>
                (If you forgot your Current Credit Password then use the link 'Get Credit Password' on the left main menu.)
            </div>
		</p>
        <?php } ?>
		<p>
			<label class="title">New Credit Password:</label>
			<span class="pj-form-field-custom pj-form-field-custom-before">
				<span class="pj-form-field-before"><abbr class="pj-form-field-icon-password"></abbr></span>
				<input type="password" name="new_password" id="new_password" class="pj-form-field required w200" value="" autocomplete="off" />
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave', false, true); ?>" class="pj-button" />
		</p>
	</form>
	<?php
}
?>