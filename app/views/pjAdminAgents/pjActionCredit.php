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
        pjUtil::printNotice('Error', 'Password is incorrect');
    } else if (isset($_GET['err']) && $_GET['err'] == 'ACU02') {
        pjUtil::printNotice('Error', 'Sorry, some technical problem occured');
    } else if (isset($_GET['err']) && $_GET['err'] == 'ACU06') {
        pjUtil::printNotice('Error', "You don't have enough Credit to add Credit to the Sub Agent");
    } else if (isset($_GET['err']) && $_GET['err'] == 'ACU05') {
        pjUtil::printNotice('Error', 'Sorry, some technical problem occured');
    }

	pjUtil::printNotice('Add Agent Credit', 'Use the form below to add agent credit.'); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminAgents&amp;action=pjActionCreditUpdate" method="post" id="frmUpdateCredit" class="form pj-form">
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
            <label class="title">Agent Commission %</label>
            <span class="inline_block">
               <?php echo $tpl['arr']['commission_percent']; ?>
            </span>
        </p>
        <p>
			<label class="title">Your Credit Password:</label>
			<span class="pj-form-field-custom pj-form-field-custom-before">
				<span class="pj-form-field-before"><abbr class="pj-form-field-icon-password"></abbr></span>
				<input type="password" name="credit_password" id="credit_password" class="pj-form-field required w200" value=""  autocomplete="off" />
                <!-- <input type="button" style="margin-left:20px;" value="Reset Password" class="pj-button" onclick="javascript:confirm_reset();"/>                 -->
            </span>
            <br><br>
            (If you forgot your Current Credit Password then use the link 'Get Credit Password' on the left main menu.)
        </p>
		<p>
			<label class="title">Agent Current Credit</label>
			<span class="inline_block">				
				<?php echo $tpl['arr']['credit']; ?>
			</span>
        </p>
        <p>
			<label class="title">Add Credit</label>
			<span class="inline_block">				
				<input type="text" name="credit" id="credit" class="pj-form-field required w200" value="" />
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave', false, true); ?>" class="pj-button"/>
			<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminAgents&action=pjActionIndex';" />
		</p>
	</form>

    <div style="display:none;">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdmin&amp;action=pjActionCreditPassword" method="post" id="frmResetPassword" class="form pj-form">
            <input type="hidden" name="reset_password" value="1" />            
        </form>
    </div>
	<?php
}
?>