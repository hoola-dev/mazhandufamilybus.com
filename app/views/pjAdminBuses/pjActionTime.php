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
	
	$show_period = 'false';
	if((strpos($tpl['option_arr']['o_time_format'], 'a') > -1 || strpos($tpl['option_arr']['o_time_format'], 'A') > -1))
	{
		$show_period = 'true';
	}
	
	$route_name = NULL;
	foreach ($tpl['route_arr'] as $v)
	{
		if($tpl['arr']['route_id'] == $v['id'])
		{
			$route_name = pjSanitize::clean($v['title']);
			break;
		}
	}
	?>
	<div class="bold b10 fs14"><?php __('lblRoute'); ?>: <?php echo $route_name;?></div>
	<?php
	
	include_once PJ_VIEWS_PATH . 'pjLayouts/elements/submenu.php';
	pjUtil::printNotice(__('infoUpdateTimeTitle', true, false), __('infoUpdateTimeDesc', true, false)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBuses&amp;action=pjActionTime" method="post" id="frmUpdateTime" class="pj-form form">
		<input type="hidden" name="bus_update" value="1" />
		<input type="hidden" id="id" name="id" value="<?php echo $tpl['arr']['id']?>" />
		
		<p>
			<label class="title"><?php __('lblBusType'); ?>:</label>
			<span class="inline_block" id="boxBusType">
				<select name="bus_type_id" id="bus_type_id" class="pj-form-field w250 required" <?php if ($controller->isEditor()) { ?> disabled <?php } ?>>
					<option value="">-- <?php __('lblChoose'); ?> --</option>
					<?php
					foreach ($tpl['bus_type_arr'] as $v)
					{
						?><option value="<?php echo $v['id']; ?>"<?php echo $v['id'] == $tpl['arr']['bus_type_id'] ? ' selected="selected"' : null;?>><?php echo stripslashes($v['name']); ?>, <?php echo stripslashes($v['seats_count']); ?> <?php echo strtolower(__('lblSeats', true, false))?></option><?php
					}
					?>
				</select>
				<a href="#" class="pj-form-langbar-tip listing-tip" title="<?php __('lblBusTypeTip'); ?>"></a>
			</span>
		</p>
        <p>
            <label class="title">Is International Bus:</label>
            <span class="inline_block" id="boxInternationalBus">
                <select name="is_international_bus" id="is_international_bus" class="pj-form-field w250 required">						
                    <option value="0"<?php echo $tpl['arr']['is_international_bus'] == 0 ? ' selected="selected"' : null;?>>No</option>
                    <option value="1"<?php echo $tpl['arr']['is_international_bus'] == 1 ? ' selected="selected"' : null;?>>Yes</option>
                </select>
                <a href="#" class="pj-form-langbar-tip listing-tip" title="Whether the bus is meant for international trips"></a>
            </span>
        </p>
		<div id="bs_bus_locations">
			<?php
			if(isset($tpl['location_arr']))
			{
				include_once PJ_VIEWS_PATH . 'pjAdminBuses/pjActionGetLocations.php';
			} 
			?>
		</div>
		<p>
			<label class="title"><?php __('lblPeriod'); ?>:</label>
			<span class="inline_block">
				<label class="content float_left r5"><?php __('lblFrom'); ?>:</label>
				<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
					<input type="text" name="start_date" id="start_date" class="pj-form-field pointer w80 datepick-period" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value="<?php echo pjUtil::formatDate($tpl['arr']['start_date'], "Y-m-d", $tpl['option_arr']['o_date_format']); ?>" <?php if ($controller->isEditor()) { ?> disabled <?php } ?>/>
                    <?php if (!$controller->isEditor()) { ?> 
                        <span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
                    <?php } ?>
				</span>
				<label class="content float_left r5"><?php __('lblTo'); ?>:</label>
				<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
					<input type="text" name="end_date" id="end_date" class="pj-form-field pointer w80 datepick-period" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value="<?php echo pjUtil::formatDate($tpl['arr']['end_date'], "Y-m-d", $tpl['option_arr']['o_date_format']); ?>" <?php if ($controller->isEditor()) { ?> disabled <?php } ?>/>
					<?php if (!$controller->isEditor()) { ?> 
                    <span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
                    <?php } ?>
				</span>
			</span>
			<a href="#" class="pj-form-langbar-tip listing-tip" title="<?php __('lblPeriodTip'); ?>"></a>
		</p>
		<p>
			<label class="title"><?php __('lblRecurring'); ?>:</label>
			<span class="inline_block">
				<?php
				$weekdays = __('weekdays', true, false);
				?>
				<span class="block b5"><input type="checkbox" id="bs_weekday_monday" name="recurring[]" value="monday" class="r5"<?php echo strpos($tpl['arr']['recurring'], 'monday') === false ? null : ' checked="checked"';?> <?php if ($controller->isEditor()) { ?> disabled <?php } ?> /><label for="bs_weekday_monday"><?php echo $weekdays['monday'];?></label></span>
				<span class="block b5"><input type="checkbox" id="bs_weekday_tuesday" name="recurring[]" value="tuesday" class="r5"<?php echo strpos($tpl['arr']['recurring'], 'tuesday') === false ? null : ' checked="checked"';?> <?php if ($controller->isEditor()) { ?> disabled <?php } ?> /><label for="bs_weekday_tuesday"><?php echo $weekdays['tuesday'];?></label></span>
				<span class="block b5"><input type="checkbox" id="bs_weekday_wednesday" name="recurring[]" value="wednesday" class="r5"<?php echo strpos($tpl['arr']['recurring'], 'wednesday') === false ? null : ' checked="checked"';?> <?php if ($controller->isEditor()) { ?> disabled <?php } ?> /><label for="bs_weekday_wednesday"><?php echo $weekdays['wednesday'];?></label></span>
				<span class="block b5"><input type="checkbox" id="bs_weekday_thursday" name="recurring[]" value="thursday" class="r5"<?php echo strpos($tpl['arr']['recurring'], 'thursday') === false ? null : ' checked="checked"';?> <?php if ($controller->isEditor()) { ?> disabled <?php } ?> /><label for="bs_weekday_thursday"><?php echo $weekdays['thursday'];?></label></span>
				<span class="block b5"><input type="checkbox" id="bs_weekday_friday" name="recurring[]" value="friday" class="r5"<?php echo strpos($tpl['arr']['recurring'], 'friday') === false ? null : ' checked="checked"';?> <?php if ($controller->isEditor()) { ?> disabled <?php } ?> /><label for="bs_weekday_friday"><?php echo $weekdays['friday'];?></label></span>
				<span class="block b5"><input type="checkbox" id="bs_weekday_saturday" name="recurring[]" value="saturday" class="r5"<?php echo strpos($tpl['arr']['recurring'], 'saturday') === false ? null : ' checked="checked"';?> <?php if ($controller->isEditor()) { ?> disabled <?php } ?> /><label for="bs_weekday_saturday"><?php echo $weekdays['saturday'];?></label></span>
				<span class="block"><input type="checkbox" id="bs_weekday_sunday" name="recurring[]" value="sunday" class="r5"<?php echo strpos($tpl['arr']['recurring'], 'sunday') === false ? null : ' checked="checked"';?> <?php if ($controller->isEditor()) { ?> disabled <?php } ?> /><label for="bs_weekday_sunday"><?php echo $weekdays['sunday'];?></label></span>
			</span>
		</p>
		<p>
			<label class="title">&nbsp;</label>
			<input type="submit" value="<?php __('btnSave'); ?>" class="pj-button" />
			<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminBuses&action=pjActionIndex';" />
		</p>
	</form>
	<script type="text/javascript">
	var locale_array = new Array(); 
	var myLabel = myLabel || {};
	myLabel.showperiod = <?php echo $show_period; ?>;
	myLabel.field_required = "<?php __('bs_field_required'); ?>";
	myLabel.timeFormat = "<?php echo $tpl['option_arr']['o_time_format']?>";
	</script>
	<?php
}
?>