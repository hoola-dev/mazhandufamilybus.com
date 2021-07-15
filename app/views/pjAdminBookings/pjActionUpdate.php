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
        if ($_GET['err'] == 'CRU01') {
            pjUtil::printNotice('No credit balance', 'Booking could not be confirmed since you do not have enough credit balance.');            
        } else if ($_GET['err'] == 'NAA') {
            pjUtil::printNotice('No seat available', 'The selected seat already occupied');            
        } else {
            pjUtil::printNotice(@$titles[$_GET['err']], @$bodies[$_GET['err']]);
        }
	}
	$week_start = isset($tpl['option_arr']['o_week_start']) && in_array((int) $tpl['option_arr']['o_week_start'], range(0,6)) ? (int) $tpl['option_arr']['o_week_start'] : 0;
	$jqDateFormat = pjUtil::jqDateFormat($tpl['option_arr']['o_date_format']);
	
	pjUtil::printNotice(__('infoUpdateBookingTitle', true, false), __('infoUpdateBookingDesc', true, false)); 
	?>
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionUpdate" method="post" class="form pj-form" id="frmUpdateBooking">
		<input type="hidden" name="booking_update" value="1" />
		<input type="hidden" id="booking_route" name="booking_route" value="<?php echo stripslashes($tpl['arr']['booking_route']);?>" />
		<input type="hidden" id="reload_map" name="reload_map" value="1" />
		<input type="hidden" id="return_reload_map" name="reload_map" value="1" />
		<input type="hidden" name="id" value="<?php echo $tpl['arr']['id'];?>" />
		<input type="hidden" name="tab_id" value="<?php echo isset($_GET['tab_id']) && !empty($_GET['tab_id']) ? $_GET['tab_id'] : 'tabs-1'; ?>" />
		<input type="hidden" id="is_from_outside_booking" name="is_from_outside_booking" value="<?php echo $tpl['is_from_outside_booking']; ?>" />
        <input type="hidden" id="is_from_search_outside_booking" name="is_from_search_outside_booking" value="<?php echo $tpl['is_from_search_outside_booking']; ?>" />
		
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1"><?php __('lblBookingDetails');?></a></li>
				<li><a href="#tabs-2"><?php __('lblClientDetails');?></a></li>
			</ul>
		
		
			<div id="tabs-1">
				<div class="bs-loader-outer">
					<div class="bs-loader"></div>
					
					<div class="float_right">
                        <p>
							<label class="title-block"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionResend&amp;id=<?php echo $tpl['arr']['id']; ?>"><?php __('lblResendConfirm'); ?></a></label>
							<label class="title-block"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionPrintTickets&amp;id=<?php echo $tpl['arr']['id']; ?>&hash=<?php echo sha1($tpl['arr']['id'].$tpl['arr']['created'].PJ_SALT)?>" target="_blank"><?php __('lblPrintTickets'); ?></a></label>
						</p>
						<p>
							<label class="title-block"><?php __('lblIpAddress'); ?></label>
							<label class="title-block"><?php echo $tpl['arr']['ip']; ?></label>
						</p>
						<p>
							<label class="title-block"><?php __('lblCreatedOn'); ?></label>
							<label class="title-block"><?php echo pjUtil::formatDate(date('Y-m-d', strtotime($tpl['arr']['created'])), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ' ' . pjUtil::formatTime(date('H:i:s', strtotime($tpl['arr']['created'])), 'H:i:s', $tpl['option_arr']['o_time_format']); ?></label>
						</p>
                        <p>
							<label class="title-block">Last updated by:</label>
							<label class="title-block">
                                <?php echo $tpl['arr']['updated_name'].'<br>'.$tpl['arr']['updated_email']; ?>
                                <?php if ($tpl['arr']['updated_on']) { echo '<br>'.pjUtil::formatDate(date('Y-m-d', strtotime($tpl['arr']['updated_on'])), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ' ' . pjUtil::formatTime(date('H:i:s', strtotime($tpl['arr']['updated_on'])), 'H:i:s', $tpl['option_arr']['o_time_format']); } ?>
                            </label>
						</p>
                        <p>
							<label class="title-block">Status last updated by:</label>
							<label class="title-block">
                                <?php echo $tpl['arr']['status_updated_name'].'<br>'.$tpl['arr']['status_updated_email']; ?>
                                <?php if ($tpl['arr']['status_updated_on']) { echo '<br>'.pjUtil::formatDate(date('Y-m-d', strtotime($tpl['arr']['status_updated_on'])), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ' ' . pjUtil::formatTime(date('H:i:s', strtotime($tpl['arr']['status_updated_on'])), 'H:i:s', $tpl['option_arr']['o_time_format']); } ?>
                            </label>
						</p>  
                        <p>
							<label class="title-block">Discount added history:</label>                            
							<label class="title-block">
                                <div>
                                    <?php if ($tpl['discount_arr']) { ?>                                        
                                        <table class="table" cellspacing="0" cellpadding="0" style="width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Discount</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($tpl['discount_arr'] as $d) { ?>
                                                    <tr>
                                                        <td><?php echo $d['name']; ?></td>
                                                        <td><?php echo $d['discount']; ?></td>
                                                        <td><?php echo pjUtil::formatDate(date('Y-m-d', strtotime($d['added_on'])), 'Y-m-d', $tpl['option_arr']['o_date_format']) . ' ' . pjUtil::formatTime(date('H:i:s', strtotime($d['added_on'])), 'H:i:s', $tpl['option_arr']['o_time_format']); ?></td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                        <div style="margin-top:10px;">Total discount: <?php echo $tpl['option_arr']['o_currency'].' '.$tpl['total_discount']; ?></div>
                                      <?php } else { ?>
                                        No discount added yet
                                    <?php } ?>
                                </div>
                            </label>
						</p>
                        
					</div>
					
					<p>
						<label class="title"><?php __('lblUniqueID');?></label>
						<span class="inline-block">
							<input type="text" disabled="true" id="uuid" name="uuid" value="<?php echo pjSanitize::clean($tpl['arr']['uuid']); ?>" class="pj-form-field w136"/>
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblDate'); ?>:</label>
						<span class="pj-form-field-custom pj-form-field-custom-after float_left r5">
							<input type="text" name="booking_date" id="booking_date" class="pj-form-field pointer w100 datepick" readonly="readonly" rel="<?php echo $week_start; ?>" rev="<?php echo $jqDateFormat; ?>" value="<?php echo pjUtil::formatDate($tpl['arr']['booking_date'], 'Y-m-d', $tpl['option_arr']['o_date_format'])?>"/>
							<span class="pj-form-field-after"><abbr class="pj-form-field-icon-date"></abbr></span>
						</span>
					</p>
					<?php
					$time_arr = explode(" - ", $tpl['arr']['booking_time']); 
					?>
					<div id="fromToBox">
						<p>
							<label class="title"><?php __('lblFrom'); ?>:</label>
							<span class="inline-block">
								<span id="pickupContainer">
									<select name="pickup_id" id="pickup_id" class="pj-form-field w200 required">
										<option value="">-- <?php __('lblChoose'); ?>--</option>
										<?php
										foreach($tpl['from_location_arr'] as $k => $v)
										{
											?><option value="<?php echo $v['id'];?>"<?php echo $v['id'] == $tpl['arr']['pickup_id'] ? ' selected="selected"' : null; ?>><?php echo pjSanitize::clean($v['name']);?></option><?php
										} 
										?>
									</select>
								</span>
								<span id="bsDepartureTime" class="bs-time float_left l5"><?php echo !empty($time_arr) ? __('lblDepartureTime', true, false) . ': ' . $time_arr[0] : null;?></span>
							</span>
						</p>
						<p>
							<label class="title"><?php __('lblTo'); ?>:</label>
							<span class="inline-block">
								<span id="returnContainer">
									<select name="return_id" id="return_id" class="pj-form-field w200 required">
										<option value="">-- <?php __('lblChoose'); ?>--</option>
										<?php
										foreach($tpl['to_location_arr'] as $k => $v)
										{
											?><option value="<?php echo $v['id'];?>"<?php echo $v['id'] == $tpl['arr']['return_id'] ? ' selected="selected"' : null; ?>><?php echo pjSanitize::clean($v['name']);?></option><?php
										} 
										?>
									</select>
								</span>
								<span id="bsArrivalTime" class="bs-time float_left l5"><?php echo !empty($time_arr) ? __('lblArrivalTime', true, false) . ': ' . $time_arr[1] : null;?></span>
							</span>
						</p>
					</div>
					<div id="busBox">
						<p>
							<label class="title"><?php __('lblBus'); ?>:</label>
							<span class="inline-block">
								<select name="bus_id" id="bus_id" class="pj-form-field w300 required">
									<option value="">-- <?php __('lblChoose'); ?>--</option>
									<?php
									foreach ($tpl['bus_arr'] as $k => $v)
									{
										?><option value="<?php echo $v['id']; ?>"<?php echo $v['id'] == $tpl['arr']['bus_id'] ? ' selected="selected"' : null; ?> data-set="<?php echo !empty($v['seats_map']) ? 'T' : 'F';?>"><?php echo $v['route']; ?>, <?php echo $v['depart_arrive']; ?></option><?php
									}
									?>
								</select>
							</span>
						</p>
					</div>
					
					<div id="ticketBox">
						<?php
						if(isset($tpl['ticket_arr']))
						{ 
							?>
							<p>
								<label class="title"><?php __('lblTickets');?>:</label>
								<span class="block overflow">
									<?php
									$seats_avail = $tpl['seats_available'];
									$total_titkets = 0;
									foreach($tpl['ticket_arr'] as $v)
									{
										if($v['price'] != '')
										{
											if((int) $tpl['arr']['back_id'] > 0 && $tpl['arr']['is_return'] == 'F')
											{
												$price = $v['price'] - ($v['price'] * $v['discount'] / 100);
											}else{
												$price = $v['price'];
											}
											?>
											<span class="block b5 overflow">
												<label class="block float_left r5 t5 w150"><?php echo $v['ticket'];?></label>
												<select name="ticket_cnt_<?php echo $v['ticket_id'];?>" class="pj-form-field w60 r3 float_left bs-ticket" data-price="<?php echo $price;?>">
													<?php
													for($i = 0; $i <= $seats_avail; $i++)
													{
														if(isset($tpl['ticket_pair_arr'][$v['ticket_id']]) && ($tpl['ticket_pair_arr'][$v['ticket_id']] == $i) )
														{
															?><option value="<?php echo $i; ?>" selected="selected"><?php echo $i; ?></option><?php
															$total_titkets += $i;
														}else{
															?><option value="<?php echo $i; ?>"><?php echo $i; ?></option><?php
														}
													}
													$seats_avail -= $total_titkets;
													?>
												</select>
												<label class="block float_left r5 t5">x</label>
												<label class="block float_left t5"><?php echo pjUtil::formatCurrencySign( number_format($price, 2), $tpl['option_arr']['o_currency']);?></label>
											</span>
											<?php
										}
									} 
									?>
								</span>
								<input type="hidden" id="bs_number_of_seats" name="bs_number_of_seats" value="<?php echo $tpl['seats_available']; ?>"/>
							</p>
							<?php
						} 
						?>
					</div>
					<div id="seatsBox" style="display: <?php echo !empty($tpl['bus_type_arr']['seats_map']) ? 'block' : 'none';?>;">
						<p>
							<label class="title"><?php __('lblSeats'); ?>:</label>
							<span class="inline-block">
								<label class="content">
									<span id="bs_selected_seat_label" class="block float_left r10"><?php echo join(", ", $tpl['selected_seats'])?></span>
									<a class="bs-select-seats" href="#"><?php __('lblSelectSeats');?></a>
								</label>
								<input type="hidden" id="selected_seats" name="selected_seats" value="<?php echo join("|", $tpl['seat_pair_arr'])?>"<?php echo !empty($tpl['bus_type_arr']['seats_map']) ? (!empty($tpl['seat_pair_arr']) ? ' class=""' : null): null;?> />
							</span>
						</p>
					</div>
					<div id="selectSeatsBox" style="display: none;">
						<p>
							<label class="title"><?php __('lblSeats'); ?>:</label>
							<span class="inline-block">
								<span class="block b5">
									<select name="assigned_seats[]" id="assigned_seats" class="pj-form-field<?php echo empty($tpl['bus_type_arr']['seats_map']) ? ' ' : null;?>" multiple="multiple" size="5">
										<?php
										foreach ($tpl['seat_arr'] as $seat)
										{
											if(!in_array($seat['id'], $tpl['booked_seat_arr']))
											{
												?><option value="<?php echo $seat['id']; ?>"<?php echo in_array($seat['id'], $tpl['seat_pair_arr']) ? ' selected="selected"' : null;?>><?php echo stripslashes($seat['name']); ?></option><?php
											}
										}
										?>
									</select>
								</span>
								<a class="block" target="_blank" href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminSchedule&amp;action=pjActionSeats&amp;bus_id=<?php echo $tpl['arr']['bus_id']?>&amp;date=<?php echo pjUtil::formatDate($tpl['arr']['booking_date'], 'Y-m-d', $tpl['option_arr']['o_date_format'])?>"><?php __('lblViewSeatsList');?></a>
							</span>
						</p>
					</div>
					<p>
						<label class="title"><?php __('lblIsReturn'); ?>:</label>
						<span class="inline-block t5">
							<input type="checkbox" name="is_return" id="is_return" value="T" <?php echo $tpl['arr']['is_return'] == 'T'?'checked="checked"':NULL; ?> style="display: none;"/>
							<?php if ($tpl['arr']['is_return'] == 'T') { ?>
							<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionUpdate&id=<?php echo $tpl['arr']['back_id']?>"><?php __('lblReturnBooking')?></a>
							<?php } elseif (!empty($tpl['arr']['back_id'])) { ?>
							<a href="<?php echo $_SERVER['PHP_SELF']; ?>?controller=pjAdminBookings&amp;action=pjActionUpdate&id=<?php echo $tpl['arr']['back_id']?>"><?php __('lblPickupBooking')?></a>
							<?php } ?>
						</span>
					</p>	
					<p>
						<label class="title"><?php __('lblSubTotal'); ?>:</label>
						<span class="pj-form-field-custom pj-form-field-custom-before">
							<span class="pj-form-field-before"><abbr class="pj-form-field-icon-text"><?php echo pjUtil::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
							<input type="text" id="sub_total" name="sub_total" class="pj-form-field number w108" value="<?php echo pjSanitize::clean($tpl['arr']['sub_total']); ?>" readonly="readonly"/>
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblTax'); ?>:</label>
						<span class="pj-form-field-custom pj-form-field-custom-before">
							<span class="pj-form-field-before"><abbr class="pj-form-field-icon-text"><?php echo pjUtil::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
							<input type="text" id="tax" name="tax" class="pj-form-field number w108" readonly="readonly" value="<?php echo pjSanitize::clean($tpl['arr']['tax']); ?>" data-tax="<?php echo $tpl['option_arr']['o_tax_payment'];?>"/>
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblTotal'); ?>:</label>
						<span class="pj-form-field-custom pj-form-field-custom-before">
							<span class="pj-form-field-before"><abbr class="pj-form-field-icon-text"><?php echo pjUtil::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
                            <input type="text" id="total" name="total" class="pj-form-field number w108" value="<?php echo pjSanitize::clean($tpl['arr']['total']); ?>" <?php if ($controller->isEditor()) { ?> readonly="readonly" <?php } ?>>
						</span>
					</p>
					<p>
						<label class="title"><?php __('lblDeposit'); ?>:</label>
						<span class="pj-form-field-custom pj-form-field-custom-before">
							<span class="pj-form-field-before"><abbr class="pj-form-field-icon-text"><?php echo pjUtil::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
							<input type="text" id="deposit" name="deposit" class="pj-form-field number w108" value="<?php echo pjSanitize::clean($tpl['arr']['deposit']); ?>" data-deposit="<?php echo $tpl['option_arr']['o_deposit_payment'];?>" readonly="readonly"/>
						</span>
					</p>
                    <?php if ($controller->isAllowDiscount()) { ?>
                    <p>
						<label class="title">Max discount: <?php echo ($tpl['bus']['is_international_bus'] == 1) ? $tpl['option_arr']['o_currency'].' '.$tpl['option_arr']['o_international_bus_discount'] : $tpl['option_arr']['o_currency'].' '.$tpl['option_arr']['o_local_bus_discount']; ?><br>Discount till now: <?php echo $tpl['option_arr']['o_currency'].' '.$tpl['total_discount']; ?><br>New discount on ticket:</label>
						<span class="pj-form-field-custom pj-form-field-custom-before">
							<span class="pj-form-field-before"><abbr class="pj-form-field-icon-text"><?php echo pjUtil::formatCurrencySign(NULL, $tpl['option_arr']['o_currency'], ""); ?></abbr></span>
							<input type="text" id="new_discount_on_ticket" name="new_discount_on_ticket" class="pj-form-field number w108" value="" onkeyup="javascript:check_discount(this.value);"/>
						</span>
					</p>
                    <?php } ?>
					
					<p>
						<label class="title"><?php __('lblPaymentMethod');?></label>
						<span class="inline-block">
							<select name="payment_method" id="payment_method" class="pj-form-field w150">
								<!-- <option value="">-- <?php __('lblChoose'); ?>--</option> -->
								<?php
								foreach (__('payment_methods', true, false) as $k => $v)
								{
								?>
                                    <?php if ($k !== $tpl['arr']['payment_method']) { continue; } ?>
                                    <?php if ($k == 'flutterwave') { $v = 'Credit Card'; } ?>
                                    <option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
								}
								?>
							</select>
						</span>
					</p>
					<p class="boxCC" style="display: <?php echo $tpl['arr']['payment_method'] != 'creditcard' ? 'none' : 'block'; ?>">
						<label class="title"><?php __('lblCCType'); ?></label>
						<span class="inline-block">
							<select name="cc_type" class="pj-form-field w150">
								<option value="">---</option>
								<?php
								foreach (__('cc_types', true, false) as $k => $v)
								{
									if (isset($tpl['arr']['cc_type']) && $tpl['arr']['cc_type'] == $k)
									{
										?><option value="<?php echo $k; ?>" selected="selected"><?php echo $v; ?></option><?php
									} else {
										?><option value="<?php echo $k; ?>"><?php echo $v; ?></option><?php
									}
								}
								?>
							</select>
						</span>
					</p>
					<p class="boxCC" style="display: <?php echo $tpl['arr']['payment_method'] != 'creditcard' ? 'none' : 'block'; ?>">
						<label class="title"><?php __('lblCCNum'); ?></label>
						<span class="inline-block">
							<input type="text" name="cc_num" id="cc_num" value="<?php echo pjSanitize::clean($tpl['arr']['cc_num']); ?>" class="pj-form-field w136"/>
						</span>
					</p>
					<p class="boxCC" style="display: <?php echo $tpl['arr']['payment_method'] != 'creditcard' ? 'none' : 'block'; ?>">
						<label class="title"><?php __('lblCCExp'); ?></label>
						<span class="inline-block">
							<select name="cc_exp_month" class="pj-form-field">
								<option value="">---</option>
								<?php
								list($year, $month) = explode("-", $tpl['arr']['cc_exp']);
								$month_arr = __('months', true, false);
								ksort($month_arr);
								foreach ($month_arr as $key => $val)
								{
									?><option value="<?php echo $key;?>"<?php echo (int) $month == $key ? ' selected="selected"' : NULL; ?>><?php echo $val;?></option><?php
								}
								?>
							</select>
							<select name="cc_exp_year" class="pj-form-field">
								<option value="">---</option>
								<?php
								$y = (int) date('Y');
								for ($i = $y; $i <= $y + 10; $i++)
								{
									?><option value="<?php echo $i; ?>"<?php echo $year == $i ? ' selected="selected"' : NULL; ?>><?php echo $i; ?></option><?php
								}
								?>
							</select>
						</span>
					</p>
					<p class="boxCC" style="display: <?php echo $tpl['arr']['payment_method'] != 'creditcard' ? 'none' : 'block'; ?>">
						<label class="title"><?php __('lblCCCode'); ?></label>
						<span class="inline-block">
							<input type="text" name="cc_code" id="cc_code" value="<?php echo pjSanitize::clean($tpl['arr']['cc_code']); ?>" class="pj-form-field w100" />
						</span>
					</p>
					<div class="p">
						<label class="title"><?php __('lblStatus'); ?></label>
						<span class="inline-block">
							<select name="status" id="status" class="pj-form-field w150 required">
								<option value="">-- <?php __('lblChoose'); ?>--</option>
								<?php
								foreach (__('booking_statuses', true, false) as $k => $v)
								{
									?><option value="<?php echo $k; ?>"<?php echo $k == $tpl['arr']['status'] ? ' selected="selected"' : NULL; ?>><?php echo $v; ?></option><?php
								}
								?>
							</select>
						</span>
					</div>
					<p>
						<label class="title">&nbsp;</label>
						<input type="submit" value="<?php __('btnSave', false, true); ?>" class="pj-button" />
						<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminBookings&action=pjActionIndex';" />
					</p>
					
				</div><!-- .bs-loader-outer -->
			</div><!-- #tabs-1 -->
			
			<div id="tabs-2">
				<p>
					<label class="title"><?php __('lblBookingTitle'); ?>:</label>
					<span class="inline-block">
						<select name="c_title" id="c_title" class="pj-form-field w150<?php echo $tpl['option_arr']['o_bf_include_title'] == 3 ? ' required' : NULL; ?>">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
							<?php
							$title_arr = pjUtil::getTitles();
							$name_titles = __('personal_titles', true, false);
							foreach ($title_arr as $v)
							{
								?><option value="<?php echo $v; ?>"<?php echo $tpl['arr']['c_title'] == $v ? ' selected="selected"' : NULL; ?>><?php echo $name_titles[$v]; ?></option><?php
							}
							?>
						</select>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBookingFname'); ?>:</label>
					<span class="inline-block">
						<input type="text" name="c_fname" id="c_fname" class="pj-form-field w250<?php echo $tpl['option_arr']['o_bf_include_fname'] == 3 ? ' required' : NULL; ?>" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['c_fname'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBookingLname'); ?></label>
					<span class="inline-block">
						<input type="text" name="c_lname" id="c_lname" class="pj-form-field w250<?php echo $tpl['option_arr']['o_bf_include_lname'] == 3 ? ' required' : NULL; ?>" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['c_lname'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title">Phone with country code:</label>
					<span class="inline-block">
						<input type="text" name="c_phone" id="c_phone" class="pj-form-field w250<?php echo $tpl['option_arr']['o_bf_include_phone'] == 3 ? ' required' : NULL; ?>" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['c_phone'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBookingEmail'); ?></label>
					<span class="inline-block">
						<input type="text" name="c_email" id="c_email" class="pj-form-field w250<?php echo $tpl['option_arr']['o_bf_include_email'] == 3 ? ' required' : NULL; ?>" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['c_email'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBookingNotes'); ?></label>
					<span class="inline-block">
						<textarea name="c_notes" id="c_notes" class="pj-form-field w500 h120<?php echo $tpl['option_arr']['o_bf_include_notes'] == 3 ? ' required' : NULL; ?>"><?php echo stripslashes($tpl['arr']['c_notes']); ?></textarea>
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBookingCompany'); ?></label>
					<span class="inline-block">
						<input type="text" name="c_company" id="c_company" class="pj-form-field w300<?php echo $tpl['option_arr']['o_bf_include_company'] == 3 ? ' required' : NULL; ?>" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['c_company'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBookingAddress'); ?></label>
					<span class="inline-block">
						<input type="text" name="c_address" id="c_address" class="pj-form-field w300<?php echo $tpl['option_arr']['o_bf_include_address'] == 3 ? ' required' : NULL; ?>" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['c_address'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBookingCity'); ?></label>
					<span class="inline-block">
						<input type="text" name="c_city" id="c_city" class="pj-form-field w300<?php echo $tpl['option_arr']['o_bf_include_city'] == 3 ? ' required' : NULL; ?>" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['c_city'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBookingState'); ?></label>
					<span class="inline-block">
						<input type="text" name="c_state" id="c_state" class="pj-form-field w300<?php echo $tpl['option_arr']['o_bf_include_state'] == 3 ? ' required' : NULL; ?>" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['c_state'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBookingZip'); ?></label>
					<span class="inline-block">
						<input type="text" name="c_zip" id="c_zip" class="pj-form-field w300<?php echo $tpl['option_arr']['o_bf_include_zip'] == 3 ? ' required' : NULL; ?>" value="<?php echo htmlspecialchars(stripslashes($tpl['arr']['c_zip'])); ?>" />
					</span>
				</p>
				<p>
					<label class="title"><?php __('lblBookingCountry'); ?></label>
					<span class="inline-block">
						<select name="c_country" id="c_country" class="pj-form-field w300<?php echo $tpl['option_arr']['o_bf_include_country'] == 3 ? ' required' : NULL; ?>">
							<option value="">-- <?php __('lblChoose'); ?>--</option>
							<?php
							foreach ($tpl['country_arr'] as $v)
							{
								?><option value="<?php echo $v['id']; ?>"<?php echo $tpl['arr']['c_country'] == $v['id'] ? ' selected="selected"' : NULL; ?>><?php echo stripslashes($v['country_title']); ?></option><?php
							}
							?>
						</select>
					</span>
				</p>				
				<p>
					<label class="title">&nbsp;</label>
					<input type="submit" value="<?php __('btnSave', false, true); ?>" class="pj-button" />
					<input type="button" value="<?php __('btnCancel'); ?>" class="pj-button" onclick="window.location.href='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjAdminBookings&action=pjActionIndex';" />
				</p>
			</div>
		</div>
	</form>
	
	<div id="dialogSelect" title="<?php __('lblSelectSeats'); ?>" style="display:none"><img src="<?php echo PJ_IMG_PATH . 'backend/pj-preloader.gif'?>" /></div>
	<div id="dialogReturnSelect" title="<?php __('lblSelectSeats'); ?>" style="display:none"><img src="<?php echo PJ_IMG_PATH . 'backend/pj-preloader.gif'?>" /></div>
	
	<script type="text/javascript">
    remaining_discount = <?php echo $tpl['remaining_discount']; ?>;

	var myLabel = myLabel || {};
	myLabel.from = "<?php echo strtolower(__('lblFrom', true, false));?>";
	myLabel.to = "<?php echo strtolower(__('lblTo', true, false));?>";
	myLabel.assigned_seats = "<?php echo __('lblAssignedSeats');?>";
	myLabel.loader = '<img src="<?php echo PJ_IMG_PATH;?>backend/pj-preloader.gif" />';
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