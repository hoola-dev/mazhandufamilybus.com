<script language="javascript">
var base_url = '<?php echo PJ_INSTALL_URL; ?>';
</script>
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
	if (isset($_GET['err']) && $_GET['err'] == 'ACU01') {
        pjUtil::printNotice('Error', 'Credit Password is incorrect');
    } else if (isset($_GET['err']) && $_GET['err'] == 'ACU02') {
        pjUtil::printNotice('Error', 'Sorry, some technical problem occured');
    } else if (isset($_GET['err']) && $_GET['err'] == 'ACU05') {
        pjUtil::printNotice('Error', 'Sorry, some technical problem occured');
    }

	pjUtil::printNotice('Edit Agent Credit', 'Use the form below to update agent credit.'); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminAppUsers&amp;action=pjActionCreditUpdate" method="post" id="frmUpdateCredit" class="form pj-form">
		<input type="hidden" name="credit_update" id="credit_update" value="1" />
        <input type="hidden" name="user_id" id="user_id" value="<?php echo (int) $tpl['arr']['id']; ?>" />
        <p>
			<label class="title">Agent Name</label>
			<span class="inline_block">
				<?php echo $tpl['arr']['name']; ?>
			</span>
		</p>		
		<p>
			<label class="title">Agent Email</label>
			<span class="inline_block">
				<?php echo $tpl['arr']['email']; ?>
			</span>
		</p>
        <p>
			<label class="title">Last updated By</label>
			<span class="inline_block">
				<?php echo $tpl['arr']['last_added_by']; ?>
			</span>
		</p>
        <p>
			<label class="title">Last updated On</label>
			<span class="inline_block">
				<?php echo $tpl['arr']['created']; ?>
			</span>
		</p>
        <p>
			<label class="title">Your Credit Password:</label>
			<span class="pj-form-field-custom pj-form-field-custom-before">
				<span class="pj-form-field-before"><abbr class="pj-form-field-icon-password"></abbr></span>
				<input type="password" name="credit_password" id="credit_password" class="pj-form-field required w200" value=""  autocomplete="off" />
			</span>
		</p>
		<p>
			<label class="title">Agent Credit</label>
			<span class="inline_block">				
				<input type="text" name="credit" id="credit" class="pj-form-field required w200" value="<?php echo pjSanitize::html($tpl['arr']['credit']); ?>" />
			</span>
		</p>		
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave', false, true); ?>" class="pj-button"/>
			<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminAppUsers&action=pjActionIndex';" />
		</p>
	</form>
	<?php
}
?>