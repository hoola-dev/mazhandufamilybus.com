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
	
	<div id="tabs">
		<ul>
			<li><a href="#tabs-1"><?php __('lblBus');?></a></li>
			<li><a href="#tabs-2"><?php __('lblRoute');?></a></li>
		</ul>
	
	
		<div id="tabs-1">
			<?php
			pjUtil::printNotice(__('infoBusReportTitle', true, false), __('infoBusReportDesc', true, false));  
			?>
			<form target="_blank" action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminReports&amp;action=pjActionBusReport" method="post" class="form pj-form" id="frmBusReport">
				<input type="hidden" name="bus_report" value="1" />
				<p>
					<label class="title"><?php __('lblRoute'); ?>:</label>
					<span class="inline-block">
						<select name="bus_route_id" id="bus_route_id" class="pj-form-field w300 required">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
							<?php
							foreach ($tpl['route_arr'] as $k => $v)
							{
								?><option value="<?php echo $v['id']; ?>"><?php echo $v['title']; ?></option><?php
							}
							?>
						</select>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBus'); ?>:</label>
					<span id="bus_container" class="inline-block">
						<select name="bus_id" id="bus_id" class="pj-form-field w300 required">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
						</select>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblTimeScale'); ?>:</label>
					<span class="inline-block">
						<select name="bus_time_scale" id="bus_time_scale" class="pj-form-field w150 required">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
							<?php
							foreach(__('time_scale', true, false) as $k => $v)
							{
								?><option value="<?php echo $k;?>"><?php echo $v;?></option><?php
							} 
							?>
						</select>
					</span>
				</p>
				<p class="boxBusPeriod">
					<label class="title"><?php __('lblPeriod'); ?>:</label>
					<span class="inline_block">
						<label class="content float_left r5"><?php __('lblFrom'); ?>:</label>
						<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
							<input type="text" name="bus_start_date" id="bus_start_date" class="pj-form-field pointer w80 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" />
							<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
						</span>
						<label class="content float_left r5"><?php __('lblTo'); ?>:</label>
						<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
							<input type="text" name="bus_end_date" id="bus_end_date" class="pj-form-field pointer w80 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" />
							<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
						</span>
						<input type="hidden" id="bus_period" name="bus_period" value=""/>
					</span>
				</p>
				<p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="<?php __('btnGenerate', false, true); ?>" class="pj-button" />
				</p>
			</form>	
		</div>
		
		<div id="tabs-2">
			<?php
			pjUtil::printNotice(__('infoRouteReportTitle', true, false), __('infoRouteReportDesc', true, false));  
			?>
			<form target="_blank" action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminReports&amp;action=pjActionRouteReport" method="post" class="form pj-form" id="frmRouteReport">
				<input type="hidden" name="route_report" value="1" />
				
				<p>
					<label class="title"><?php __('lblRoute'); ?>:</label>
					<span class="inline-block">
						<select name="route_id" id="route_id" class="pj-form-field w300 required">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
							<?php
							foreach ($tpl['route_arr'] as $k => $v)
							{
								?><option value="<?php echo $v['id']; ?>"><?php echo $v['title']; ?></option><?php
							}
							?>
						</select>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblTimeScale'); ?>:</label>
					<span class="inline-block">
						<select name="route_time_scale" id="route_time_scale" class="pj-form-field w150 required">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
							<?php
							foreach(__('time_scale', true, false) as $k => $v)
							{
								?><option value="<?php echo $k;?>"><?php echo $v;?></option><?php
							} 
							?>
						</select>
					</span>
				</p>
				<p class="boxRoutePeriod">
					<label class="title"><?php __('lblPeriod'); ?>:</label>
					<span class="inline_block">
						<label class="content float_left r5"><?php __('lblFrom'); ?>:</label>
						<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
							<input type="text" name="route_start_date" id="route_start_date" class="pj-form-field pointer w80 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" />
							<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
						</span>
						<label class="content float_left r5"><?php __('lblTo'); ?>:</label>
						<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
							<input type="text" name="route_end_date" id="route_end_date" class="pj-form-field pointer w80 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" />
							<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
						</span>
						<input type="hidden" id="route_period" name="route_period" value=""/>
					</span>
				</p>
				<p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="<?php __('btnGenerate', false, true); ?>" class="pj-button" />
				</p>
			</form>	
		</div>
	</div>
	
	<script type="text/javascript">
	var myLabel = myLabel || {};
	
	</script>
	<?php
	if (isset($_GET['tab_id']) && !empty($_GET['tab_id']))
	{		
		$tab_id = $_GET['tab_id'];
		$tab_id = $tab_id < 0 ? 0 : $tab_id;
		?>
		<script type="text/javascript">
		(function ($) {
			$(function () {
				$("#tabs").tabs("option", "selected", <?php echo $tab_id; ?>);
			});
		})(jQuery);
		</script>
		<?php
	}
}
?>