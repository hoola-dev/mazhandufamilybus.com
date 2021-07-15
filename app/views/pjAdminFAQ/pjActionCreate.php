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
	if (isset($_GET['err']) && $_GET['err'] == 'NEWS01') {
        pjUtil::printNotice('Error', 'Sorry, some technical problem occured');
    }

    $week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
    $jqDateFormat = pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']);

	pjUtil::printNotice('Add FAQ', 'Use the form below to add FAQ.'); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminFAQ&amp;action=pjActionAdd" method="post" id="frmFAQ" class="form pj-form" onsubmit="javascript:return false;">		
        <p>
			<label class="title">Title:</label>
			<span class="pj-form-field-custom pj-form-field-custom-before">				
				<input type="text" name="fq_title" id="fq_title" class="pj-form-field required w400" value=""/>                
            </span>            
        </p>
        <p>
            <label class="title">Description:</label>
            <span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
                <textarea name="fq_description" id="fq_description" class="pj-form-field required" rows="10" cols="50"></textarea>                
            </span>
        </p>
        <p>
			<label class="title">Active:</label>
			<span class="pj-form-field-custom pj-form-field-custom-before">				
                <select name="fq_is_active" id="fq_is_active" class="pj-form-field w100 required">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>              
            </span>            
        </p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="button" value="<?php __('btnSave', false, true); ?>" class="pj-button" onclick="javascript:add_faq();"/>
			<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminFAQ&action=pjActionIndex';" />
		</p>
	</form>
<?php
}
?>