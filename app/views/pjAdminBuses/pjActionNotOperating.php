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
		pjUtil::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
	}
	$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
	$jqDateFormat = pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']);
	?>
	<div class="bold b10 fs14"><?php __('lblRoute'); ?>: <?php echo pjSanitize::html($tpl['route_arr']['title']);?></div>
	<?php
	include_once PJ_VIEWS_PATH . 'pjLayouts/elements/submenu.php';
	pjUtil::printNotice(__('infoNotOperatingTitle', true, false), __('infoNotOperatingDesc', true, false)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBuses&amp;action=pjActionNotOperating" method="post" id="frmNotOperating" class="pj-form form">
		<input type="hidden" name="bus_update" value="1" />
		<input type="hidden" id="id" name="id" value="<?php echo $tpl['arr']['id']?>" />
		
		
		<p>
			<label class="title"><?php __('lblNotOperatingOn'); ?>:</label>
			<span id="bs_date_container" class="inline_block float_left r5">
				<?php
				foreach($tpl['date_arr'] as $v)
				{
					?>
					<span class="block overflow b5">
						<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
							<input type="text" name="date[]" class="pj-form-field pointer w80 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value="<?php echo pjUtil::formatDate($v['date'], "Y-m-d", $tpl['option_arr']['o_date_format']); ?>" onchange="javascript:check_booked_seats(this);"/>
							<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
						</span>
						<input type="button" value="<?php __('btnRemove'); ?>" class="pj-button pj-button-remove-date" />
					</span>
					<?php
				}
				if(isset($_GET['date']))
				{
					?>
					<span class="block overflow b5">
						<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
							<input type="text" name="date[]" class="pj-form-field pointer w80 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value="<?php echo $_GET['date']; ?>" onchange="javascript:check_booked_seats(this);"/>
							<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
						</span>
						<input type="button" value="<?php __('btnRemove'); ?>" class="pj-button pj-button-remove-date" />
					</span>
					<?php
				} 
				?>
				<label class="pjBrsNoDates block t5" style="display: none;"><?php __('lblNoDatesAdded');?></label>
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="button" value="<?php __('btnAdd'); ?>" class="pj-button pj-button-add-date" />
			<input type="submit" id="btn_submit" value="<?php __('btnSave'); ?>" class="pj-button" />
		</p>
		
	</form>
	
	<div id="bs_date_clone" style="display:none;">
		<span class="block overflow b5">
			<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
				<input type="text" name="date[]" class="pj-form-field pointer w80 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" onchange="javascript:check_booked_seats(this);"/>
				<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
			</span>
			<input type="button" value="<?php __('btnRemove'); ?>" class="pj-button pj-button-remove-date" />
		</span>
	</div>
	
	<script type="text/javascript">
	var locale_array = new Array(); 
	var myLabel = myLabel || {};
	myLabel.field_required = "<?php __('bs_field_required'); ?>";
	myLabel.timeFormat = "<?php echo $tpl['option_arr']['o_time_format']?>";
	</script>
	<?php
}
?>