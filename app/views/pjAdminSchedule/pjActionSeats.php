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

	?>
	
	<div class="ui-tabs ui-widget ui-widget-content ui-corner-all b10">
		<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionIndex"><?php __('menuDailySchedule'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionTimetable"><?php __('menuRouteTimetable'); ?></a></li>
			<li class="ui-state-default ui-corner-top"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionBookings&amp;bus_id=<?php echo $_GET['bus_id']?>&amp;date=<?php echo $_GET['date'];?>"><?php __('lblPassengersList'); ?></a></li>
			<li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionSeats&amp;bus_id=<?php echo $_GET['bus_id']?>&amp;date=<?php echo $_GET['date'];?>"><?php __('lblSeatsList'); ?></a></li>
		</ul>
	</div>
	
	<label class="block b10 bold"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBuses&amp;action=pjActionTime&amp;id=<?php echo $_GET['bus_id']?>"><?php __('lblBus')?>:&nbsp;<?php echo $tpl['bus_arr']['route']?>, <?php echo pjUtil::formatTime($tpl['bus_arr']['departure_time'], 'H:i:s', $tpl['option_arr']['o_time_format'])?> - <?php echo pjUtil::formatTime($tpl['bus_arr']['arrival_time'], 'H:i:s', $tpl['option_arr']['o_time_format'])?></a> <?php __('lblOn');?> <?php echo $_GET['date']?></label>
	
	<?php
	pjUtil::printNotice(__('infoSeatsListTitle', true, false), __('infoSeatsListDesc', true, false));
	$first_order = 1;
	$last_order = 1; 
	
	$col_width = 150;
	$number_of_locations = count($tpl['location_arr']); 
	?>
	<div class="overflow pj-form form">
		<div class="overflow float_right">
			<div class="overflow b10">
				<a id="bs_print_seats" href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionPrintSeats&amp;bus_id=<?php echo $_GET['bus_id']?>&amp;date=<?php echo pjUtil::formatDate($_GET['date'], $tpl['option_arr']['o_date_format']);?>" target="_blank" class="pj-button float_right"><?php __('lblPrintList'); ?></a>
			</div>
		</div>
	</div>
	
	<div class="pj-location-grid">
		<div class="pj-first-column">
			<table cellpadding="0" cellspacing="0" border="0" class="display">
				<tbody>
					<tr class="title-head-row">
						<td><?php __('lblSeats');?></td>
					</tr>
					<?php
					foreach($tpl['seat_arr'] as $k => $seat)
					{
						?>
						<tr class="title-row" lang="<?php echo $seat['id']; ?>">
							<td class="align_center bold"><?php echo pjSanitize::clean($seat['name']);?></td>
						</tr>
						<?php
					} 
					?>
				</tbody>
			</table>
		</div>
		<div class="pj-location-column">
			<div class="wrapper1">
		    	<div class="div1-compare" style="width: <?php echo $col_width * $number_of_locations; ?>px;"></div>
			</div>
			<div class="wrapper2">
				<div class="div2-compare" style="width: <?php echo $col_width * $number_of_locations; ?>px;">
					<table cellpadding="0" cellspacing="0" border="0" class="display" id="compare_table" width="<?php echo $col_width * $number_of_locations; ?>px">
						<thead>
							<tr class="content-head-row">
								<?php
								$total = 0;
								$switch = __('switch', true, false);
								foreach($tpl['location_arr'] as $k => $v)
								{
									$on_str = $off_str = 0;
									$_str = '<br/>';
									if($k < $number_of_locations - 1)
									{
										if(isset($tpl['on_arr'][$v['location_id']]))
										{
											$on_str = array_sum($tpl['on_arr'][$v['location_id']]);
											$total += $on_str;
											$_str .= ' ' . $switch['on']. ': ' . $on_str . ' /';
										}else{
											$_str .= ' ' . $switch['on']. ': 0' . ' /';
										}
									}
									if($k > 0)
									{	
										if(isset($tpl['off_arr'][$v['location_id']]))
										{
											$off_str = array_sum($tpl['off_arr'][$v['location_id']]);
											$total -= $off_str;
											$_str .= ' ' . $switch['off']. ': ' . $off_str . ' /';
										}else{
											$_str .= ' ' . $switch['off']. ': 0' . ' /';
										}
									}
									if($total > 0)
									{
										$_str .= ' ' . __('lblT', true, false) . ': ' . $total;
									}else{
										$_str .= ' ' . __('lblT', true, false) . ': 0';
									}
									$time = '&nbsp;';
									if(!empty($v['departure_time']))
									{
										$time = __('lblDeparture', true, false) . ": ". pjUtil::formatTime($v['departure_time'], "H:i:s", $tpl['option_arr']['o_time_format']);
									}else{
										$time = __('lblArrive', true, false) . ": ". pjUtil::formatTime($v['arrival_time'], "H:i:s", $tpl['option_arr']['o_time_format']);
									}
									
									?><th class="<?php echo $k == 0 ? 'first-col' : null;?>" width="<?php echo $col_width;?>px"><b><?php echo pjSanitize::clean($v['location']);?></b><br/><?php echo $time;?><?php echo $_str;?></th><?php
									
									if($k == 0)
									{
										$first_order = $v['order'];
									}
									if($k == count($tpl['location_arr']) - 1)
									{
										$last_order = $v['order'];
									}
								} 
								?>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach($tpl['seat_arr'] as $key => $seat)
							{
								?>
								<tr id="content_row_<?php echo $seat['id']; ?>" >
									<?php
									if(isset($tpl['bs_arr'][$seat['id']]))
									{
										$bs_arr = $tpl['bs_arr'][$seat['id']];
										$first_col = 1;
										$person_titles = __('personal_titles', true, false);
										foreach($bs_arr as $k => $bs)
										{
											$colspan = $bs['return_order'] - $bs['pickup_order'];
											if($bs['return_order'] == $last_order)
											{
												$colspan++;
											}
											if($bs['pickup_order'] > $first_col)
											{
												$interval = $bs['pickup_order'] - $first_col;
												for($i = 1;$i <= $interval; $i++)
												{
													?><td class="<?php echo $key == 0 ? 'first-col' : null;?>" id="content_row_<?php echo $seat['id']; ?>">&nbsp;</td><?php
												}
											}
											
											$client_name_arr = array();
											if(!empty($bs['c_title']))
											{
												$client_name_arr[] = $person_titles[$bs['c_title']];
											}
											if(!empty($bs['c_fname']))
											{
												$client_name_arr[] = pjSanitize::clean($bs['c_fname']);
											}
											if(!empty($bs['c_lname']))
											{
												$client_name_arr[] = pjSanitize::clean($bs['c_lname']);
											}
											$tickets = $bs['tickets'];
											$cnt_tickets = count($tickets);
											
											$_ticket_arr = array();
											if($cnt_tickets > 1)
											{
												foreach($tickets as $t)
												{
													$_ticket_arr[] = $t;
												}
											}else{
												$_ticket_arr[] = $tickets[0];
											}
											$tooltip = __('lblBookingID', true, false) . ': ' . $bs['id'] . '<br/>' . __('lblNumberOfTickets', true, false) . ': ' . join(", ", $_ticket_arr) . '<br/>' . __('lblSeats', true, false) . ': ' . join(", ", $bs['seats']) . '<br/>' . __('lblPhone', true, false) . ': ' . $bs['c_phone'];
											?><td class="bs-booked-seat<?php echo $key == 0 ? ' first-col' : null;?>" id="content_row_<?php echo $seat['id']; ?>" colspan="<?php echo $colspan;?>"><a class="timetable-tip" title="<?php echo $tooltip;?>" href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionUpdate&amp;id=<?php echo $bs['id'];?>"><?php echo join(" ", $client_name_arr);?></a></td><?php
											$first_col = $bs['return_order'];
											if($k == count($bs_arr) - 1 && $bs['return_order'] < $last_order)
											{
												$interval = $last_order - $bs['return_order'] + 1;
												for($i = 1;$i <= $interval; $i++)
												{
													?><td class="<?php echo $key == 0 ? 'first-col' : null;?>" id="content_row_<?php echo $seat['id']; ?>">&nbsp;</td><?php
												}
											}
										}
									}else{
										for($i = $first_order;$i <= $last_order; $i++)
										{
											?><td class="<?php echo $key == 0 ? 'first-col' : null;?>" id="content_row_<?php echo $seat['id']; ?>">&nbsp;</td><?php
										}
									}
									?>
								</tr>
								<?php
							} 
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		
		<div class="bs-seat-legends">
			<?php __('lblSeatsLegends');?>
		</div>
	</div>
	<?php
}
?>