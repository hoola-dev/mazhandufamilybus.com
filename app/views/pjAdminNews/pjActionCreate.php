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

	pjUtil::printNotice('Add News', 'Use the form below to add news.'); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminNews&amp;action=pjActionAdd" method="post" id="frmNews" class="form pj-form" enctype="multipart/form-data" onsubmit="javascript:return false;">		
        <p>
			<label class="title">Title:</label>
			<span class="pj-form-field-custom pj-form-field-custom-before">				
				<input type="text" name="nw_title" id="nw_title" class="pj-form-field required w400" value=""/>                
            </span>            
        </p>
        <p>
            <label class="title">Date:</label>
            <span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
                <input type="text" name="nw_date" id="nw_date" class="pj-form-field pointer w100 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value="<?php echo isset($_GET['bus_id']) ? $_GET['date'] : pjUtil::formatDate(date('Y-m-d'), 'Y-m-d', $tpl['option_arr']['o_date_format'])?>"/>
                <span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
            </span>
        </p>
        <p>
            <label class="title">Description:</label>
            <span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
                <textarea name="nw_description" id="nw_description" class="pj-form-field required" rows="10" cols="50"></textarea>                
            </span>
        </p>
        <p>
			<label class="title">Link:</label>
			<span class="pj-form-field-custom pj-form-field-custom-before">				
				<input type="text" name="nw_link" id="nw_link" class="pj-form-field w400" value=""/>                
            </span>            
        </p>
        <p>
			<label class="title">Image:</label>
			<span class="pj-form-field-custom pj-form-field-custom-before">				
				<input type="file" name="nw_image" id="nw_image" class="required" value=""/><br>(600 x 300px)                
            </span>            
        </p>
        <p>
			<label class="title">Active:</label>
			<span class="pj-form-field-custom pj-form-field-custom-before">				
                <select name="nw_is_active" id="nw_is_active" class="pj-form-field w100 required">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>              
            </span>            
        </p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="button" value="<?php __('btnSave', false, true); ?>" class="pj-button" onclick="javascript:add_news();"/>
			<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminNews&action=pjActionIndex';" />
		</p>
	</form>
<?php
}
?>