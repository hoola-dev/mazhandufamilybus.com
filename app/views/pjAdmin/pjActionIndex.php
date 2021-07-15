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
}else{
	$booking_statuses = __('booking_statuses', true, false);
	$current_date = date($tpl['option_arr']['o_date_format'], time());
    ?>
    <?php if ($controller->isEditor()) { ?>
        <div class="dashboard_header_credit">
            <div clas="dashboard_header_credit_text" style="margin-top:10px;color: #515050;font-size: 16px;">
                Your Agent Code<br><br> <abbr><?php echo $tpl['user_code']; ?></abbr>
            </div>
        </div>
    <?php } ?>
    <?php if ($controller->isEditor()) { ?>
    <div class="dashboard_header">
		<div class="item">
			<div class="stat">
				<div class="info" style="margin-left:0px;">
					<abbr style="font-size:23px;"><?php echo $tpl['user_credit_balance'];?></abbr>
					Your Credit Balance
				</div>
			</div>
		</div>
		<div class="item">
			<div class="stat">
				<div class="info" style="margin-left:0px;">
                <abbr style="font-size:23px;"><?php echo $tpl['user_commission_percent'].'%';?></abbr>
					Your Commission Percentage
				</div>
			</div>
		</div>
		<div class="item">
			<div class="stat">
				<div class="info" style="margin-left:0px;">
                <abbr style="font-size:23px;"><?php echo $tpl['user_total_commission'];?></abbr>
					Your Total Commission
				</div>
			</div>
		</div>
	</div>
    <?php } ?>

	<div class="dashboard_header">
		<div class="item">
			<div class="stat bookings">
				<div class="info">
					<abbr><?php echo $tpl['cnt_today_bookings'];?></abbr>
					<?php echo (int) $tpl['cnt_today_bookings'] !== 1 ? strtolower(__('lblDashTodayBookings', true)) : strtolower(__('lblDashTodayBooking', true)); ?>
				</div>
			</div>
		</div>
		<div class="item">
			<div class="stat today-departure">
				<div class="info">
					<abbr><?php echo $tpl['cnt_today_departure'];?></abbr>
					<?php echo (int) $tpl['cnt_today_departure'] !== 1 ? strtolower(__('lblDashTodayBusesDept', true)) : strtolower(__('lblDashTodayBusDept', true)); ?>
				</div>
			</div>
		</div>
		<div class="item">
			<div class="stat total-buses">
				<div class="info smaller">
					<abbr><?php echo $tpl['cnt_routes'];?></abbr>
					<?php echo (int) $tpl['cnt_routes'] !== 1 ? strtolower(__('lblDashRoutes', true)) : strtolower(__('lblDashRoute', true)); ?>
				</div>
				<div class="info smaller">
					<abbr><?php echo $tpl['cnt_buses'];?></abbr>
					<?php echo (int) $tpl['cnt_buses'] !== 1 ? strtolower(__('lblDashBuses', true)) : strtolower(__('lblDashBus', true)); ?>
				</div>
			</div>
		</div>
	</div>
	
	<!-- <div class="dashboard_box">
		<div class="dashboard_top">
			<div class="dashboard_column_top"><?php __('lblDashLatestBookings');?> (<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionIndex"><?php __('lblDashViewAll'); ?></a>)</div>
			<div class="dashboard_column_top"><?php __('lblDashNextDeparture');?> (<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionIndex"><?php __('lblDashViewAll'); ?></a>)</div>
			<div class="dashboard_column_top"><?php __('lblDashBriefInfo');?></div>
		</div>
		<div class="dashboard_middle">
			<div class="dashboard_column">
				<div class="dashboard_list dashboard_latest_list">
					<?php
					if(count($tpl['latest_bookings']) > 0)
					{
						foreach($tpl['latest_bookings'] as $v)
						{
							$client_name_arr = array();
							if(!empty($v['c_fname']))
							{
								$client_name_arr[] = pjSanitize::clean($v['c_fname']);
							}
							if(!empty($v['c_lname']))
							{
								$client_name_arr[] = pjSanitize::clean($v['c_lname']);
							}
							$bus = $v['route_title'] . ', ' . date($tpl['option_arr']['o_time_format'], strtotime($v['departure_time'])) . ' - ' . date($tpl['option_arr']['o_time_format'], strtotime($v['arrival_time']));
							$route = mb_strtolower(__('lblFrom', true), 'UTF-8') . ' ' . $v['from_location'] . ' ' . mb_strtolower(__('lblTo', true), 'UTF-8') . ' ' . $v['to_location'];
							?>
							<div class="dashboard_row">
								<label><?php echo date($tpl['option_arr']['o_date_format'], strtotime($v['created'])) . ' ' . date($tpl['option_arr']['o_time_format'], strtotime($v['created']));?></label>
								<label><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionUpdate&amp;id=<?php echo $v['id']?>"><?php echo join(" ", $client_name_arr);?></a></label>
								<?php
								if(!empty($v['c_phone']))
								{ 
									?><label><?php echo $v['c_phone']?></label><?php
								} 
								?>
								<label><span><?php __('lblStatus');?>:</span> <?php echo $booking_statuses[$v['status']];?></label>
								<label>&nbsp;</label>
								<label><?php echo $bus;?></label>
								<label><span><?php __('lblAt');?></span> <?php echo date($tpl['option_arr']['o_date_format'], strtotime($v['booking_date']));?></label>
								<label><?php echo $route;?></label>
								<label><span><?php __('lblTickets');?></span> <?php echo $v['tickets'];?></label>
							</div>
							<?php
						}
					}else{
						?>
						<div class="dashboard_row">
							<label><span><?php __('lblDashNoBooking');?></span></label>
						</div>
						<?php
					} 
					?>
				</div>
			</div>
			<div class="dashboard_column">
				<div class="dashboard_list dashboard_latest_list">
					<?php
					if(count($tpl['next_buses_arr']) > 0)
					{
						foreach($tpl['next_buses_arr'] as $v)
						{
							$bus_time = '';
							if(!empty($v['departure']) && !empty($v['arrive']))
							{
								$bus_time = pjUtil::formatTime($v['departure'], "H:i:s", $tpl['option_arr']['o_time_format']) . ' - ' . pjUtil::formatTime($v['arrive'], "H:i:s", $tpl['option_arr']['o_time_format']);
							}
							?>
							<div class="dashboard_row">
								<label><span><?php __('lblBus');?>:</span> <?php echo $v['route'];?>, <?php echo $bus_time;?></label>
								<label><span><?php __('lblAt');?></span> <?php echo date($tpl['option_arr']['o_date_format'], strtotime($v['departure_date']));?></label>
								<label>&nbsp;</label>
								<label><span><?php __('lblTotalBookings');?>:</span> <?php echo $v['total_bookings'];?></label>
								<label><span><?php __('lblTotalTocketsSold');?>:</span> <?php echo intval($v['total_tickets']);?></label>
								<label><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionBookings&amp;bus_id=<?php echo $v['id']?>&amp;date=<?php echo date($tpl['option_arr']['o_date_format'], strtotime($v['departure_date']));?>"><?php __('lblViewPassengersList');?></a></label>
								<label><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionSeats&amp;bus_id=<?php echo $v['id']?>&amp;date=<?php echo date($tpl['option_arr']['o_date_format'], strtotime($v['departure_date']));?>"><?php __('lblViewSeatsList');?></a></label>
							</div>
							<?php
						}
					}else{
						?>
						<div class="dashboard_row">
							<label><span><?php __('lblDashNoBuses');?></span></label>
						</div>
						<?php
					} 
					?>
				</div>
			</div>
			<?php
			$total_revenue = !empty($tpl['total_revenue']) ? $tpl['total_revenue'][0]['revenue'] : 0; 
			
			?>
			<div class="dashboard_column">
				<div class="quick_links">
					<label><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionTimetable"><?php __('lblDashTimetable');?></a></label>
					<label><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionIndex"><?php __('menuDailySchedule');?></a></label>
					<label>&nbsp;</label>
					<label><span><?php __('lblTotalBookings');?>:</span> <?php echo $tpl['cnt_bookings'];?></label>
					<label><span><?php __('lblConfirmedBookings');?>:</span> <?php echo $tpl['cnt_confirmed_bookings'];?></label>
					<label>&nbsp;</label>
					<label><span><?php __('lblTotalTocketsSold');?>:</span> <?php echo !empty($tpl['sold_tickets']) ? $tpl['sold_tickets'][0]['tickets'] : 0;?></label>
					<?php
					if($controller->isAdmin())
					{ 
						?>
						<label><span><?php __('lblTotalRevenue');?>:</span> <?php echo pjUtil::formatCurrencySign(number_format($total_revenue, 2), $tpl['option_arr']['o_currency']);?></label>
						<?php
					} 
					?>
					<label>&nbsp;</label>
					<?php
					if($controller->isAdmin())
					{
						if(!empty($tpl['backup_arr']['data']))
						{
							?>
							<label><span><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjBackup&action=pjActionIndex"><?php __('lblLastBackupAt');?></a>:</span></label>
							<label><?php echo $tpl['backup_arr']['data'][0]['created'];?></label>
							<?php
						}else{
							?>
							<label><span><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjBackup&action=pjActionIndex"><?php __('lblMakeBackup');?></a></span></label>
							<?php
						} 
					}
					?>
				</div>
				<div class="dashboard_subtop"><label><?php __('lblOverlappingSeats');?></label></div>
				<div class="dashboard_list dashboard_overlapping_list">
					<?php
					if(count($tpl['overlapping_seats']) > 0)
					{
						$and = ' ' . __('lblAnd', true, false) . ' ';
						foreach($tpl['overlapping_seats'] as $v)
						{
							$row_arr = array();
							$uuid_arr = $v['uuid'];
							foreach($uuid_arr as $pair)
							{
								list($id, $uuid) = explode(":", $pair);
								$row_arr[] = '<a href="'.$_SERVER['PHP_SELF'].'?controller=pjAdminBookings&amp;action=pjActionUpdate&amp;id='.$id.'">' . $pair . '</a>';
							}
							?>
							<div class="dashboard_row lh20">
								<?php echo join($and, $row_arr);?>
							</div>
							<?php
						}
					}else{
						?>
						<div class="dashboard_row">
							<label><span><?php __('lblNoOverlapping');?></span></label>
						</div>
						<?php
					} 
					?>
				</div>
			</div>
		</div>
		<div class="dashboard_bottom"></div>
	</div> -->
    <?php if ($controller->isAdmin() || $controller->isBusAdmin() || $controller->isInvestor()) { ?>
        <div style="background-color: #ffffff;border-radius: 10px;margin:20px 10px 20px 10px;padding:20px 15px;">
            <div style="margin-bottom:30px;text-align:center;font-size:20px;">
                Today's Bookings
            </div>
            <div style="margin-bottom:10px;">
                Total Bookings: <?php echo $tpl['cnt_today_bookings']; ?>
            </div>
            <div style="margin-bottom:10px;">
                Total App Bookings: <?php echo $tpl['cnt_api_booking']; ?>
            </div>
            <div style="margin-bottom:10px;">
                Total Web Bookings: <?php echo $tpl['cnt_web_booking']; ?>
            </div>
            <div style="margin-bottom:10px;">
                <!-- Total Agent Bookings: <?php echo $tpl['cnt_agent_booking']; ?> -->
            </div>

            <div style="clear:both;margin-top:30px;">
                <h2>Route Bookings:</h1>
                <table class="table" cellspacing="0" cellpadding="0" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 5%;">Sl</th>
                            <th style="width: 55%;">Route</th>
                            <th style="width: 20%;">Total Bookings</th>
                            <th style="width: 25%;text-align:right;">Total Amount</th>  
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($tpl['route_today_arr']) { ?>
                        <?php $i = 1; ?>
                        <?php foreach ($tpl['route_today_arr'] as $route) { ?>
                            <tr>    
                                <td><?php echo $i; ?></td>
                                <td><?php echo $route['route']; ?></td>
                                <td style="text-align:center;"><?php echo $route['total_bookings']; ?></td>
                                <td style="text-align:right;"><?php echo pjUtil::formatCurrencySign( number_format($route['total_amount'], 2), $tpl['option_arr']['o_currency']);?></td>
                            </tr>
                            <?php $i++; ?>
                        <?php } ?>
                        <tr><td colspan="4" style="text-align:right;">Total: <?php echo pjUtil::formatCurrencySign( number_format($tpl['route_total'], 2), $tpl['option_arr']['o_currency']);?></td></tr>
                    <?php } else { ?>
                        <tr><td colspan="4">No bookings</td></tr>
                    <?php } ?>                
                    </tbody>
                </table>
            </div>

            <div style="clear:both;margin-top:30px;">
                <h2>Agent Bookings:</h1>
                <table class="table" cellspacing="0" cellpadding="0" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 5%;">Sl</th>
                            <th style="width: 25%">Agent</th>
                            <th style="width: 27%;">Contact</th>          
                            <th style="width: 20%;">Total Bookings</th>
                            <th style="width: 22%;">Total Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($tpl['agent_bookings']) { ?>
                            <?php $i = 1; ?>
                            <?php foreach($tpl['agent_bookings'] as $v) { ?>	
                                <tr>
                                    <td><?php echo $i;?></td>
                                    <td><?php echo $v['name'];?></td>
                                    <td><?php echo $v['phone'].'<br>'.$v['email'];?></td>
                                    <td><?php echo pjUtil::formatCurrencySign( number_format($v['booking_total'], 2), $tpl['option_arr']['o_currency']);?></td>
                                    <td><?php echo pjUtil::formatCurrencySign( number_format($v['total_commission'], 2), $tpl['option_arr']['o_currency']);?></td>
                                </tr>
                                <?php $i++; ?>
                            <?php } ?>		
                        <?php } else { ?>
                            <tr><td colspan="6">No bookings</td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="background-color: #ffffff;border-radius: 10px;margin:20px 10px 20px 10px;padding:20px 15px;">
            <div style="margin-bottom:30px;text-align:center;font-size:20px;">
                Today's Running Buses
            </div>
            <div style="margin-bottom:10px;">
                Total Bookings: <?php echo $tpl['cnt_today_bookings']; ?>
            </div>
            <div style="margin-bottom:10px;">
                Total App Bookings: <?php echo $tpl['cnt_api_booking']; ?>
            </div>
            <div style="margin-bottom:10px;">
                Total Web Bookings: <?php echo $tpl['cnt_web_booking']; ?>
            </div>
            <div style="margin-bottom:10px;">
                <!-- Total Agent Bookings: <?php echo $tpl['cnt_agent_booking']; ?> -->
            </div>

            <div style="clear:both;margin-top:30px;">
                <h2>Today's Running Buses:</h1>
                <table class="table" cellspacing="0" cellpadding="0" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 5%;">Sl</th>
                            <th style="width: 55%;">Bus</th>
                            <th style="width: 20%;">Total Bookings</th>
                            <th style="width: 25%;text-align:right;">Total Amount</th>  
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($tpl['bus_today_arr']) { ?>
                        <?php $i = 1; ?>
                        <?php foreach ($tpl['bus_today_arr'] as $bus) { ?>
                            <?php
                            $departure_time = pjUtil::formatTime ($bus['departure_time'], 'H:i', $tpl['option_arr']['o_time_format']);
                            $arrival_time = pjUtil::formatTime ($bus['arrival_time'], 'H:i', $tpl['option_arr']['o_time_format']);
                            ?>
                            <tr>    
                                <td><?php echo $i; ?></td>
                                <td><?php echo $bus['route'].' '.$departure_time.' - '.$arrival_time; ?></td>
                                <td style="text-align:center;"><?php echo $bus['total_bookings']; ?></td>
                                <td style="text-align:right;"><?php echo pjUtil::formatCurrencySign( number_format($bus['total_amount'], 2), $tpl['option_arr']['o_currency']);?></td>
                            </tr>
                            <?php $i++; ?>
                        <?php } ?>
                        <tr><td colspan="4" style="text-align:right;">Total: <?php echo pjUtil::formatCurrencySign( number_format($tpl['total_bus_amount'], 2), $tpl['option_arr']['o_currency']);?></td></tr>
                    <?php } else { ?>
                        <tr><td colspan="4">No bookings</td></tr>
                    <?php } ?>                
                    </tbody>
                </table>
            </div>            
        </div>
    <?php } ?>
	<div class="dashboard-info-box-wrap">
	    <div class="dashboard_box bg--white">
	    	<div class="dashboard_top">
		    	<div class="dashboard_column_top"><?php __('lblDashLatestBookings');?> (<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionIndex"><?php __('lblDashViewAll'); ?></a>)</div>
			</div>
			<div class="dashboard_middle">
				<div class="dashboard_column">
					<div class="dashboard_list dashboard_latest_list">
						<?php
						if(count($tpl['latest_bookings']) > 0)
						{
							foreach($tpl['latest_bookings'] as $v)
							{
								$client_name_arr = array();
								if(!empty($v['c_fname']))
								{
									$client_name_arr[] = pjSanitize::clean($v['c_fname']);
								}
								if(!empty($v['c_lname']))
								{
									$client_name_arr[] = pjSanitize::clean($v['c_lname']);
								}
								$bus = $v['route_title'] . ', ' . date($tpl['option_arr']['o_time_format'], strtotime($v['departure_time'])) . ' - ' . date($tpl['option_arr']['o_time_format'], strtotime($v['arrival_time']));
								$route = mb_strtolower(__('lblFrom', true), 'UTF-8') . ' ' . $v['from_location'] . ' ' . mb_strtolower(__('lblTo', true), 'UTF-8') . ' ' . $v['to_location'];
								?>
								<div class="dashboard_row">
									<label><?php echo date($tpl['option_arr']['o_date_format'], strtotime($v['created'])) . ' ' . date($tpl['option_arr']['o_time_format'], strtotime($v['created']));?></label>
									<label><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionUpdate&amp;id=<?php echo $v['id']?>"><?php echo join(" ", $client_name_arr);?></a></label>
									<?php
									if(!empty($v['c_phone']))
									{ 
										?><label><?php echo $v['c_phone']?></label><?php
									} 
									?>
									<label><span><?php __('lblStatus');?>:</span> <?php echo $booking_statuses[$v['status']];?></label>
									<label>&nbsp;</label>
									<label><?php echo $bus;?></label>
									<label><span><?php __('lblAt');?></span> <?php echo date($tpl['option_arr']['o_date_format'], strtotime($v['booking_date']));?></label>
									<label><?php echo $route;?></label>
									<label><span><?php __('lblTickets');?></span> <?php echo $v['tickets'];?></label>
								</div>
								<?php
							}
						}else{
							?>
							<div class="dashboard_row">
								<label><span><?php __('lblDashNoBooking');?></span></label>
							</div>
							<?php
						} 
						?>
					</div>
				</div>
			</div>
			<div class="dashboard_bottom"></div>
		</div>
		<div class="dashboard_box bg--white">
	    	<div class="dashboard_top">
	    		<div class="dashboard_column_top"><?php __('lblDashNextDeparture');?> (<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionIndex"><?php __('lblDashViewAll'); ?></a>)</div>
			</div>
			<div class="dashboard_middle">
				<div class="dashboard_column">
					<div class="dashboard_list dashboard_latest_list">
						<?php
						if(count($tpl['next_buses_arr']) > 0)
						{
							foreach($tpl['next_buses_arr'] as $v)
							{
								$bus_time = '';
								if(!empty($v['departure']) && !empty($v['arrive']))
								{
									$bus_time = pjUtil::formatTime($v['departure'], "H:i:s", $tpl['option_arr']['o_time_format']) . ' - ' . pjUtil::formatTime($v['arrive'], "H:i:s", $tpl['option_arr']['o_time_format']);
								}
								?>
								<div class="dashboard_row">
									<label><span><?php __('lblBus');?>:</span> <?php echo $v['route'];?>, <?php echo $bus_time;?></label>
									<label><span><?php __('lblAt');?></span> <?php echo date($tpl['option_arr']['o_date_format'], strtotime($v['departure_date']));?></label>
									<label>&nbsp;</label>
									<label><span><?php __('lblTotalBookings');?>:</span> <?php echo $v['total_bookings'];?></label>
									<label><span><?php __('lblTotalTocketsSold');?>:</span> <?php echo intval($v['total_tickets']);?></label>
									<label><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionBookings&amp;bus_id=<?php echo $v['id']?>&amp;date=<?php echo date($tpl['option_arr']['o_date_format'], strtotime($v['departure_date']));?>"><?php __('lblViewPassengersList');?></a></label>
									<label><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionSeats&amp;bus_id=<?php echo $v['id']?>&amp;date=<?php echo date($tpl['option_arr']['o_date_format'], strtotime($v['departure_date']));?>"><?php __('lblViewSeatsList');?></a></label>
								</div>
								<?php
							}
						}else{
							?>
							<div class="dashboard_row">
								<label><span><?php __('lblDashNoBuses');?></span></label>
							</div>
							<?php
						} 
						?>
					</div>
				</div>
				<?php
				$total_revenue = !empty($tpl['total_revenue']) ? $tpl['total_revenue'][0]['revenue'] : 0; 
				
				?>
			</div>
			<div class="dashboard_bottom"></div>
		</div>
		<div class="dashboard_box">
	    	<div class="brief-info">
			   <div class="dashboard_column_top"><?php __('lblDashBriefInfo');?></div>
			   <div class="dashboard_column">
					<div class="quick_links">
						<label><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionTimetable"><?php __('lblDashTimetable');?></a></label>
						<label><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionIndex"><?php __('menuDailySchedule');?></a></label>
						<label>&nbsp;</label>
						<label><span><?php __('lblTotalBookings');?>:</span> <?php echo $tpl['cnt_bookings'];?></label>
						<label><span><?php __('lblConfirmedBookings');?>:</span> <?php echo $tpl['cnt_confirmed_bookings'];?></label>
						<label>&nbsp;</label>
						<label><span><?php __('lblTotalTocketsSold');?>:</span> <?php echo !empty($tpl['sold_tickets']) ? $tpl['sold_tickets'][0]['tickets'] : 0;?></label>
						<?php
						if($controller->isAdmin())
						{ 
							?>
							<label><span><?php __('lblTotalRevenue');?>:</span> <?php echo pjUtil::formatCurrencySign(number_format($total_revenue, 2), $tpl['option_arr']['o_currency']);?></label>
							<?php
						} 
						?>
						<label>&nbsp;</label>
						<?php
						if($controller->isAdmin())
						{
							if(!empty($tpl['backup_arr']['data']))
							{
								?>
								<label><span><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjBackup&action=pjActionIndex"><?php __('lblLastBackupAt');?></a>:</span></label>
								<label><?php echo $tpl['backup_arr']['data'][0]['created'];?></label>
								<?php
							}else{
								?>
								<label><span><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjBackup&action=pjActionIndex"><?php __('lblMakeBackup');?></a></span></label>
								<?php
							} 
						}
						?>
					</div>
			   </div>
			</div>
			<div class="overlap-info">
				<div class="dashboard_middle">
					<div class="dashboard_column overlap-seat-box">
						<div class="dashboard_subtop"><label><?php __('lblOverlappingSeats');?></label></div>
						<div class="dashboard_list dashboard_overlapping_list">
							<?php
							if(count($tpl['overlapping_seats']) > 0)
							{
								$and = ' ' . __('lblAnd', true, false) . ' ';
								foreach($tpl['overlapping_seats'] as $v)
								{
									$row_arr = array();
									$uuid_arr = $v['uuid'];
									foreach($uuid_arr as $pair)
									{
										list($id, $uuid) = explode(":", $pair);
										$row_arr[] = '<a href="'.$_SERVER['PHP_SELF'].'?controller=pjAdminBookings&amp;action=pjActionUpdate&amp;id='.$id.'">' . $pair . '</a>';
									}
									?>
									<div class="dashboard_row lh20">
										<?php echo join($and, $row_arr);?>
									</div>
									<?php
								}
							}else{
								?>
								<div class="dashboard_row">
									<label><span><?php __('lblNoOverlapping');?></span></label>
								</div>
								<?php
							} 
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	
	<div class="clear_left t20 overflow">
		<div class="float_left black t30 t20"><span class="gray"><?php echo ucfirst(__('lblDashLastLogin', true)); ?>:</span> <?php echo pjUtil::formatDate(date('Y-m-d', strtotime($_SESSION[$controller->defaultUser]['last_login'])), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ' ' . pjUtil::formatTime(date('H:i:s', strtotime($_SESSION[$controller->defaultUser]['last_login'])), 'H:i:s', $tpl['option_arr']['o_time_format']); ?></div>
		<div class="float_right overflow">
		<?php
		list($hour, $day, $other) = explode("_", date("H:i_l_F d, Y"));
		$days = __('days', true, false);
		?>
			<div class="dashboard_date">
				<abbr><?php echo $days[date('w')]; ?></abbr>
				<?php echo pjUtil::formatDate(date('Y-m-d'), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ' ' . pjUtil::formatTime(date('H:i:s'), 'H:i:s', $tpl['option_arr']['o_time_format']); ?>
			</div>
			<div class="dashboard_hour"><?php echo $hour; ?></div>
		</div>
	</div>
	<?php
}
?>