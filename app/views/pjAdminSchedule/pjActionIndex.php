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
	
	$current_date = date($tpl['option_arr']['o_date_format']);
	?>
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionIndex"><?php __('menuDailySchedule'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionTimetable"><?php __('menuRouteTimetable'); ?></a></li>
		</ul>
	</div>
	<?php
	pjUtil::printNotice(__('infoScheduleTitle', true, false), __('infoScheduleDesc', true, false)); 
	?>
	
	<div class="b10">
		<!-- <form id="frmSchedule" action="" class="pj-form frm-filter">
			<label class="block float_left t6 r5"><?php __('lblBusesOn')?>:</label>
			<a href="#" class="pj-button btn-today float_left r10 pj-button-active" data-value="<?php echo $current_date;?>"><?php __('lblToday'); ?></a>
			<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
				<input type="text" id="schedule_date" name="schedule_date" class="pj-form-field pointer w100 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value="<?php echo $current_date;?>"/>
				<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
			</span>
			
			<span class="block float_right">
				<a id="bs_print_schedule" href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&action=pjActionPrintSchedule&date=<?php echo pjUtil::formatDate($current_date, $tpl['option_arr']['o_date_format']);?>" target="_blank" class="pj-button float_right l5"><?php __('lblPrintSchedule'); ?></a>
				<select name="route_id" id="route_id" class="pj-form-field w200 float_right l5">
					<option value="">-- <?php __('lblChoose'); ?>--</option>
					<?php
					foreach($tpl['route_arr'] as $k => $v)
					{
						?><option value="<?php echo $v['id'];?>"><?php echo stripslashes($v['route']);?></option><?php
					} 
					?>
				</select>
				<label class="block float_right t6"><?php __('lblFilterByRoute');?>:</label>
				<input type="hidden" id="bs_print_href" id="bs_print_href" value="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&action=pjActionPrintSchedule"/>
			</span>
		</form>
		<br class="clear_both" /> -->
		<form id="frmSchedule" action="" class="pj-form frm-filter d--flex">
		    <div class="d--flex flex--basis--50">
		    	<label class="t6 r5"><?php __('lblBusesOn')?>:</label>
				<a href="#" class="pj-button btn-today today--button r10 pj-button-active" data-value="<?php echo $current_date;?>"><?php __('lblToday'); ?></a>
				<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
					<input type="text" id="schedule_date" name="schedule_date" class="pj-form-field pointer w100 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value="<?php echo $current_date;?>"/>
					<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
				</span>
			</div>
			<div class="d--flex flex--basis--50 justify--flex--end">
				<span>
					<a id="bs_print_schedule" href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&action=pjActionPrintSchedule&date=<?php echo pjUtil::formatDate($current_date, $tpl['option_arr']['o_date_format']);?>" target="_blank" class="pj-button float_right l5"><?php __('lblPrintSchedule'); ?></a>
					<select name="route_id" id="route_id" class="pj-form-field w200 float_right l5">
						<option value="">-- <?php __('lblChoose'); ?>--</option>
						<?php
						foreach($tpl['route_arr'] as $k => $v)
						{
							?><option value="<?php echo $v['id'];?>"><?php echo stripslashes($v['route']);?></option><?php
						} 
						?>
					</select>
					<label class="block float_right t6"><?php __('lblFilterByRoute');?>:</label>
					<input type="hidden" id="bs_print_href" id="bs_print_href" value="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&action=pjActionPrintSchedule"/>
				</span>
			</div>
		</form>
	</div>
	<div class="bs-loader-outer">
		<div class="bs-loader"></div>
		<div id="boxSchedule"><?php include PJ_VIEWS_PATH . 'pjAdminSchedule/elements/getSchedule.php'; ?></div>
	</div>
	
	<br/>
	
	<?php
}
?>