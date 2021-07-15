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
	if(!isset($tpl['bus_arr']))
	{
		$titles = __('error_titles', true);
		$bodies = __('error_bodies', true);
		pjUtil::printNotice(NULl, @$bodies['AS09']);
	}else{
		?>
		<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
			<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
				<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionIndex"><?php __('menuDailySchedule'); ?></a></li>
				<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionTimetable"><?php __('menuRouteTimetable'); ?></a></li>
				<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionBookings&amp;bus_id=<?php echo $_GET['bus_id']?>&amp;date=<?php echo $_GET['date'];?>"><?php __('lblPassengersList'); ?></a></li>
				<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionSeats&amp;bus_id=<?php echo $_GET['bus_id']?>&amp;date=<?php echo $_GET['date'];?>"><?php __('lblSeatsList'); ?></a></li>
			</ul>
		</div>
		<label class="block b10 bold"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBuses&amp;action=pjActionTime&amp;id=<?php echo $_GET['bus_id']?>"><?php __('lblBus')?>:&nbsp;<?php echo $tpl['bus_arr']['route']?>, <?php echo pjUtil::formatTime($tpl['bus_arr']['departure_time'], 'H:i:s', $tpl['option_arr']['o_time_format'])?> - <?php echo pjUtil::formatTime($tpl['bus_arr']['arrival_time'], 'H:i:s', $tpl['option_arr']['o_time_format'])?></a> <?php __('lblOn');?> <?php echo $_GET['date']?></label>
		<?php
		pjUtil::printNotice(__('infoScheduleBookingsTitle', true, false), __('infoScheduleBookingsDesc', true, false)); 
		?>
		
		<form id="frmSchedule" action="" class="pj-form frm-filter">
			<p>
				<span class="block float_left">
					<label class="title"><?php __('lblStartLocation'); ?>:</label>
					<span class="inline_block">
						<select id="location_id" name="location_id" class="pj-form-field w200" data-href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionGetBookings&amp;bus_id=<?php echo $_GET['bus_id']?>&amp;date=<?php echo pjUtil::formatDate($_GET['date'], $tpl['option_arr']['o_date_format']);?>">
							<option value="">-- <?php __('lblChoose') ?> --</option>
							<?php
							foreach($tpl['location_arr'] as $k => $v)
							{
								if($k <= count($tpl['location_arr']) - 2)
								{
									?>
									<option value="<?php echo $v['city_id']?>"><?php echo pjSanitize::clean($v['location']);?></option>
									<?php
								}
							} 
							?>
						</select>
					</span>
				</span>
				<a id="bs_print_booking" href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionPrintBookings&amp;bus_id=<?php echo $_GET['bus_id']?>&amp;date=<?php echo pjUtil::formatDate($_GET['date'], $tpl['option_arr']['o_date_format']);?>" target="_blank" class="pj-button float_right"><?php __('lblPrintList'); ?></a>
			</p>
		</form>
		<br class="clear_both" />
		
		<div class="bs-loader-outer">
			<div class="bs-loader"></div>
			<div id="boxBookings"><?php include PJ_VIEWS_PATH . 'pjAdminSchedule/elements/getBookings.php'; ?></div>
		</div>
		
		<br/>
		<?php
	}
}
?>