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
	$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
	$jqDateFormat = pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']);
	
	$show_period = 'false';
	if((strpos($tpl['option_arr']['o_time_format'], 'a') > -1 || strpos($tpl['option_arr']['o_time_format'], 'A') > -1))
	{
		$show_period = 'true';
	}
	
	pjUtil::printNotice(__('infoAddBusTitle', true, false), __('infoAddBusDesc', true, false));
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBuses&amp;action=pjActionCreate" method="post" id="frmCreateBus" class="pj-form form">
		<input type="hidden" name="bus_create" value="1" />
		<?php
		$locale = isset($_GET['locale']) && (int) $_GET['locale'] > 0 ? (int) $_GET['locale'] : NULL;
		if (is_null($locale))
		{
			foreach ($tpl['lp_arr'] as $v)
			{
				if ($v['is_default'] == 1)
				{
					$locale = $v['id'];
					break;
				}
			}
		}
		if (is_null($locale))
		{
			$locale = @$tpl['lp_arr'][0]['id'];
		}
		?>
		<input type="hidden" name="locale" value="<?php echo $locale; ?>" />
		<?php if ((int) $tpl['option_arr']['o_multi_lang'] === 1 && count($tpl['lp_arr']) > 1) : ?>
		<div class="multilang"></div>
		<?php endif;?>
		<div class="clear_both">
			<p>
				<label class="title"><?php __('lblRoute'); ?>:</label>
				<span class="inline_block" id="boxRoute">
					<select name="route_id" id="route_id" class="pj-form-field w250 required">
						<option value="">-- <?php __('lblChoose'); ?> --</option>
						<?php
						foreach ($tpl['route_arr'] as $v)
						{
							if(isset($tpl['route_id']) && $tpl['route_id'] == $v['id'])
							{
								?><option value="<?php echo $v['id']; ?>" selected="selected"><?php echo stripslashes($v['title']); ?></option><?php
							}else{
								?><option value="<?php echo $v['id']; ?>"><?php echo stripslashes($v['title']); ?></option><?php
							}
						}
						?>
					</select>
				</span>
			</p>
			<p>
				<label class="title"><?php __('lblBusType'); ?>:</label>
				<span class="inline_block" id="boxBusType">
					<select name="bus_type_id" id="bus_type_id" class="pj-form-field w250 required">
						<option value="">-- <?php __('lblChoose'); ?> --</option>
						<?php
						foreach ($tpl['bus_type_arr'] as $v)
						{
							?><option value="<?php echo $v['id']; ?>"><?php echo stripslashes($v['name']); ?>, <?php echo stripslashes($v['seats_count']); ?> <?php echo strtolower(__('lblSeats', true, false))?></option><?php
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
                        <option value="0">No</option>
                        <option value="1">Yes</option>
					</select>
					<a href="#" class="pj-form-langbar-tip listing-tip" title="Whether the bus is meant for international trips"></a>
				</span>
			</p>
			<div class="bs-loader-outer">
				<div class="bs-loader"></div>
				<div id="bs_bus_locations">
					<?php
					if(isset($tpl['location_arr']))
					{
						include_once PJ_VIEWS_PATH . 'pjAdminBuses/pjActionGetLocations.php';
					} 
					?>
				</div>
			</div>
			<p>
				<label class="title"><?php __('lblPeriod'); ?>:</label>
				<span class="inline_block">
					<label class="content float_left r5"><?php __('lblFrom'); ?>:</label>
					<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
						<input type="text" name="start_date" id="start_date" class="pj-form-field pointer w80 datepick-period" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" />
						<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
					</span>
					<label class="content float_left r5"><?php __('lblTo'); ?>:</label>
					<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
						<input type="text" name="end_date" id="end_date" class="pj-form-field pointer w80 datepick-period" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" />
						<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
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
					<span class="block b5"><input type="checkbox" id="bs_weekday_monday" name="recurring[]" value="monday" checked="checked" class="r5" /><label for="bs_weekday_monday"><?php echo $weekdays['monday'];?></label></span>
					<span class="block b5"><input type="checkbox" id="bs_weekday_tuesday" name="recurring[]" value="tuesday" checked="checked" class="r5" /><label for="bs_weekday_tuesday"><?php echo $weekdays['tuesday'];?></label></span>
					<span class="block b5"><input type="checkbox" id="bs_weekday_wednesday" name="recurring[]" value="wednesday" checked="checked" class="r5" /><label for="bs_weekday_wednesday"><?php echo $weekdays['wednesday'];?></label></span>
					<span class="block b5"><input type="checkbox" id="bs_weekday_thursday" name="recurring[]" value="thursday" checked="checked" class="r5" /><label for="bs_weekday_thursday"><?php echo $weekdays['thursday'];?></label></span>
					<span class="block b5"><input type="checkbox" id="bs_weekday_friday" name="recurring[]" value="friday" checked="checked" class="r5" /><label for="bs_weekday_friday"><?php echo $weekdays['friday'];?></label></span>
					<span class="block b5"><input type="checkbox" id="bs_weekday_saturday" name="recurring[]" value="saturday" checked="checked" class="r5" /><label for="bs_weekday_saturday"><?php echo $weekdays['saturday'];?></label></span>
					<span class="block"><input type="checkbox" id="bs_weekday_sunday" name="recurring[]" value="sunday" checked="checked" class="r5" /><label for="bs_weekday_sunday"><?php echo $weekdays['sunday'];?></label></span>
				</span>
			</p>
			
			<p>
				<label class="title">&nbsp;</label>
				<input type="submit" value="<?php __('btnSave'); ?>" class="pj-button" />
				<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminBuses&action=pjActionIndex';" />
			</p>
		</div>
	</form>
	
	<script type="text/javascript">
	var locale_array = new Array(); 
	var myLabel = myLabel || {};
	myLabel.field_required = "<?php __('bs_field_required'); ?>";
	myLabel.showperiod = <?php echo $show_period; ?>;
	myLabel.timeFormat = "<?php echo $tpl['option_arr']['o_time_format']?>";
	(function ($) {
		$(function() {
			$(".multilang").multilang({
				langs: <?php echo $tpl['locale_str']; ?>,
				flagPath: "<?php echo PJ_FRAMEWORK_LIBS_PATH; ?>pj/img/flags/",
				select: function (event, ui) {
					$("input[name='locale']").val(ui.index);
					$.get("index.php?controller=pjAdminBuses&action=pjActionGetLocale", {
						"locale" : ui.index
					}).done(function (data) {
						route_id = $("#route_id").find("option:selected").val();
						$("#boxRoute").html(data.route);
						$("#route_id").find("option[value='"+route_id+"']").prop("selected", true);
					});
				}
			});
			$(".multilang").find("a[data-index='<?php echo $locale; ?>']").addClass("pj-form-langbar-item-active");
		});
	})(jQuery_1_8_2);
	</script>
	<?php
}
?>