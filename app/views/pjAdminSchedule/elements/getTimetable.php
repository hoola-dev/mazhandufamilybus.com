<?php
$week_start_date = $tpl['week_start_date'];
$week_end_date = $tpl['week_end_date']; 
$days = __('days', true, false);

$selected_date = null;
if (isset($_GET['selected_date']) && !empty($_GET['selected_date']))
{
	$selected_date = pjUtil::formatDate($_GET['selected_date'], $tpl['option_arr']['o_date_format']);
	$selected_date = strtotime($selected_date);
}
$current_timestamp = strtotime(date('Y-m-d'));
?>
<div class="b5 overflow">
	<a class="float_left block bs-navigator-week" id="bs_prev_week" href="javascript:void(0);" data-week_start="<?php echo date('Y-m-d', strtotime($week_start_date . " -7 days")) ?>" data-week_end="<?php echo date('Y-m-d', strtotime($week_end_date . " -7 days")) ?>"><?php __('lblPrevWeek');?></a>
	<a class="float_right block bs-navigator-week" id="bs_next_week" href="javascript:void(0);" data-week_start="<?php echo date('Y-m-d', strtotime($week_end_date . " +1 days")) ?>" data-week_end="<?php echo date('Y-m-d', strtotime($week_end_date . " +7 days")) ?>"><?php __('lblNextWeek');?></a>
</div>
<div class="clear_both"></div>
<table class="pj-table" cellspacing="0" cellpadding="0" style="width: 100%;">
	<thead>
		<tr>
			<td><?php __('lblBus');?></td>
			<?php
			for($i = 0; $i < 7; $i++)
			{
				
				$week_date_timestamp = strtotime($week_start_date . " +$i days");
				?><td<?php echo $week_date_timestamp < $current_timestamp ? ($week_date_timestamp == $selected_date ? ' class="bs-passed-date bs-bold-date"' : ' class="bs-passed-date"') : ($week_date_timestamp == $current_timestamp || $week_date_timestamp == $selected_date ? ' class="bs-bold-date"' : null);?>><?php echo $days[date('w', $week_date_timestamp)]; ?><br/><?php echo pjUtil::formatDate(date("Y-m-d", $week_date_timestamp), "Y-m-d", $tpl['option_arr']['o_date_format']) ?></td><?php
			} 
			?>
		</tr>
	</thead>
	<tbody>
		<?php
		if(count($tpl['bus_arr']) > 0)
		{
			foreach($tpl['bus_arr'] as $v)
			{
				?>
				<tr>
					<td><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBuses&action=pjActionTime&id=<?php echo $v['id']?>"><?php echo $v['route'];?></a></td>
					<?php
					for($i = 0; $i < 7; $i++)
					{
						$week_date_timestamp = strtotime($week_start_date . " +$i days");
						$end_date_timestamp = strtotime($v['end_date']);
						
						if($end_date_timestamp >= $week_date_timestamp)
						{
							$week_day = strtolower(date('l', $week_date_timestamp));
							$week_date_sql = date('Y-m-d', $week_date_timestamp);
							$pos = strpos($v['recurring'], $week_day);
							if(isset($tpl['date_arr'][$v['id']]))
							{
								if(in_array($week_date_sql, $tpl['date_arr'][$v['id']]))
								{
									?><td>&nbsp;</td><?php
								}else{
									if($pos === false)
									{
										?><td>&nbsp;</td><?php
									}else{
										if(isset($tpl['ticket_arr'][$v['id'] . '~:~' . $week_date_sql]))
										{
											$passengers = $tpl['ticket_arr'][$v['id'] . '~:~' . $week_date_sql];
											$passengers = $passengers . ': ' . ($passengers != 1 ? __('lblPassengers', true, false) : __('lblPassenger', true, false));
										}else{
											$passengers = '0: ' . __('lblPassengers', true, false);
										}
										?><td<?php echo $week_date_timestamp < $current_timestamp ? ' class="bs-passed-date"' : null;?>><a class="timetable-tip" href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&action=pjActionSeats&bus_id=<?php echo $v['id']?>&date=<?php echo pjUtil::formatDate(date('Y-m-d', $week_date_timestamp), 'Y-m-d', $tpl['option_arr']['o_date_format']); ?>" title="<?php echo $passengers;?>"><?php echo pjUtil::formatTime($v['departure_time'], 'H:i:s', $tpl['option_arr']['o_time_format'])?></a></td><?php
									}	
								}
							}else{
								if($pos === false)
								{
									?><td>&nbsp;</td><?php
								}else{
									if(isset($tpl['ticket_arr'][$v['id'] . '~:~' . $week_date_sql]))
									{
										$passengers = $tpl['ticket_arr'][$v['id'] . '~:~' . $week_date_sql];
										$passengers = $passengers . ': ' . ($passengers != 1 ? __('lblPassengers', true, false) : __('lblPassenger', true, false));
									}else{
										$passengers = '0: ' . __('lblPassengers', true, false);
									}
									?><td<?php echo $week_date_timestamp < $current_timestamp ? ' class="bs-passed-date"' : null;?>><a class="timetable-tip" href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&action=pjActionSeats&bus_id=<?php echo $v['id']?>&date=<?php echo pjUtil::formatDate(date('Y-m-d', $week_date_timestamp), 'Y-m-d', $tpl['option_arr']['o_date_format']); ?>" title="<?php echo $passengers;?>"><?php echo pjUtil::formatTime($v['departure_time'], 'H:i:s', $tpl['option_arr']['o_time_format'])?></a></td><?php
								}
							}
						}else{
							?><td>&nbsp;</td><?php
						}
					} 
					?>
				</tr>
				<?php
			}
		} else {
			?>
			<tr>
				<td colspan="8"><?php __('gridEmptyResult');?></td>
			</tr>
			<?php
		}
		?>
	</tbody>
</table>